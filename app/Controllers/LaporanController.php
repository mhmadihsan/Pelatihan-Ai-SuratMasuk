<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;
use App\Core\View;
use App\Repositories\SuratRepository;
use App\Support\AgendaNumber;
use App\Support\CsvWriter;

final class LaporanController
{
    public function index(Request $request, ?array $user): Response
    {
        return $this->render($request, $user);
    }

    public function cetak(Request $request, ?array $user): Response
    {
        return $this->render($request, $user, 'cetak');
    }

    public function ekspor(Request $request, ?array $user): Response
    {
        return $this->render($request, $user, 'ekspor');
    }

    private function render(Request $request, ?array $user, string $mode = 'index'): Response
    {
        $validator = new Validator();
        $input = $request->query + ['dari' => date('Y-m-01'), 'sampai' => date('Y-m-d'), 'sifat' => '', 'asal' => ''];
        $filters = [
            'dari' => $validator->date($input, 'dari', 'Dari tanggal'),
            'sampai' => $validator->date($input, 'sampai', 'Sampai tanggal'),
            'sifat' => $validator->choice($input, 'sifat', 'Sifat', ['', 'biasa', 'penting', 'segera', 'rahasia'], false),
            'asal' => $validator->string($input, 'asal', 'Asal surat', required: false, max: 150),
        ];
        if ($validator->errors() === [] && $filters['dari'] > $filters['sampai']) {
            $validator->add('sampai', 'Tanggal akhir tidak boleh lebih awal dari tanggal awal.');
        }
        $errors = $validator->errors();
        $rows = $errors === [] ? (new SuratRepository(Database::connection()))->report($filters) : [];
        if ($errors === [] && $mode === 'ekspor') {
            $csv = [['No. Agenda', 'No. Surat', 'Tgl Surat', 'Tgl Diterima', 'Asal Surat', 'Perihal', 'Sifat', 'Lampiran', 'Keterangan']];
            foreach ($rows as $row) {
                $csv[] = [AgendaNumber::format($row['nomor_urut'], $row['tahun_agenda']), $row['nomor_surat'],
                    $row['tanggal_surat'], $row['tanggal_diterima'], $row['asal_surat'], $row['perihal'],
                    ucfirst($row['sifat']), $row['jumlah_lampiran'], $row['keterangan']];
            }
            return new Response(CsvWriter::write($csv), 200, [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="simasuk_laporan_' . $filters['dari'] . '_' . $filters['sampai'] . '.csv"',
                'Cache-Control' => 'no-store',
            ]);
        }
        return View::render('laporan/' . ($errors === [] && $mode === 'cetak' ? 'cetak' : 'index'), [
            'title' => 'Laporan surat masuk', 'currentUser' => $user,
            'filters' => $filters, 'rows' => $rows, 'errors' => $errors,
        ], $errors === [] ? 200 : 422);
    }
}
