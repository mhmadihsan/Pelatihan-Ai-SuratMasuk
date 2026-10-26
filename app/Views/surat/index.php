<?php

declare(strict_types=1);

use App\Support\AgendaNumber;

?>
<div class="mb-8 flex flex-wrap items-end justify-between gap-4">
    <div>
        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-700">Arsip surat</p>
        <h1 class="mt-1 text-3xl font-bold tracking-tight text-slate-950">Surat masuk</h1>
        <p class="mt-2 text-sm text-slate-600">Cari, saring, dan kelola seluruh surat masuk dalam satu tempat.</p>
    </div>
    <a href="/surat/tambah" class="inline-flex items-center gap-2 rounded-xl bg-linear-to-r from-blue-700 to-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-blue-700/20 transition hover:from-blue-800 hover:to-blue-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
        Tambah surat masuk
    </a>
</div>
<form method="get" action="/surat" class="mb-6 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
    <div class="mb-4 flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4 text-blue-700" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z"/></svg>
        Filter pencarian
    </div>
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-12">
        <div class="lg:col-span-5">
            <label for="q" class="mb-2 block text-sm font-semibold text-slate-700">Cari nomor surat, asal surat, atau perihal</label>
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-0 grid w-10 place-items-center text-slate-400">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                </span>
                <input id="q" name="q" type="search" value="<?= e($filters['q']) ?>" aria-describedby="q-error" placeholder="Ketik kata kunci…"
                    class="w-full rounded-xl border border-slate-300 bg-white py-2.5 pl-10 pr-3 text-sm text-slate-900 shadow-sm transition placeholder:text-slate-400 focus:border-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-600/10">
            </div>
            <p id="q-error" role="alert" class="mt-2 text-sm text-red-600"><?= e($errors['q'] ?? '') ?></p>
        </div>
        <div class="lg:col-span-3">
            <label for="filter-sifat" class="mb-2 block text-sm font-semibold text-slate-700">Sifat</label>
            <select id="filter-sifat" name="sifat" aria-describedby="sifat-error"
                class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm transition focus:border-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-600/10">
                <?php foreach (['' => 'Semua sifat', 'biasa' => 'Biasa', 'penting' => 'Penting', 'segera' => 'Segera', 'rahasia' => 'Rahasia'] as $value => $label): ?>
                    <option value="<?= e($value) ?>" <?= e($filters['sifat'] === $value ? 'selected' : '') ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
            <p id="sifat-error" role="alert" class="mt-2 text-sm text-red-600"><?= e($errors['sifat'] ?? '') ?></p>
        </div>
        <?php foreach (['dari' => 'Dari tanggal diterima', 'sampai' => 'Sampai tanggal diterima'] as $field => $label): ?>
            <div class="lg:col-span-2">
                <label for="<?= e($field) ?>" class="mb-2 block text-sm font-semibold text-slate-700"><?= e($label) ?></label>
                <input id="<?= e($field) ?>" name="<?= e($field) ?>" type="date" value="<?= e($filters[$field]) ?>"
                    aria-describedby="<?= e($field) ?>-error"
                    class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm transition focus:border-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-600/10">
                <p id="<?= e($field) ?>-error" role="alert" class="mt-2 text-sm text-red-600"><?= e($errors[$field] ?? '') ?></p>
            </div>
        <?php endforeach; ?>
        <div class="flex flex-wrap items-center gap-3 sm:col-span-2 lg:col-span-12">
            <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-blue-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                Terapkan filter
            </button>
            <a href="/surat" class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 hover:text-slate-900">Reset</a>
            <p role="alert" class="text-sm text-red-600"><?= e($errors['page'] ?? '') ?></p>
        </div>
    </div>
</form>
<?php if ($suratList === []): ?>
    <section class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center shadow-sm">
        <span class="mx-auto grid h-16 w-16 place-items-center rounded-2xl bg-blue-50 text-blue-600 ring-1 ring-blue-100">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-8 w-8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5 12 2.25 3.75 7.5m16.5 0-8.25 4.5m8.25-4.5v9L12 21.75m0-9L3.75 7.5m8.25 4.5v9.75M3.75 7.5v9L12 21.75"/></svg>
        </span>
        <h2 class="mt-5 text-lg font-bold text-slate-950"><?= e($errors !== [] ? 'Periksa filter pencarian' : 'Tidak ada surat masuk yang cocok') ?></h2>
        <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-600">Sesuaikan filter atau catat surat melalui tombol Tambah surat masuk.</p>
        <?php if ($errors === []): ?>
            <a href="/surat/tambah" class="mt-5 inline-flex items-center gap-2 rounded-xl bg-blue-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-800">Tambah surat masuk</a>
        <?php endif; ?>
    </section>
