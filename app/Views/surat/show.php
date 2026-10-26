<?php

declare(strict_types=1);

use App\Support\AgendaNumber;

$agenda = AgendaNumber::format($surat['nomor_urut'], $surat['tahun_agenda']);
?>
<a href="/surat" class="inline-flex items-center gap-1.5 text-sm font-semibold text-blue-700 transition hover:text-blue-900">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
    Kembali ke daftar surat
</a>
<div class="mt-4 mb-8 flex flex-wrap items-start justify-between gap-4">
    <div>
        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-700">Arsip surat</p>
        <div class="mt-1 flex flex-wrap items-center gap-3">
            <h1 class="text-3xl font-bold tracking-tight text-slate-950">Detail surat masuk</h1>
            <?php $sifat = $surat['sifat']; require __DIR__ . '/_sifat.php'; ?>
        </div>
        <p class="mt-2 text-sm text-slate-600">Nomor agenda <span class="font-semibold text-slate-800"><?= e($agenda) ?></span> · diterima <?= e(tanggal($surat['tanggal_diterima'])) ?></p>
    </div>
    <div class="mt-1">
        <?php require __DIR__ . '/_actions.php'; ?>
    </div>
</div>
<section class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
    <div class="relative overflow-hidden border-b border-blue-100 bg-linear-to-r from-blue-50 via-white to-slate-50 px-5 py-5 sm:px-7">
        <div aria-hidden="true" class="absolute -right-10 -top-16 h-36 w-36 rounded-full bg-blue-100/60"></div>
        <div class="relative flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-700">Nomor agenda</p>
                <p class="mt-1 text-3xl font-bold tracking-tight text-slate-950"><?= e($agenda) ?></p>
            </div>
            <div class="flex items-center gap-2 rounded-xl bg-white px-3 py-2 text-xs font-medium text-slate-600 shadow-sm ring-1 ring-slate-200">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4 text-blue-700" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
                Diterima <?= e(tanggal($surat['tanggal_diterima'])) ?>
            </div>
        </div>
    </div>
    <dl class="grid gap-x-8 gap-y-6 p-5 sm:grid-cols-2 sm:p-7">
        <div>
            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Nomor surat</dt>
            <dd class="mt-1.5 break-words text-sm font-medium text-slate-900"><?= e($surat['nomor_surat']) ?></dd>
        </div>
        <div>
            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Asal surat</dt>
            <dd class="mt-1.5 break-words text-sm font-medium text-slate-900"><?= e($surat['asal_surat']) ?></dd>
        </div>
        <div>
            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Tanggal surat</dt>
            <dd class="mt-1.5 text-sm text-slate-900"><?= e(tanggal($surat['tanggal_surat'])) ?></dd>
        </div>
        <div>
            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Tanggal diterima</dt>
            <dd class="mt-1.5 text-sm text-slate-900"><?= e(tanggal($surat['tanggal_diterima'])) ?></dd>
        </div>
        <div class="sm:col-span-2">
            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Perihal</dt>
            <dd class="mt-1.5 break-words text-base font-semibold text-slate-950"><?= e($surat['perihal']) ?></dd>
        </div>
        <div>
            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Sifat</dt>
            <dd class="mt-1.5"><?php $sifat = $surat['sifat']; require __DIR__ . '/_sifat.php'; ?></dd>
        </div>
        <div>
            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Jumlah lampiran</dt>
            <dd class="mt-1.5 text-sm text-slate-900"><?= e($surat['jumlah_lampiran']) ?> lampiran</dd>
        </div>
        <div class="sm:col-span-2">
            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Keterangan</dt>
            <dd class="mt-1.5 whitespace-pre-wrap break-words rounded-xl bg-slate-50 p-4 text-sm leading-6 text-slate-700 ring-1 ring-slate-100"><?= e($surat['keterangan'] === null || $surat['keterangan'] === '' ? '—' : $surat['keterangan']) ?></dd>
        </div>
    </dl>
</section>
