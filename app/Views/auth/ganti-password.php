<?php

declare(strict_types=1);

use App\Core\Csrf;

$fields = [
    'password_lama' => ['Kata sandi saat ini', 'current-password'],
    'password_baru' => ['Kata sandi baru', 'new-password'],
    'konfirmasi_password' => ['Konfirmasi kata sandi baru', 'new-password'],
];
?>
<section class="mx-auto max-w-xl overflow-hidden rounded-3xl bg-white shadow-xl shadow-slate-900/5 ring-1 ring-slate-200" aria-labelledby="judul-password">
    <div class="relative overflow-hidden border-b border-blue-100 bg-linear-to-br from-blue-50 via-white to-slate-50 px-6 py-6 sm:px-8">
        <div aria-hidden="true" class="absolute -right-8 -top-10 h-28 w-28 rounded-full bg-blue-100/70"></div>
        <div class="relative flex items-start gap-4">
            <span class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-blue-700 text-white shadow-lg shadow-blue-700/20">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m9 0h-9a1.5 1.5 0 0 0-1.5 1.5v6a1.5 1.5 0 0 0 1.5 1.5h9a1.5 1.5 0 0 0 1.5-1.5v-6a1.5 1.5 0 0 0-1.5-1.5Z"/></svg>
            </span>
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-700">Keamanan akun</p>
                <h1 id="judul-password" class="mt-1 text-2xl font-bold tracking-tight text-slate-950">Ganti kata sandi</h1>
                <p class="mt-2 text-sm leading-6 text-slate-600">Gunakan kata sandi yang kuat dan berbeda dari kata sandi saat ini.</p>
            </div>
        </div>
    </div>
    <div class="px-6 py-6 sm:px-8">
        <?php if ($currentUser['wajib_ganti_password']): ?>
            <div class="mb-6 flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm leading-6 text-amber-900" role="status">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="mt-0.5 h-5 w-5 shrink-0 text-amber-600" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008v.008H12v-.008ZM10.34 3.94 2.7 17.1a1.5 1.5 0 0 0 1.3 2.25h16a1.5 1.5 0 0 0 1.3-2.25L13.66 3.94a1.5 1.5 0 0 0-2.6 0Z"/></svg>
                <span>Untuk keamanan akun, ganti kata sandi awal Anda sebelum menggunakan aplikasi.</span>
            </div>
        <?php endif; ?>
        <p id="password-help" class="mb-6 rounded-xl bg-slate-50 px-4 py-3 text-xs leading-5 text-slate-600 ring-1 ring-slate-200">Kata sandi baru minimal 8 karakter, maksimal 72 byte.</p>
        <form method="post" action="/ganti-password" class="space-y-5">
            <input type="hidden" name="_token" value="<?= e(Csrf::token()) ?>">
            <?php foreach ($fields as $field => [$label, $autocomplete]): ?>
                <div>
                    <label for="<?= e($field) ?>" class="mb-2 block text-sm font-semibold text-slate-700"><?= e($label) ?></label>
                    <input id="<?= e($field) ?>" name="<?= e($field) ?>" type="password" required autocomplete="<?= e($autocomplete) ?>"
                        aria-invalid="<?= e(isset($errors[$field]) ? 'true' : 'false') ?>" aria-describedby="<?= e($field) ?>-error password-help"
                        class="block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-3 text-sm text-slate-900 shadow-sm transition focus:border-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-600/10">
                    <p id="<?= e($field) ?>-error" class="mt-2 text-sm text-red-600" role="alert"><?= e($errors[$field] ?? '') ?></p>
                </div>
            <?php endforeach; ?>
            <div class="flex flex-col gap-3 pt-1 sm:flex-row sm:items-center">
                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-linear-to-r from-blue-700 to-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-700/20 transition hover:from-blue-800 hover:to-blue-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700">
                    Simpan kata sandi
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                </button>
                <?php if (!$currentUser['wajib_ganti_password']): ?>
                    <a href="/" class="inline-flex items-center justify-center rounded-xl px-4 py-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-100 hover:text-slate-900">Batal</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</section>
