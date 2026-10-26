<?php

declare(strict_types=1);

use App\Core\Csrf;
?>
<a href="/pengguna" class="inline-flex items-center gap-1.5 text-sm font-semibold text-blue-700 transition hover:text-blue-900">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
    Kembali ke daftar pengguna
</a>
<div class="mt-4 mb-8">
    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-700">Akun pengguna</p>
    <h1 class="mt-1 text-3xl font-bold tracking-tight text-slate-950"><?= e($title) ?></h1>
    <p class="mt-2 text-sm text-slate-600"><?= e($id === null ? 'Buat akun baru dengan peran dan kata sandi awal.' : 'Perbarui identitas, peran, atau kata sandi pengguna.') ?></p>
</div>
<div class="grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_18rem]">
    <div class="space-y-6">
        <form method="post" action="<?= e($id === null ? '/pengguna' : '/pengguna/' . $id) ?>" class="space-y-5 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 sm:p-7">
            <input type="hidden" name="_token" value="<?= e(Csrf::token()) ?>">
            <div>
                <h2 class="text-base font-bold text-slate-950">Informasi akun</h2>
                <p class="mt-1 text-xs text-slate-500">Username tidak boleh mengandung spasi atau huruf besar.</p>
            </div>
            <?php foreach (['username' => ['Username', 'Contoh: operator.surat', 50], 'nama_lengkap' => ['Nama lengkap', 'Nama sesuai identitas', 100]] as $field => [$label, $placeholder, $max]): ?>
                <div>
                    <label for="<?= e($field) ?>" class="mb-2 block text-sm font-semibold text-slate-700"><?= e($label) ?> <span class="text-red-600">*</span></label>
                    <input id="<?= e($field) ?>" name="<?= e($field) ?>" value="<?= e($old[$field] ?? '') ?>" maxlength="<?= e($max) ?>" required
                        placeholder="<?= e($placeholder) ?>" aria-invalid="<?= e(isset($errors[$field]) ? 'true' : 'false') ?>" aria-describedby="<?= e($field) ?>-error"
                        class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 shadow-sm transition placeholder:text-slate-400 focus:border-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-600/10">
                    <p id="<?= e($field) ?>-error" role="alert" class="mt-2 text-sm text-red-600"><?= e($errors[$field] ?? '') ?></p>
                </div>
            <?php endforeach; ?>
            <div>
                <label for="peran" class="mb-2 block text-sm font-semibold text-slate-700">Peran <span class="text-red-600">*</span></label>
                <select id="peran" name="peran" aria-invalid="<?= e(isset($errors['peran']) ? 'true' : 'false') ?>" aria-describedby="peran-error"
                    class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 shadow-sm transition focus:border-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-600/10">
                    <?php foreach (['operator' => 'Operator', 'admin' => 'Admin'] as $value => $label): ?>
                        <option value="<?= e($value) ?>" <?= ($old['peran'] ?? '') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['peran'])): ?><p id="peran-error" role="alert" class="mt-2 text-sm text-red-600"><?= e($errors['peran']) ?></p><?php endif; ?>
            </div>
            <?php if ($id === null): ?>
                <div class="border-t border-slate-100 pt-5">
                    <label for="password" class="mb-2 block text-sm font-semibold text-slate-700">Kata sandi awal <span class="text-red-600">*</span></label>
                    <input type="password" id="password" name="password" autocomplete="new-password" minlength="8" required
                        aria-invalid="<?= e(isset($errors['password']) ? 'true' : 'false') ?>" aria-describedby="password-help password-error"
                        class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 shadow-sm transition focus:border-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-600/10">
                    <p id="password-help" class="mt-2 text-xs text-slate-500">Minimal 8 karakter, maksimal 72 byte. Pengguna wajib mengganti saat masuk.</p>
                    <p id="password-error" role="alert" class="mt-2 text-sm text-red-600"><?= e($errors['password'] ?? '') ?></p>
                </div>
            <?php endif; ?>
            <div class="flex flex-wrap items-center gap-3 border-t border-slate-100 pt-5">
                <button class="inline-flex items-center gap-2 rounded-xl bg-linear-to-r from-blue-700 to-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-blue-700/20 transition hover:from-blue-800 hover:to-blue-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                    Simpan
                </button>
                <a href="/pengguna" class="inline-flex items-center rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-100 hover:text-slate-900">Kembali</a>
            </div>
        </form>
        <?php if ($id !== null): ?>
            <form method="post" action="/pengguna/<?= e($id) ?>/reset-password" onsubmit="return confirm('Reset kata sandi pengguna ini?')" class="space-y-4 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 sm:p-7">
                <input type="hidden" name="_token" value="<?= e(Csrf::token()) ?>">
                <div class="flex items-start gap-3">
                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-amber-50 text-amber-600 ring-1 ring-amber-100">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m9 0h-9a1.5 1.5 0 0 0-1.5 1.5v6a1.5 1.5 0 0 0 1.5 1.5h9a1.5 1.5 0 0 0 1.5-1.5v-6a1.5 1.5 0 0 0-1.5-1.5Z"/></svg>
                    </span>
                    <div>
                        <h2 class="text-base font-bold text-slate-950">Reset kata sandi</h2>
                        <p class="mt-1 text-sm leading-6 text-slate-600">Pengguna akan diminta mengganti kata sandi ini saat masuk berikutnya.</p>
                    </div>
                </div>
                <div>
                    <label for="password_reset" class="mb-2 block text-sm font-semibold text-slate-700">Kata sandi baru</label>
                    <input type="password" id="password_reset" name="password" autocomplete="new-password" minlength="8" required
                        aria-invalid="<?= e(isset($errors['password']) ? 'true' : 'false') ?>" aria-describedby="password-reset-help password-reset-error"
                        class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 shadow-sm transition focus:border-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-600/10">
                    <p id="password-reset-help" class="mt-2 text-xs text-slate-500">Minimal 8 karakter, maksimal 72 byte.</p>
                    <p id="password-reset-error" role="alert" class="mt-2 text-sm text-red-600"><?= e($errors['password'] ?? '') ?></p>
                </div>
                <button class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">Reset kata sandi</button>
            </form>
        <?php endif; ?>
    </div>
    <aside class="rounded-2xl bg-linear-to-br from-slate-900 to-slate-800 p-5 text-white shadow-lg shadow-slate-900/10 lg:sticky lg:top-24">
        <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.16em] text-slate-300">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
            Peran akses
        </div>
        <div class="mt-4 space-y-3 text-sm leading-6 text-slate-300">
            <div class="rounded-xl border border-white/10 bg-white/5 p-3"><span class="font-semibold text-white">Admin</span><br>Mengelola surat, laporan, dan seluruh pengguna.</div>
            <div class="rounded-xl border border-white/10 bg-white/5 p-3"><span class="font-semibold text-white">Operator</span><br>Mencatat, mencari, mengubah, dan melaporkan surat.</div>
        </div>
        <p class="mt-4 text-xs leading-5 text-slate-400">Sistem selalu menjaga minimal satu Admin aktif.</p>
    </aside>
</div>
