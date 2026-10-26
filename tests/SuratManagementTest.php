<?php

declare(strict_types=1);

use App\Core\Database;
use App\Repositories\Testing\UserFixtureRepository;

function suratLogin(string $base, array &$cookies, string $username, string $password): string
{
    $token = csrfToken(httpRequest($base . '/login', $cookies));
    $response = httpRequest($base . '/login', $cookies, 'POST', [
        '_token' => $token, 'username' => $username, 'password' => $password,
    ]);
    assertSame(302, $response['status']);
    return csrfToken(httpRequest($base . '/', $cookies));
}

function test_surat_search_filters_and_pagination(): void
{
    withSuratFixture(static function ($surat, $fixtures, $years, $userId, $username, $password): void {
        $prefix = 'cari_' . bin2hex(random_bytes(4));
        $ids = [];
        for ($i = 1; $i <= 5; $i++) {
            $data = suratData($years[0]);
            $data['nomor_surat'] = $prefix . '-' . $i;
            $data['asal_surat'] = 'Asal ' . $i;
            $data['perihal'] = 'Perihal ' . $i;
            $data['sifat'] = $i === 1 ? 'biasa' : 'penting';
            $data['tanggal_diterima'] = $years[0] . '-01-0' . $i;
            $ids[] = $surat->create($data, $userId);
        }
        $filters = ['q' => $prefix, 'sifat' => '', 'dari' => '', 'sampai' => ''];
        $first = $surat->paginate($filters, 1, 2);
        assertSame(5, $first['total']);
        assertSame(3, $first['pages']);
        assertSame([$ids[4], $ids[3]], array_column($first['items'], 'id'));
        assertSame([$ids[2], $ids[1]], array_column($surat->paginate($filters, 2, 2)['items'], 'id'));
        assertSame([$ids[0]], array_column($surat->paginate($filters, PHP_INT_MAX, 2)['items'], 'id'));
        $combined = array_replace($filters, ['sifat' => 'penting', 'dari' => $years[0] . '-01-02',
            'sampai' => $years[0] . '-01-04']);
        assertSame([$ids[3], $ids[2], $ids[1]], array_column($surat->paginate($combined, 1, 10)['items'], 'id'));
        foreach (['Asal 3', 'Perihal 3', $prefix . '-3'] as $term) {
            assertSame([$ids[2]], array_column($surat->paginate(array_replace($filters, ['q' => $term]), 1, 10)['items'], 'id'));
        }
        foreach ([$prefix . "' OR 1=1 --", $prefix . '%', $prefix . '_', $prefix . '!'] as $term) {
            $result = $surat->paginate(array_replace($filters, ['q' => $term]), 1, 10);
            assertSame(0, $result['total'], 'Pencarian harus memperlakukan input sebagai teks literal.');
        }
        withServer(static function (string $base) use ($username, $password, $prefix, $years, $ids): void {
            $cookies = [];
            suratLogin($base, $cookies, $username, $password);
            $query = ['q' => $prefix, 'sifat' => 'penting', 'dari' => $years[0] . '-01-02', 'sampai' => $years[0] . '-01-05'];
            $response = httpRequest($base . '/surat?' . http_build_query($query), $cookies);
            assertSame(200, $response['status']);
            assertTrue(str_contains($response['body'], '4 surat · Halaman 1 dari 2'), 'Jumlah dan halaman harus sesuai filter.');
            assertTrue(!str_contains($response['body'], '/surat/' . $ids[1] . '"'), 'Halaman pertama hanya memuat dua surat.');
            preg_match('/rel="next" href="([^"]+)"/', $response['body'], $match);
            assertTrue(isset($match[1]), 'Tautan halaman berikutnya harus tersedia.');
            $url = html_entity_decode($match[1], ENT_QUOTES, 'UTF-8');
            parse_str(parse_url($url, PHP_URL_QUERY), $actual);
            assertSame($query + ['page' => '2'], $actual, 'Semua filter harus dipertahankan.');
            $second = httpRequest($base . $url, $cookies);
            assertTrue(str_contains($second['body'], '/surat/' . $ids[1] . '"'), 'Surat halaman kedua harus tampil.');
            assertTrue(str_contains($second['body'], 'rel="prev"'), 'Tautan kembali harus tersedia.');
            foreach ([['q' => ['x']], ['sifat' => 'asing'], ['dari' => '2026-02-30'],
                ['sampai' => ['x']], ['dari' => '2026-02-02', 'sampai' => '2026-01-01'],
                ['page' => '0'], ['page' => '-1'], ['page' => ['1']], ['page' => '1e2'],
                ['page' => '999999999999999999999']] as $invalid) {
                $response = httpRequest($base . '/surat?' . http_build_query($invalid), $cookies);
                assertSame(200, $response['status']);
                assertTrue(str_contains($response['body'], 'Periksa filter pencarian'), 'Input GET rusak harus menjadi galat form.');
            }
            $response = httpRequest($base . '/surat?q=' . urlencode('<script>'), $cookies);
            assertTrue(str_contains($response['body'], '&lt;script&gt;'), 'Pencarian harus di-escape.');
            assertTrue(!str_contains($response['body'], '<script>'), 'Pencarian tidak boleh mengeksekusi HTML.');
        }, ['PER_PAGE' => '2']);
    });
}