<?php else: ?>
    <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-4 sm:px-6">
            <div>
                <h2 class="text-base font-bold text-slate-950">Daftar surat</h2>
                <p class="mt-0.5 text-xs text-slate-500"><?= e($pagination['total']) ?> surat ditemukan</p>
            </div>
            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">Halaman <?= e($pagination['page']) ?> / <?= e($pagination['pages']) ?></span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <caption class="sr-only">Daftar surat masuk, nomor agenda terbaru di atas</caption>
                <thead class="bg-slate-50/80 text-[11px] uppercase tracking-wider text-slate-500">
                    <tr>
                        <?php foreach (['No. Agenda', 'No. Surat', 'Tgl Diterima', 'Asal Surat', 'Perihal', 'Sifat', 'Aksi'] as $label): ?>
                            <th scope="col" class="whitespace-nowrap px-4 py-3 font-semibold first:pl-5 last:pr-5 sm:first:pl-6 sm:last:pr-6"><?= e($label) ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach ($suratList as $surat): ?>
                        <tr class="transition hover:bg-blue-50/40">
                            <td class="whitespace-nowrap px-4 py-4 first:pl-5 sm:first:pl-6">
                                <a href="/surat/<?= e($surat['id']) ?>" class="font-semibold text-blue-700 transition hover:text-blue-900 hover:underline"><?= e(AgendaNumber::format($surat['nomor_urut'], $surat['tahun_agenda'])) ?></a>
                            </td>
                            <td class="px-4 py-4 text-slate-700"><?= e($surat['nomor_surat']) ?></td>
                            <td class="whitespace-nowrap px-4 py-4 text-slate-600"><?= e(tanggal($surat['tanggal_diterima'])) ?></td>
                            <td class="px-4 py-4 text-slate-700"><?= e($surat['asal_surat']) ?></td>
                            <td class="px-4 py-4 text-slate-700"><?= e($surat['perihal']) ?></td>
                            <td class="px-4 py-4"><?php $sifat = $surat['sifat']; require __DIR__ . '/_sifat.php'; ?></td>
                            <td class="px-4 py-4 last:pr-5 sm:last:pr-6">
                                <div class="flex flex-wrap items-center gap-3">
                                    <a href="/surat/<?= e($surat['id']) ?>" class="inline-flex items-center gap-1 text-sm font-semibold text-blue-700 hover:text-blue-900" aria-label="Detail surat <?= e(AgendaNumber::format($surat['nomor_urut'], $surat['tahun_agenda'])) ?>">
                                        Detail
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-3.5 w-3.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                                    </a>
                                    <?php require __DIR__ . '/_actions.php'; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>
<?php if ($errors === []): ?>
    <nav aria-label="Halaman daftar surat" class="mt-5 flex flex-wrap items-center justify-between gap-4 rounded-2xl bg-white px-5 py-4 text-sm shadow-sm ring-1 ring-slate-200">
        <p class="text-slate-600"><?= e($pagination['total']) ?> surat · Halaman <?= e($pagination['page']) ?> dari <?= e($pagination['pages']) ?></p>
        <div class="flex items-center gap-2">
            <?php if ($pagination['page'] > 1): ?>
                <a rel="prev" href="<?= e('/surat?' . http_build_query($filters + ['page' => $pagination['page'] - 1], encoding_type: PHP_QUERY_RFC3986)) ?>" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-2 font-semibold text-slate-700 transition hover:bg-slate-50">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                    Sebelumnya
                </a>
            <?php endif; ?>
            <?php if ($pagination['page'] < $pagination['pages']): ?>
                <a rel="next" href="<?= e('/surat?' . http_build_query($filters + ['page' => $pagination['page'] + 1], encoding_type: PHP_QUERY_RFC3986)) ?>" class="inline-flex items-center gap-1.5 rounded-lg bg-blue-700 px-3 py-2 font-semibold text-white transition hover:bg-blue-800">
                    Berikutnya
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                </a>
            <?php endif; ?>
        </div>
    </nav>
<?php endif; ?>
