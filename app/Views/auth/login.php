<?php

declare(strict_types=1);

use App\Core\Csrf;

?>
<section class="overflow-hidden rounded-3xl bg-white shadow-2xl shadow-slate-900/10 ring-1 ring-slate-200 lg:grid lg:grid-cols-[1.05fr_1fr]" aria-labelledby="judul-login">
    <div class="relative hidden overflow-hidden bg-linear-to-br from-blue-950 via-blue-800 to-blue-600 p-10 text-white lg:flex lg:flex-col lg:justify-between">
        <div aria-hidden="true" class="absolute -right-24 -top-24 h-72 w-72 rounded-full border border-white/15"></div>
        <div aria-hidden="true" class="absolute -bottom-32 -left-20 h-80 w-80 rounded-full border border-white/10"></div>
        <div aria-hidden="true" class="absolute right-10 top-1/3 h-24 w-24 rounded-3xl bg-white/5 backdrop-blur"></div>
        <div class="relative">
            <span class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-blue-100 backdrop-blur">
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-300"></span>
                Sistem internal
            </span>
            <h2 class="mt-8 max-w-md text-3xl font-bold leading-tight tracking-tight">Kelola surat masuk dengan cepat, rapi, dan aman.</h2>
            <p class="mt-4 max-w-sm text-sm leading-6 text-blue-100/90">Satu tempat untuk pencatatan agenda, penelusuran surat, laporan, dan pengelolaan pengguna.</p>
            <ul class="mt-8 space-y-4 text-sm text-blue-50">
                <?php foreach (['Nomor agenda otomatis dan konsisten.', 'Pencarian serta filter surat yang presisi.', 'Laporan siap cetak dan ekspor CSV.'] as $feature): ?>
                    <li class="flex items-start gap-3">
                        <span class="mt-0.5 grid h-5 w-5 shrink-0 place-items-center rounded-full bg-emerald-400/20 text-emerald-200">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="h-3 w-3" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                        </span>
                        <?= e($feature) ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="relative mt-10 rounded-2xl border border-white/15 bg-white/10 p-4 backdrop-blur">
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-200">Alur kerja</p>
            <p class="mt-1 text-sm text-blue-50">Catat → telusuri → laporkan. Tanpa langkah yang berulang.</p>
        </div>
    </div>
    <div class="p-6 sm:p-10">
        <div class="mx-auto max-w-sm">
            <p class="text-sm font-semibold text-blue-700">Selamat datang</p>
            <h1 id="judul-login" class="mt-2 text-3xl font-bold tracking-tight text-slate-950">Masuk ke <?= e(config('APP_NAME')) ?></h1>
            <p class="mt-3 text-sm leading-6 text-slate-600">Gunakan akun Anda untuk mengelola administrasi surat masuk.</p>
            <form method="post" action="/login" class="mt-8 space-y-5">
                <input type="hidden" name="_token" value="<?= e(Csrf::token()) ?>">
                <div>
                    <label for="username" class="mb-2 block text-sm font-semibold text-slate-700">Username</label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 grid w-11 place-items-center text-slate-400">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.25a7.5 7.5 0 0 1 15 0"/></svg>
                        </span>
                        <input id="username" name="username" type="text" required maxlength="50" autocomplete="username" autocapitalize="none" spellcheck="false"
                            value="<?= e($old['username'] ?? '') ?>" aria-invalid="<?= e(isset($errors['username']) ? 'true' : 'false') ?>" aria-describedby="username-error"
                            class="block w-full rounded-xl border border-slate-300 bg-white py-3 pl-11 pr-3 text-sm text-slate-900 shadow-sm transition placeholder:text-slate-400 focus:border-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-600/10">
                    </div>
                    <p id="username-error" class="mt-2 text-sm text-red-600" role="alert"><?= e($errors['username'] ?? '') ?></p>
                </div>
                <div>
                    <div class="mb-2 flex items-center justify-between">
                        <label for="password" class="block text-sm font-semibold text-slate-700">Kata sandi</label>
                    </div>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 grid w-11 place-items-center text-slate-400">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m9 0h-9a1.5 1.5 0 0 0-1.5 1.5v6a1.5 1.5 0 0 0 1.5 1.5h9a1.5 1.5 0 0 0 1.5-1.5v-6a1.5 1.5 0 0 0-1.5-1.5Z"/></svg>
                        </span>
                        <input id="password" name="password" type="password" required autocomplete="current-password"
                            aria-invalid="<?= e(isset($errors['password']) ? 'true' : 'false') ?>" aria-describedby="password-error"
                            class="block w-full rounded-xl border border-slate-300 bg-white py-3 pl-11 pr-3 text-sm text-slate-900 shadow-sm transition placeholder:text-slate-400 focus:border-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-600/10">
                    </div>
                    <p id="password-error" class="mt-2 text-sm text-red-600" role="alert"><?= e($errors['password'] ?? '') ?></p>
                </div>
                <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-linear-to-r from-blue-700 to-blue-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-700/25 transition hover:from-blue-800 hover:to-blue-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700">
                    Masuk
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                </button>
            </form>
            <p class="mt-6 border-t border-slate-100 pt-5 text-center text-xs leading-5 text-slate-500">Akses hanya untuk pengguna yang terdaftar. Hubungi Admin bila akun Anda belum aktif.</p>
        </div>
    </div>
</section>
