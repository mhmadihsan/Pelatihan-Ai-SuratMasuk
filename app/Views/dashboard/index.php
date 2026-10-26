<?php

declare(strict_types=1);

use App\Support\AgendaNumber;

$sifatMeta = [
    'biasa' => ['label' => 'Biasa', 'dot' => 'bg-slate-400', 'panel' => 'from-slate-50 to-white', 'ring' => 'ring-slate-200', 'text' => 'text-slate-700'],
    'penting' => ['label' => 'Penting', 'dot' => 'bg-amber-500', 'panel' => 'from-amber-50 to-white', 'ring' => 'ring-amber-200', 'text' => 'text-amber-800'],
    'segera' => ['label' => 'Segera', 'dot' => 'bg-red-500', 'panel' => 'from-red-50 to-white', 'ring' => 'ring-red-200', 'text' => 'text-red-800'],
    'rahasia' => ['label' => 'Rahasia', 'dot' => 'bg-purple-500', 'panel' => 'from-purple-50 to-white', 'ring' => 'ring-purple-200', 'text' => 'text-purple-800'],
];
?>
<section class="relative mb-8 overflow-hidden rounded-3xl bg-linear-to-br from-blue-950 via-blue-800 to-blue-600 p-6 text-white shadow-xl shadow-blue-900/15 sm:p-8">
    <div aria-hidden="true" class="absolute -right-20 -top-24 h-64 w-64 rounded-full border border-white/15"></div>
    <div aria-hidden="true" class="absolute -bottom-28 right-24 h-56 w-56 rounded-full border border-white/10"></div>
    <div class="relative flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-blue-200">Dashboard</p>
            <h1 class="mt-2 text-3xl font-bold tracking-tight sm:text-4xl">Selamat datang, <?= e($currentUser['nama_lengkap']) ?></h1>
            <p class="mt-3 max-w-xl text-sm leading-6 text-blue-100/90">Ringkasan surat masuk per <?= e(tanggal(date('Y-m-d'))) ?>.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="/surat/tambah" class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-blue-800 shadow-lg shadow-blue-950/20 transition hover:bg-blue-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                Tambah surat
            </a>
            <a href="/laporan" class="inline-flex items-center gap-2 rounded-xl border border-white/25 bg-white/10 px-4 py-2.5 text-sm font-semibold text-white backdrop-blur transition hover:bg-white/15 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 20.25h16.5M6 16.5V9m6 7.5V4.5m6 12v-4.5"/></svg>
                Lihat laporan
            </a>
        </div>
    </div>
</section>
<div class="mb-8 grid gap-4 sm:grid-cols-2">
    <?php foreach (['bulan' => ['Surat bulan ini', 'Sejak awal bulan berjalan', 'from-blue-600 to-blue-700'], 'tahun' => ['Surat tahun ini', 'Akumulasi tahun berjalan', 'from-slate-800 to-slate-950']] as $key => [$label, $hint, $gradient]): ?>
        <section class="group relative overflow-hidden rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 transition hover:-translate-y-0.5 hover:shadow-lg hover:shadow-slate-900/5">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-sm font-semibold text-slate-600"><?= e($label) ?></h2>
                    <p class="mt-3 text-4xl font-bold tracking-tight text-slate-950"><?= e($summary[$key]) ?></p>
                    <p class="mt-2 text-xs text-slate-500"><?= e($hint) ?></p>
                </div>
                <span class="grid h-12 w-12 place-items-center rounded-2xl bg-linear-to-br <?= e($gradient) ?> text-white shadow-lg shadow-slate-900/15">
                    <?php if ($key === 'bulan'): ?>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-6 w-6" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
                    <?php else: ?>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-6 w-6" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    <?php endif; ?>
                </span>
            </div>
        </section>
    <?php endforeach; ?>
</div>
<div class="mb-8">
    <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-700">Komposisi</p>
            <h2 class="mt-1 text-xl font-bold tracking-tight text-slate-950">Jumlah per sifat · seluruh periode</h2>
        </div>
        <span class="text-xs text-slate-500">Seluruh surat aktif</span>
    </div>
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <?php foreach ($summary['sifat'] as $sifat => $count): ?>
            <?php $meta = $sifatMeta[$sifat] ?? $sifatMeta['biasa']; ?>
            <section class="rounded-2xl bg-linear-to-br <?= e($meta['panel']) ?> p-5 shadow-sm ring-1 <?= e($meta['ring']) ?>">
                <div class="flex items-center justify-between gap-3">
                    <h3 class="flex items-center gap-2 text-sm font-semibold <?= e($meta['text']) ?>"><span class="h-2 w-2 rounded-full <?= e($meta['dot']) ?>"></span><?= e(ucfirst($sifat)) ?></h3>
                    <span class="text-[11px] font-medium uppercase tracking-wide text-slate-400">Sifat</span>
                </div>
                <p class="mt-3 text-3xl font-bold tracking-tight text-slate-950"><?= e($count) ?></p>
                <p class="mt-1 text-xs text-slate-500">surat tercatat</p>
            </section>
        <?php endforeach; ?>
    </div>
</div>
<section class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
    <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-100 px-5 py-4 sm:px-6">
        <div>
            <h2 class="text-lg font-bold tracking-tight text-slate-950">5 surat terbaru</h2>
            <p class="mt-0.5 text-xs text-slate-500">Nomor agenda terbaru di atas</p>
        </div>
        <a href="/surat" class="inline-flex items-center gap-1.5 text-sm font-semibold text-blue-700 transition hover:text-blue-900">
            Semua surat
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
        </a>
    </div>
    <?php if ($summary['terbaru'] === []): ?>
        <div class="px-6 py-14 text-center">
            <span class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-slate-100 text-slate-400">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-7 w-7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/></svg>
            </span>
            <p class="mt-4 text-sm font-medium text-slate-700">Belum ada surat masuk.</p>
            <a href="/surat/tambah" class="mt-3 inline-flex text-sm font-semibold text-blue-700 hover:underline">Catat surat pertama</a>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50/80 text-[11px] uppercase tracking-wider text-slate-500">
                    <tr>
                        <?php foreach (['No. Agenda', 'Tgl Diterima', 'Asal surat', 'Perihal'] as $heading): ?>
                            <th scope="col" class="whitespace-nowrap px-5 py-3 font-semibold sm:px-6"><?= e($heading) ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach ($summary['terbaru'] as $row): ?>
                        <tr class="transition hover:bg-blue-50/40">
                            <td class="whitespace-nowrap px-5 py-4 font-semibold sm:px-6">
                                <a class="text-blue-700 transition hover:text-blue-900 hover:underline" href="/surat/<?= e($row['id']) ?>"><?= e(AgendaNumber::format($row['nomor_urut'], $row['tahun_agenda'])) ?></a>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-slate-600 sm:px-6"><?= e(tanggal($row['tanggal_diterima'])) ?></td>
                            <td class="px-5 py-4 text-slate-700 sm:px-6"><?= e($row['asal_surat']) ?></td>
                            <td class="px-5 py-4 text-slate-700 sm:px-6"><?= e($row['perihal']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
