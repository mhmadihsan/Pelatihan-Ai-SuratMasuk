<?php

declare(strict_types=1);

use App\Core\Database;
use App\Core\Validator;
use App\Repositories\AgendaCounterRepository;
use App\Repositories\SuratRepository;
use App\Repositories\Testing\SuratFixtureRepository;
use App\Repositories\Testing\UserFixtureRepository;
use App\Support\AgendaNumber;

function suratData(int $year): array
{
    return [
        'nomor_surat' => '005/UJI/' . bin2hex(random_bytes(4)),
        'tanggal_surat' => $year . '-01-01', 'tanggal_diterima' => $year . '-01-02',
        'asal_surat' => 'Instansi pengujian', 'perihal' => 'Undangan rapat',
        'sifat' => 'biasa', 'jumlah_lampiran' => 0, 'keterangan' => '',
    ];
}

function withSuratFixture(callable $test): void
{
    $migration = runCli('database/migrate.php');
    assertSame(0, $migration['code'], 'Migrasi fixture surat gagal.');
    $db = Database::connection();
    $users = new UserFixtureRepository($db);
    $fixtures = new SuratFixtureRepository($db);
    $years = $fixtures->unusedYears();
    $username = 's2_' . bin2hex(random_bytes(6));
    $password = 'SuratPengujian123';
    $userId = $users->create($username, password_hash($password, PASSWORD_DEFAULT));
    try {
        $test(new SuratRepository($db), $fixtures, $years, $userId, $username, $password);
    } finally {
        $fixtures->cleanup($userId, $years);
        $users->delete($userId);
    }
}

function test_surat_number_format_and_validator_boundaries(): void
{
    assertSame('001/2026', AgendaNumber::format(1, 2026));
    assertSame('1000/2026', AgendaNumber::format(1000, 2026));
    foreach (['2026-02-30', '2026-2-01', '0000-01-01', '2026-01-01junk', "2026-01-01\0", ['2026-01-01']] as $date) {
        $validator = new Validator();
        $validator->date(['tanggal' => $date], 'tanggal', 'Tanggal');
        assertTrue(isset($validator->errors()['tanggal']), 'Tanggal tidak valid harus ditolak.');
    }
    $validator = new Validator();
    $validator->date(['tanggal' => '2024-02-29'], 'tanggal', 'Tanggal');
    assertSame([], $validator->errors());
    foreach (['-1', '1.5', '1e2', '65536', '999999999999999999999999', ['1']] as $number) {
        $validator = new Validator();
        assertSame(null, $validator->integer(['jumlah' => $number], 'jumlah', 'Jumlah', 0, 65535));
    }
    $validator = new Validator();
    assertSame(0, $validator->integer(['jumlah' => '0'], 'jumlah', 'Jumlah', 0, 65535));
    assertSame(65535, $validator->integer(['jumlah' => '65535'], 'jumlah', 'Jumlah', 0, 65535));
    assertSame([], $validator->errors());
}

function test_surat_transaction_sequence_year_reset_and_rollback(): void
{
    withSuratFixture(static function ($surat, $fixtures, $years, $userId): void {
        [$year, $nextYear] = $years;
        $counter = new AgendaCounterRepository(Database::connection());
        $rejected = false;
        try {
            $counter->next($year);
        } catch (LogicException) {
            $rejected = true;
        }
        assertTrue($rejected, 'Counter tidak boleh dialokasikan di luar transaksi.');
        for ($number = 1; $number <= 3; $number++) {
            $id = $surat->create(suratData($year), $userId);
            assertSame($number, $surat->find($id)['nomor_urut']);
        }
        foreach ([$year => 3, $nextYear => null] as $failureYear => $before) {
            $failed = false;
            try {
                $surat->create(suratData($failureYear), 0);
            } catch (PDOException) {
                $failed = true;
            }
            assertTrue($failed, 'Foreign key tidak valid harus menggagalkan INSERT.');
            assertSame($before, $fixtures->counter($failureYear), 'Rollback harus mengembalikan counter.');
            assertSame(false, Database::connection()->inTransaction());
        }
        $id = $surat->create(suratData($nextYear), $userId);
        assertSame(1, $surat->find($id)['nomor_urut']);
        $id = $surat->create(suratData($year), $userId);
        assertSame(4, $surat->find($id)['nomor_urut']);
    });
}