function test_surat_edit_and_admin_soft_delete_permissions(): void
{
    withSuratFixture(static function ($surat, $fixtures, $years, $userId, $username, $password): void {
        $users = new UserFixtureRepository(Database::connection());
        $adminName = 's3admin_' . bin2hex(random_bytes(4));
        $adminId = $users->create($adminName, password_hash($password, PASSWORD_DEFAULT), role: 'admin');
        $data = suratData($years[0]);
        $id = $surat->create($data, $userId);
        $other = suratData($years[0]);
        $otherId = $surat->create($other, $adminId);
        try {
            withServer(static function (string $base) use ($surat, $fixtures, $years, $userId, $username,
                $password, $adminName, $adminId, $data, $id, $other, $otherId): void {
                $cookies = [];
                $token = suratLogin($base, $cookies, $username, $password);
                $edit = httpRequest($base . '/surat/' . $otherId . '/ubah', $cookies);
                assertSame(200, $edit['status'], 'Operator boleh mengubah surat pengguna lain.');
                assertTrue(str_contains($edit['body'], 'value="' . e($other['nomor_surat']) . '"'), 'Form ubah harus berisi data lama.');
                assertTrue(!str_contains($edit['body'], '/hapus"'), 'Tombol hapus tidak boleh tampil untuk Operator.');
                $updated = array_replace($other, ['_token' => $token, 'jumlah_lampiran' => '2',
                    'tanggal_diterima' => $years[1] . '-01-02', 'perihal' => '<b>Diubah Operator</b>',
                    'tahun_agenda' => '9999', 'nomor_urut' => '999', 'dibuat_oleh' => (string) $userId]);
                assertSame(419, httpRequest($base . '/surat/' . $otherId, $cookies, 'POST', [])['status']);
                $invalid = array_replace($updated, ['tanggal_diterima' => $years[0] . '-01-01', 'tanggal_surat' => $years[0] . '-01-02']);
                $response = httpRequest($base . '/surat/' . $otherId, $cookies, 'POST', $invalid);
                assertSame(200, $response['status']);
                assertTrue(str_contains($response['body'], 'tidak boleh lebih awal'), 'Ubah memakai validasi tanggal yang sama.');
                assertSame($other['perihal'], $surat->find($otherId)['perihal']);
                $response = httpRequest($base . '/surat/' . $otherId, $cookies, 'POST', $updated);
                assertSame(302, $response['status']);
                $saved = $surat->find($otherId);
                assertSame($years[0], $saved['tahun_agenda']);
                assertSame(2, $saved['nomor_urut']);
                assertSame($adminId, $saved['dibuat_oleh']);
                assertSame(2, $fixtures->counter($years[0]));
                assertSame(null, $fixtures->counter($years[1]));
                assertSame($updated['tanggal_diterima'], $saved['tanggal_diterima']);
                assertTrue(str_contains(httpRequest($base . '/surat/' . $otherId, $cookies)['body'], '&lt;b&gt;Diubah Operator&lt;/b&gt;'), 'Isi yang diubah harus di-escape.');
                assertSame(302, httpRequest($base . '/surat/' . $otherId, $cookies, 'POST', $updated)['status'], 'Simpan tanpa perubahan bukan duplikat dirinya sendiri.');
                $duplicate = array_replace($updated, ['nomor_surat' => $data['nomor_surat'], 'asal_surat' => $data['asal_surat']]);
                $response = httpRequest($base . '/surat/' . $otherId, $cookies, 'POST', $duplicate);
                assertTrue(str_contains($response['body'], 'sudah tercatat'), 'Duplikat pada ubah harus diperingatkan.');
                assertSame($other['nomor_surat'], $surat->find($otherId)['nomor_surat']);
                assertSame(302, httpRequest($base . '/surat/' . $otherId, $cookies, 'POST', $duplicate + ['konfirmasi_duplikat' => '1'])['status']);
                assertSame(403, httpRequest($base . '/surat/' . $id . '/hapus', $cookies, 'POST', ['_token' => $token])['status']);
                assertTrue($surat->find($id) !== null, 'Penolakan hapus tidak boleh mengubah surat.');
                $adminCookies = [];
                $adminToken = suratLogin($base, $adminCookies, $adminName, $password);
                $detail = httpRequest($base . '/surat/' . $id, $adminCookies);
                assertTrue(str_contains($detail['body'], '/surat/' . $id . '/hapus"'), 'Admin harus mendapat aksi hapus.');
                assertTrue(str_contains($detail['body'], 'return confirm('), 'Aksi hapus harus meminta konfirmasi browser.');
                assertSame(404, httpRequest($base . '/surat/' . $id . '/hapus', $adminCookies)['status']);
                assertSame(419, httpRequest($base . '/surat/' . $id . '/hapus', $adminCookies, 'POST', [])['status']);
                assertSame(302, httpRequest($base . '/surat/' . $id . '/hapus', $adminCookies, 'POST', ['_token' => $adminToken])['status']);
                assertSame(null, $surat->find($id));
                $deletion = $fixtures->deletion($id);
                assertSame($adminId, $deletion['dihapus_oleh']);
                assertTrue($deletion['dihapus_pada'] !== null, 'Soft delete harus mempertahankan baris dan waktu hapus.');
                foreach (['/surat/' . $id, '/surat/' . $id . '/ubah'] as $path) {
                    assertSame(404, httpRequest($base . $path, $adminCookies)['status']);
                }
                assertSame(404, httpRequest($base . '/surat/' . $id, $adminCookies, 'POST', array_replace($updated, ['_token' => $adminToken]))['status']);
                assertSame(404, httpRequest($base . '/surat/' . $id . '/hapus', $adminCookies, 'POST', ['_token' => $adminToken])['status']);
                $newId = $surat->create(suratData($years[0]), $userId);
                assertSame(3, $surat->find($newId)['nomor_urut'], 'Nomor surat terhapus tidak boleh digunakan ulang.');
                $filtered = $surat->paginate(['q' => $data['nomor_surat'], 'sifat' => '', 'dari' => '', 'sampai' => ''], 1, 20);
                assertSame([$otherId], array_column($filtered['items'], 'id'), 'Pencarian tidak boleh memuat surat terhapus.');
            });
        } finally {
            $fixtures->cleanup($adminId, $years);
            $fixtures->cleanup($userId, $years);
            $users->delete($adminId);
        }
    });
}
