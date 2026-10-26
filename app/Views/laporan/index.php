<?php

declare(strict_types=1);
?>
<div class="mb-8">
    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-700">Pelaporan</p>
    <h1 class="mt-1 text-3xl font-bold tracking-tight text-slate-950">Laporan surat masuk</h1>
    <p class="mt-2 text-sm text-slate-600">Saring berdasarkan periode, sifat, dan asal surat. Cetak atau ekspor hasilnya.</p>
</div>
<form method="get" action="/laporan" class="mb-6 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
    <div class="mb-4 flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4 text-blue-700" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
        Filter laporan
    </div>
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <?php foreach (['dari' => 'Dari tanggal', 'sampai' => 'Sampai tanggal', 'asal' => 'Asal surat'] as $field => $label): ?>
            <div>
                <label for="<?= e($field) ?>" class="mb-2 block text-sm font-semibold text-slate-700"><?= e($label) ?></label>
                <input id="<?= e($field) ?>" name="<?= e($field) ?>" type="<?= e($field === 'asal' ? 'text' : 'date') ?>" value="<?= e($filters[$field]) ?>"
                    <?= $field !== 'asal' ? 'required' : '' ?> aria-describedby="<?= e($field) ?>-error"
                    class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 shadow-sm transition placeholder:text-slate-400 focus:border-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-600/10">
                <?php if (isset($errors[$field])): ?><p id="<?= e($field) ?>-error" role="alert" class="mt-2 text-sm text-red-600"><?= e($errors[$field]) ?></p><?php endif; ?>
            </div>
        <?php endforeach; ?>
        <div>
            <label for="sifat" class="mb-2 block text-sm font-semibold text-slate-700">Sifat</label>
            <select id="sifat" name="sifat" aria-describedby="sifat-error" class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 shadow-sm transition focus:border-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-600/10">
                <?php foreach (['' => 'Semua sifat', 'biasa' => 'Biasa', 'penting' => 'Penting', 'segera' => 'Segera', 'rahasia' => 'Rahasia'] as $value => $label): ?>
                    <option value="<?= e($value) ?>" <?= $filters['sifat'] === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
            <?php if (isset($errors['sifat'])): ?><p id="sifat-error" role="alert" class="mt-2 text-sm text-red-600"><?= e($errors['sifat']) ?></p><?php endif; ?>
        </div>
        <div class="flex flex-wrap items-center gap-3 sm:col-span-2 lg:col-span-4">
            <button class="inline-flex items-center gap-2 rounded-xl bg-linear-to-r from-blue-700 to-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-blue-700/20 transition hover:from-blue-800 hover:to-blue-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                Tampilkan laporan
            </button>
            <a class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50" href="/laporan?<?= e(http_build_query(['dari' => date('Y-01-01'), 'sampai' => date('Y-12-31')])) ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"/></svg>
                Buku Agenda Tahun Ini
            </a>
        </div>
    </div>
</form>
<?php if ($errors === []): ?>
    <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
        <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-100 px-5 py-4 sm:px-6">
            <div>
                <h2 class="text-base font-bold text-slate-950">Pratinjau laporan</h2>
                <p class="mt-0.5 text-xs text-slate-500"><?= e(count($rows)) ?> surat · <?= e(tanggal($filters['dari'])) ?> – <?= e(tanggal($filters['sampai'])) ?></p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a class="inline-flex items-center gap-2 rounded-xl bg-blue-700 px-3.5 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-800" href="/laporan/cetak?<?= e(http_build_query($filters)) ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.615 0-1.118-.49-1.12-1.105L6.34 18m11.32 0H6.34m11.32 0c.66 0 1.2-.54 1.2-1.2V9.6c0-.66-.54-1.2-1.2-1.2H6.34c-.66 0-1.2.54-1.2 1.2v7.2c0 .66.54 1.2 1.2 1.2M15 7.5V4.5h-6v3"/></svg>
                    Cetak A4
                </a>
                <a class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50" href="/laporan/ekspor?<?= e(http_build_query($filters)) ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                    Ekspor CSV
                </a>
            </div>
        </div>
        <?php require __DIR__ . '/_table.php'; ?>
    </div>
<?php endif; ?>
