<?php

declare(strict_types=1);

use App\Support\CsvWriter;

function test_csv_bom_delimiter_crlf_quotes_and_formula_injection(): void
{
    $values = ['=1+1', '+SUM(A1)', '-1', '@SUM(A1)', "\tformula", "\rformula", 'Instansi; "Indonesia"', "Baris\nbaru", '005/12/2026', 'Élémen'];
    $csv = CsvWriter::write([$values]);
    assertSame("\xEF\xBB\xBF", substr($csv, 0, 3));
    assertTrue(str_ends_with($csv, "\r\n"), 'CSV harus berakhir CRLF.');
    $stream = fopen('php://temp', 'w+');
    fwrite($stream, substr($csv, 3));
    rewind($stream);
    $parsed = fgetcsv($stream, 0, ';', '"', '');
    fclose($stream);
    foreach ($values as $index => $value) {
        assertSame($index < 6 ? "'" . $value : $value, $parsed[$index]);
    }
}

function test_laporan_filters_print_csv_and_deleted_rows(): void
{
    withSuratFixture(static function ($surat, $fixtures, $years, $userId, $username, $password): void {
        $year = $years[0];
        $data = suratData($year);
        $data['asal_surat'] = 'Asal%_! <uji>';
        $data['perihal'] = '=1+1';
        $data['sifat'] = 'penting';
        $first = $surat->create($data, $userId);
        $data['tanggal_diterima'] = "$year-01-03";
        $second = $surat->create($data, $userId);
        $hidden = $surat->create($data, $userId);
        $surat->softDelete($hidden, $userId);
        $other = suratData($year);
        $other['asal_surat'] = 'AsalABC';
        $surat->create($other, $userId);
        $filters = ['dari' => "$year-01-02", 'sampai' => "$year-01-03", 'sifat' => 'penting', 'asal' => '%_!'];
        assertSame([$first, $second], array_column($surat->report($filters), 'id'));
        withServer(static function ($base) use ($username, $password, $filters, $year): void {
            $guest = [];
            foreach (['/laporan', '/laporan/cetak', '/laporan/ekspor'] as $path) {
                assertSame('/login', httpRequest($base . $path, $guest)['headers']['location']);
            }
            $cookies = [];
            loginHttp($base, $username, $password, $cookies);
            $query = '?' . http_build_query($filters);
            $response = httpRequest($base . '/laporan' . $query, $cookies);
            assertSame(200, $response['status']);
            assertTrue(str_contains($response['body'], '2 surat'), 'Jumlah laporan harus sesuai filter.');
            assertTrue(str_contains($response['body'], '&lt;uji&gt;'), 'Asal harus di-escape.');
            assertTrue(str_contains($response['body'], '/laporan/ekspor?'), 'Ekspor mempertahankan filter.');
            $print = httpRequest($base . '/laporan/cetak' . $query, $cookies);
            assertSame(200, $print['status']);
            foreach (['A4 landscape', 'table-header-group', 'break-inside: avoid', 'window.print()', 'BUKU AGENDA SURAT MASUK', 'Dicetak:', 'WITA'] as $text) {
                assertTrue(str_contains($print['body'], $text), 'Cetak harus memuat ' . $text);
            }
            assertTrue(!str_contains($print['body'], '<nav'), 'Cetak harus mandiri.');
            $csv = httpRequest($base . '/laporan/ekspor' . $query, $cookies);
            assertSame(200, $csv['status']);
            assertSame('text/csv; charset=UTF-8', $csv['headers']['content-type']);
            assertTrue(str_contains($csv['headers']['content-disposition'], "simasuk_laporan_$year-01-02_$year-01-03.csv"), 'Nama CSV sesuai periode.');
            assertTrue(str_contains($csv['body'], "'=1+1"), 'Formula harus dinetralkan.');
            assertSame(3, substr_count($csv['body'], "\r\n"));
            foreach ([['dari' => '2026-02-30'], ['dari' => ['x']], ['sampai' => ''], ['sifat' => 'rusak'], ['asal' => ['x']], ['dari' => '2026-02-02', 'sampai' => '2026-01-01']] as $bad) {
                foreach (['/laporan', '/laporan/cetak', '/laporan/ekspor'] as $path) {
                    $invalid = httpRequest($base . $path . '?' . http_build_query($bad), $cookies);
                    assertSame(422, $invalid['status']);
                    assertTrue(!str_contains($invalid['headers']['content-type'], 'text/csv'), 'Filter invalid tidak boleh diekspor.');
                }
            }
            assertSame(200, httpRequest($base . '/laporan', $cookies)['status']);
        });
    });
}
