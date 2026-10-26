<?php

declare(strict_types=1);

use App\Core\Csrf;

$fields = [
    'nomor_surat' => ['Nomor surat', 'text', 100, 'Contoh: 005/12/2026'],
    'tanggal_surat' => ['Tanggal surat', 'date', null, null],
    'tanggal_diterima' => ['Tanggal diterima', 'date', null, null],
    'asal_surat' => ['Asal surat', 'text', 150, 'Instansi atau pengirim surat'],
    'perihal' => ['Perihal', 'text', 255, 'Ringkasan isi surat'],
];
?>
<a href="/surat" class="inline-flex items-center gap-1.5 text-sm font-semibold text-blue-700 transition hover:text-blue-900">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
    Kembali ke daftar surat
</a>
<div class="mt-4 mb-8">
    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-700">Pencatatan</p>
    <h1 class="mt-1 text-3xl font-bold tracking-tight text-slate-950"><?= e($title) ?></h1>
    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600"><?= e($id === null
        ? 'Nomor agenda dibuat otomatis setelah surat berhasil disimpan.'
        : 'Nomor agenda tetap sama setelah data surat diubah.') ?></p>
</div>
<div class="grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_18rem]">
    <form method="post" action="<?= e($id === null ? '/surat' : '/surat/' . $id) ?>" class="space-y-6 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 sm:p-7">
        <input type="hidden" name="_token" value="<?= e(Csrf::token()) ?>">
        <?php if ($duplicate): ?>
            <div role="alert" class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                <div class="flex items-start gap-3">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="mt-0.5 h-5 w-5 shrink-0 text-amber-600" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008v.008H12v-.008ZM10.34 3.94 2.7 17.1a1.5 1.5 0 0 0 1.3 2.25h16a1.5 1.5 0 0 0 1.3-2.25L13.66 3.94a1.5 1.5 0 0 0-2.6 0Z"/></svg>
                    <div>
                        <p class="font-semibold">Kemungkinan surat duplikat</p>
                        <p class="mt-1 leading-6">Nomor surat dan asal surat yang sama sudah tercatat. Periksa kembali sebelum melanjutkan.</p>
                    </div>
                </div>
                <label for="konfirmasi_duplikat" class="mt-4 flex items-start gap-3 rounded-lg bg-white/70 p-3 font-medium ring-1 ring-amber-200">
                    <input id="konfirmasi_duplikat" name="konfirmasi_duplikat" type="checkbox" value="1" required class="mt-0.5 h-4 w-4 rounded border-amber-300 text-blue-700 focus:ring-blue-600">
                    Saya sudah memeriksa dan ingin tetap menyimpan surat ini.
                </label>
            </div>
        <?php endif; ?>
        <?php if (isset($errors['konfirmasi_duplikat'])): ?>
            <p role="alert" class="rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700"><?= e($errors['konfirmasi_duplikat']) ?></p>
        <?php endif; ?>
        <div>
            <h2 class="text-base font-bold text-slate-950">Informasi surat</h2>
            <p class="mt-1 text-xs text-slate-500">Kolom bertanda wajib harus diisi.</p>
        </div>
        <div class="grid gap-5 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label for="nomor_surat" class="mb-2 block text-sm font-semibold text-slate-700">Nomor surat <span class="text-red-600">*</span></label>
                <input id="nomor_surat" name="nomor_surat" type="text" required maxlength="100" placeholder="Contoh: 005/12/2026"
                    value="<?= e($old['nomor_surat'] ?? '') ?>" aria-invalid="<?= e(isset($errors['nomor_surat']) ? 'true' : 'false') ?>"
                    aria-describedby="nomor_surat-error"
                    class="block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 shadow-sm transition placeholder:text-slate-400 focus:border-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-600/10">
                <p id="nomor_surat-error" role="alert" class="mt-2 text-sm text-red-600"><?= e($errors['nomor_surat'] ?? '') ?></p>
            </div>
            <?php foreach (['tanggal_surat', 'tanggal_diterima'] as $field): ?>
                <?php [$label, $type, $max] = $fields[$field]; ?>
                <div>
                    <label for="<?= e($field) ?>" class="mb-2 block text-sm font-semibold text-slate-700"><?= e($label) ?> <span class="text-red-600">*</span></label>
                    <input id="<?= e($field) ?>" name="<?= e($field) ?>" type="<?= e($type) ?>" required
                        <?php if ($field === 'tanggal_diterima'): ?>max="<?= e(date('Y-m-d')) ?>"<?php endif; ?>
                        value="<?= e($old[$field] ?? '') ?>" aria-invalid="<?= e(isset($errors[$field]) ? 'true' : 'false') ?>"
                        aria-describedby="<?= e($field) ?>-error"
                        class="block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 shadow-sm transition focus:border-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-600/10">
                    <p id="<?= e($field) ?>-error" role="alert" class="mt-2 text-sm text-red-600"><?= e($errors[$field] ?? '') ?></p>
                </div>
            <?php endforeach; ?>
            <div class="sm:col-span-2">
                <label for="asal_surat" class="mb-2 block text-sm font-semibold text-slate-700">Asal surat <span class="text-red-600">*</span></label>
                <input id="asal_surat" name="asal_surat" type="text" required maxlength="150" placeholder="Instansi atau pengirim surat"
                    value="<?= e($old['asal_surat'] ?? '') ?>" aria-invalid="<?= e(isset($errors['asal_surat']) ? 'true' : 'false') ?>"
                    aria-describedby="asal_surat-error"
                    class="block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 shadow-sm transition placeholder:text-slate-400 focus:border-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-600/10">
                <p id="asal_surat-error" role="alert" class="mt-2 text-sm text-red-600"><?= e($errors['asal_surat'] ?? '') ?></p>
            </div>
            <div class="sm:col-span-2">
                <label for="perihal" class="mb-2 block text-sm font-semibold text-slate-700">Perihal <span class="text-red-600">*</span></label>
                <input id="perihal" name="perihal" type="text" required maxlength="255" placeholder="Ringkasan isi surat"
                    value="<?= e($old['perihal'] ?? '') ?>" aria-invalid="<?= e(isset($errors['perihal']) ? 'true' : 'false') ?>"
                    aria-describedby="perihal-error"
                    class="block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 shadow-sm transition placeholder:text-slate-400 focus:border-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-600/10">
                <p id="perihal-error" role="alert" class="mt-2 text-sm text-red-600"><?= e($errors['perihal'] ?? '') ?></p>
            </div>
        </div>
        <div class="border-t border-slate-100 pt-6">
            <h2 class="text-base font-bold text-slate-950">Detail tambahan</h2>
            <p class="mt-1 text-xs text-slate-500">Tentukan sifat, lampiran, dan catatan bila diperlukan.</p>
        </div>
        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="sifat" class="mb-2 block text-sm font-semibold text-slate-700">Sifat <span class="text-red-600">*</span></label>
                <select id="sifat" name="sifat" required aria-invalid="<?= e(isset($errors['sifat']) ? 'true' : 'false') ?>"
                    aria-describedby="sifat-error" class="block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 shadow-sm transition focus:border-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-600/10">
                    <?php foreach (['biasa' => 'Biasa', 'penting' => 'Penting', 'segera' => 'Segera', 'rahasia' => 'Rahasia'] as $value => $label): ?>
                        <option value="<?= e($value) ?>" <?= e(($old['sifat'] ?? '') === $value ? 'selected' : '') ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
                <p id="sifat-error" role="alert" class="mt-2 text-sm text-red-600"><?= e($errors['sifat'] ?? '') ?></p>
            </div>
            <div>
                <label for="jumlah_lampiran" class="mb-2 block text-sm font-semibold text-slate-700">Jumlah lampiran <span class="text-red-600">*</span></label>
                <input id="jumlah_lampiran" name="jumlah_lampiran" type="number" min="0" max="65535" step="1" required
                    value="<?= e($old['jumlah_lampiran'] ?? '0') ?>" aria-invalid="<?= e(isset($errors['jumlah_lampiran']) ? 'true' : 'false') ?>"
                    aria-describedby="jumlah_lampiran-error"
                    class="block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 shadow-sm transition focus:border-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-600/10">
                <p id="jumlah_lampiran-error" role="alert" class="mt-2 text-sm text-red-600"><?= e($errors['jumlah_lampiran'] ?? '') ?></p>
            </div>
            <div class="sm:col-span-2">
                <label for="keterangan" class="mb-2 block text-sm font-semibold text-slate-700">Keterangan <span class="font-normal text-slate-400">(opsional)</span></label>
                <textarea id="keterangan" name="keterangan" rows="4" aria-invalid="<?= e(isset($errors['keterangan']) ? 'true' : 'false') ?>"
                    aria-describedby="keterangan-error" placeholder="Catatan tambahan mengenai surat ini…"
                    class="block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 shadow-sm transition placeholder:text-slate-400 focus:border-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-600/10"><?= e($old['keterangan'] ?? '') ?></textarea>
                <p id="keterangan-error" role="alert" class="mt-2 text-sm text-red-600"><?= e($errors['keterangan'] ?? '') ?></p>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-3 border-t border-slate-100 pt-6">
            <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-linear-to-r from-blue-700 to-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-blue-700/20 transition hover:from-blue-800 hover:to-blue-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75 10.5 18.75 19.5 5.25"/></svg>
                Simpan surat
            </button>
            <a href="/surat" class="inline-flex items-center rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-100 hover:text-slate-900">Batal</a>
        </div>
    </form>
    <aside class="rounded-2xl bg-linear-to-br from-slate-900 to-slate-800 p-5 text-white shadow-lg shadow-slate-900/10 lg:sticky lg:top-24">
        <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.16em] text-slate-300">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/></svg>
            Panduan singkat
        </div>
        <ul class="mt-4 space-y-4 text-sm leading-6 text-slate-300">
            <li><span class="font-semibold text-white">Nomor agenda</span><br>Dibuat otomatis dan tidak berubah saat data diubah.</li>
            <li><span class="font-semibold text-white">Tanggal diterima</span><br>Tidak boleh lebih awal dari tanggal surat atau melewati hari ini.</li>
            <li><span class="font-semibold text-white">Duplikat</span><br>Sistem memperingatkan bila nomor dan asal surat sama.</li>
        </ul>
        <div class="mt-5 rounded-xl border border-white/10 bg-white/5 p-3 text-xs leading-5 text-slate-300">Pastikan data diperiksa kembali sebelum menyimpan.</div>
    </aside>
</div>