function test_surat_concurrent_first_insert_has_no_gaps(): void
{
    withSuratFixture(static function ($surat, $fixtures, $years, $userId): void {
        $workers = [];
        try {
            for ($i = 0; $i < 4; $i++) {
                $environment = getenv();
                $command = preserveEmptyEnvironment([
                    PHP_BINARY, __DIR__ . '/workers/catat-surat.php', json_encode(suratData($years[0])), (string) $userId,
                ], $environment);
                $process = proc_open($command,
                    [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, dirname(__DIR__), $environment);
                assertTrue(is_resource($process), 'Worker pencatatan harus dimulai.');
                fclose($pipes[0]);
                $workers[] = [$process, $pipes];
            }
            $numbers = [];
            foreach ($workers as [$process, $pipes]) {
                $numbers[] = (int) stream_get_contents($pipes[1]);
                $error = stream_get_contents($pipes[2]);
                fclose($pipes[1]);
                fclose($pipes[2]);
                assertSame(0, proc_close($process), 'Pencatatan paralel gagal: ' . $error);
            }
            sort($numbers);
            assertSame([1, 2, 3, 4], $numbers);
            assertSame(4, $fixtures->counter($years[0]));
        } finally {
            foreach ($workers as [$process, $pipes]) {
                if (is_resource($process)) {
                    proc_terminate($process);
                    proc_close($process);
                }
            }
        }
    });
}

function test_surat_http_create_duplicate_list_detail_and_validation(): void
{
    withSuratFixture(static function ($surat, $fixtures, $years, $userId, $username, $password): void {
        withServer(static function (string $base) use ($surat, $fixtures, $years, $userId, $username, $password): void {
            $cookies = [];
            foreach (['/surat', '/surat/tambah', '/surat/1'] as $path) {
                assertSame('/login', httpRequest($base . $path, $cookies)['headers']['location']);
            }
            $token = csrfToken(httpRequest($base . '/login', $cookies));
            httpRequest($base . '/login', $cookies, 'POST', [
                '_token' => $token, 'username' => $username, 'password' => $password,
            ]);
            $form = httpRequest($base . '/surat/tambah', $cookies);
            assertSame(200, $form['status']);
            assertTrue(str_contains($form['body'], 'value="' . date('Y-m-d') . '"'), 'Tanggal diterima bawaan adalah hari ini.');
            $token = csrfToken($form);
            $valid = suratData($years[0]);
            $valid['jumlah_lampiran'] = '0';
            $valid['perihal'] = '<script>alert("uji")</script>';
            assertSame(419, httpRequest($base . '/surat', $cookies, 'POST', $valid)['status']);
            $valid['_token'] = $token;
            foreach ([
                ['tanggal_diterima' => $years[0] . '-01-01', 'tanggal_surat' => $years[0] . '-01-02'],
                ['tanggal_diterima' => date('Y-m-d', strtotime('+1 day'))],
                ['tanggal_surat' => $years[0] . '-02-30'], ['tanggal_surat' => "bad\0date"],
                ['tanggal_surat' => $years[0] . "-01-01\0"],
                ['sifat' => 'lainnya'], ['jumlah_lampiran' => '-1'], ['jumlah_lampiran' => '65536'],
                ['nomor_surat' => str_repeat('a', 101)], ['asal_surat' => str_repeat('b', 151)],
                ['perihal' => str_repeat('c', 256)], ['keterangan' => str_repeat('é', 32768)],
                ['nomor_surat' => ['array']], ['konfirmasi_duplikat' => ['1']],
            ] as $invalid) {
                $response = httpRequest($base . '/surat', $cookies, 'POST', array_replace($valid, $invalid));
                assertSame(200, $response['status'], 'Input salah harus kembali ke form.');
                assertTrue(str_contains($response['body'], 'aria-invalid="true"')
                    || str_contains($response['body'], 'Konfirmasi duplikat tidak valid.'), 'Galat per kolom harus tampil.');
                assertSame(null, $fixtures->counter($years[0]), 'Validasi gagal tidak boleh mengambil nomor.');
            }
            $response = httpRequest($base . '/surat', $cookies, 'POST', $valid);
            assertSame(302, $response['status']);
            $detailPath = $response['headers']['location'];
            $id = (int) basename($detailPath);
            $detail = httpRequest($base . $detailPath, $cookies);
            assertSame(200, $detail['status']);
            assertTrue(str_contains($detail['body'], '001/' . $years[0]), 'Nomor agenda pertama harus tampil.');
            assertTrue(str_contains($detail['body'], '&lt;script&gt;'), 'Detail harus meng-escape isi surat.');
            assertTrue(!str_contains($detail['body'], '<script>'), 'Isi surat tidak boleh dieksekusi.');
            assertTrue(str_contains($detail['body'], '02/01/' . $years[0]), 'Tanggal tampil dd/mm/yyyy.');
            $saved = $surat->find($id);
            assertSame($userId, $saved['dibuat_oleh']);
            assertSame(0, $saved['jumlah_lampiran']);
            $warning = httpRequest($base . '/surat', $cookies, 'POST', $valid);
            assertSame(200, $warning['status']);
            assertTrue(str_contains($warning['body'], 'sudah tercatat'), 'Duplikat harus mendapat peringatan.');
            assertTrue(str_contains($warning['body'], e($valid['nomor_surat'])), 'Isian lama harus dipertahankan.');
            assertSame(1, $fixtures->counter($years[0]));
            $response = httpRequest($base . '/surat', $cookies, 'POST', $valid + ['konfirmasi_duplikat' => '1']);
            assertSame(302, $response['status']);
            assertSame(2, $fixtures->counter($years[0]));
            $third = array_replace($valid, ['nomor_surat' => 'KETIGA', 'perihal' => 'Surat ketiga', 'keterangan' => '0']);
            unset($third['sifat'], $third['jumlah_lampiran']);
            $response = httpRequest($base . '/surat', $cookies, 'POST', $third);
            assertSame(302, $response['status']);
            $thirdId = (int) basename($response['headers']['location']);
            assertSame('biasa', $surat->find($thirdId)['sifat']);
            assertSame(0, $surat->find($thirdId)['jumlah_lampiran']);
            assertTrue(str_contains(httpRequest($base . '/surat/' . $thirdId, $cookies)['body'], '>0</dd>'),
                'Keterangan bernilai nol harus tetap tampil.');
            $list = httpRequest($base . '/surat', $cookies);
            assertSame(200, $list['status']);
            assertTrue(strpos($list['body'], '003/' . $years[0]) < strpos($list['body'], '002/' . $years[0]), 'Agenda terbaru di atas.');
            assertTrue(str_contains($list['body'], '&lt;script&gt;'), 'Daftar harus meng-escape isi surat.');
            foreach (['/surat/0', '/surat/abc', '/surat/9999999999999999999999999', '/surat/' . PHP_INT_MAX] as $path) {
                assertSame(404, httpRequest($base . $path, $cookies)['status']);
            }
            $fixtures->hide($thirdId, $userId);
            assertSame(null, $surat->find($thirdId));
            assertSame(404, httpRequest($base . '/surat/' . $thirdId, $cookies)['status']);
            assertTrue(!str_contains(httpRequest($base . '/surat', $cookies)['body'], 'KETIGA'), 'Surat terhapus tidak boleh terlihat.');
        });
    });
}
