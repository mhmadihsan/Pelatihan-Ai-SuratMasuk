<?php

declare(strict_types=1);
?>
<!doctype html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>BUKU AGENDA SURAT MASUK</title>
<link rel="stylesheet" href="/assets/app.css">
<style>
@page { size: A4 landscape; margin: 12mm; }
body { font-family: sans-serif; padding: 20px; font-size: 11px; color: #000; }
header { text-align: center; margin-bottom: 20px; } h1 { font-size: 18px; font-weight: bold; }
#agenda table { width: 100%; border-collapse: collapse; table-layout: fixed; font-size: 9pt; line-height: 1.3; }
#agenda th, #agenda td { border: 1px solid #555; padding: 4px; overflow-wrap: anywhere; }
#agenda th:nth-child(1) { width: 8%; } #agenda th:nth-child(2) { width: 11%; }
#agenda th:nth-child(3), #agenda th:nth-child(4) { width: 8%; }
#agenda th:nth-child(5) { width: 14%; } #agenda th:nth-child(6) { width: 22%; }
#agenda th:nth-child(7) { width: 6%; } #agenda th:nth-child(8) { width: 7%; }
#agenda th:nth-child(9) { width: 16%; }
thead { display: table-header-group; } tr { break-inside: avoid; page-break-inside: avoid; }
footer { margin-top: 16px; }
@media print { .aksi { display: none; } body { padding: 0; } .overflow-x-auto { overflow: visible; } }
</style></head><body>
<div class="aksi mb-5 flex items-center gap-3">
<button type="button" onclick="window.print()" class="inline-flex items-center gap-2 rounded-lg bg-blue-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-800">Cetak</button>
<a href="/laporan?<?= e(http_build_query($filters)) ?>" class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Kembali</a>
</div>
<header><h1><?= e(config('INSTANSI_NAMA')) ?></h1><p><?= e(config('INSTANSI_ALAMAT')) ?></p>
<h1>BUKU AGENDA SURAT MASUK</h1><p>Periode: <?= e(tanggal($filters['dari'])) ?> – <?= e(tanggal($filters['sampai'])) ?></p>
<?php if ($filters['sifat'] !== '' || $filters['asal'] !== ''): ?><p>Sifat: <?= e($filters['sifat'] === '' ? 'Semua' : ucfirst($filters['sifat'])) ?> · Asal: <?= e($filters['asal'] === '' ? 'Semua' : $filters['asal']) ?></p><?php endif; ?></header>
<section id="agenda"><?php require __DIR__ . '/_table.php'; ?></section>
<footer>Dicetak: <?= e(date('d/m/Y H:i:s')) ?> WITA oleh <?= e($currentUser['nama_lengkap']) ?></footer>
</body></html>
