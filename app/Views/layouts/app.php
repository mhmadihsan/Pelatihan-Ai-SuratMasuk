<?php

declare(strict_types=1);

use App\Core\Csrf;

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$isActive = static function (string $prefix) use ($path): bool {
    return $prefix === '/' ? $path === '/' : $path === $prefix || str_starts_with($path, $prefix . '/');
};
$navClass = static fn (bool $active): string => $active
    ? 'bg-white text-blue-800 shadow-sm ring-1 ring-slate-200'
    : 'text-slate-600 hover:bg-white/70 hover:text-slate-900';
$initial = $currentUser === null ? '' : mb_strtoupper(mb_substr($currentUser['nama_lengkap'], 0, 1, 'UTF-8'), 'UTF-8');
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?> · <?= e(config('APP_NAME')) ?></title>
    <link rel="stylesheet" href="/assets/app.css">
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 antialiased selection:bg-blue-200/70 selection:text-blue-950">
    <a href="#konten" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-lg focus:bg-white focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-blue-700 focus:shadow-lg focus:outline-2 focus:outline-blue-700">Lewati ke konten</a>
    <header class="sticky top-0 z-40 border-b border-slate-200/80 bg-white/85 backdrop-blur-md">
        <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-4 px-4 py-3.5 sm:px-6">
            <a href="/" class="group flex items-center gap-3">
                <span class="grid h-10 w-10 place-items-center rounded-xl bg-linear-to-br from-blue-600 to-blue-800 text-white shadow-lg shadow-blue-700/25 ring-1 ring-blue-500/20 transition group-hover:scale-105">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75A2.25 2.25 0 0 1 4.5 4.5h15a2.25 2.25 0 0 1 2.25 2.25v10.5A2.25 2.25 0 0 1 19.5 19.5h-15a2.25 2.25 0 0 1-2.25-2.25V6.75Z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="m3 7.5 8.1 5.4a1.5 1.5 0 0 0 1.8 0L21 7.5"/>
                    </svg>
                </span>
                <span class="leading-tight">
                    <span class="block text-lg font-bold tracking-tight text-slate-950"><?= e(config('APP_NAME')) ?></span>
                    <span class="block text-[10px] font-semibold uppercase tracking-[0.18em] text-slate-500">Administrasi surat masuk</span>
                </span>
            </a>
            <?php if ($currentUser !== null): ?>
                <nav aria-label="Navigasi utama" class="flex flex-wrap items-center gap-2 text-sm">
                    <div class="flex flex-wrap items-center gap-1 rounded-xl bg-slate-100/90 p-1 ring-1 ring-slate-200/70">
                        <?php if (!$currentUser['wajib_ganti_password']): ?>
                            <a href="/" class="inline-flex items-center gap-1.5 rounded-lg px-3 py-2 font-medium transition <?= e($navClass($isActive('/'))) ?>"<?= $isActive('/') ? ' aria-current="page"' : '' ?>>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 10.5h16.5M3.75 6.75h16.5M3.75 14.25h16.5M3.75 18h16.5"/></svg>
                                Dashboard
                            </a>
                            <a href="/surat" class="inline-flex items-center gap-1.5 rounded-lg px-3 py-2 font-medium transition <?= e($navClass($isActive('/surat'))) ?>"<?= $isActive('/surat') ? ' aria-current="page"' : '' ?>>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-8.25a2.25 2.25 0 0 0-2.25-2.25h-10.5A2.25 2.25 0 0 0 4.5 6v12a2.25 2.25 0 0 0 2.25 2.25h6.75"/><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 8.25h7.5M8.25 12h4.5M16.5 15.75v4.5M14.25 18h4.5"/></svg>
                                Surat
                            </a>
                            <a href="/laporan" class="inline-flex items-center gap-1.5 rounded-lg px-3 py-2 font-medium transition <?= e($navClass($isActive('/laporan'))) ?>"<?= $isActive('/laporan') ? ' aria-current="page"' : '' ?>>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 20.25h16.5M6 16.5V9m6 7.5V4.5m6 12v-4.5"/></svg>
                                Laporan
                            </a>
                            <?php if ($currentUser['peran'] === 'admin'): ?>
                                <a href="/pengguna" class="inline-flex items-center gap-1.5 rounded-lg px-3 py-2 font-medium transition <?= e($navClass($isActive('/pengguna'))) ?>"<?= $isActive('/pengguna') ? ' aria-current="page"' : '' ?>>
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.5a6 6 0 0 0-12 0"/><circle cx="9" cy="7.5" r="3.75"/><path stroke-linecap="round" stroke-linejoin="round" d="M18.75 8.25v4.5M16.5 10.5h4.5"/></svg>
                                    Pengguna
                                </a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <div class="ml-1 flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-2 py-1.5 shadow-sm">
                        <span class="grid h-8 w-8 place-items-center rounded-lg bg-linear-to-br from-slate-700 to-slate-900 text-xs font-bold text-white"><?= e($initial) ?></span>
                        <span class="hidden max-w-40 truncate text-sm font-semibold text-slate-700 sm:block"><?= e($currentUser['nama_lengkap']) ?></span>
                        <?php if (!$currentUser['wajib_ganti_password']): ?>
                            <a href="/ganti-password" class="rounded-md p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-blue-700" title="Ganti kata sandi" aria-label="Ganti kata sandi">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m9 0h-9a1.5 1.5 0 0 0-1.5 1.5v6a1.5 1.5 0 0 0 1.5 1.5h9a1.5 1.5 0 0 0 1.5-1.5v-6a1.5 1.5 0 0 0-1.5-1.5Z"/></svg>
                            </a>
                        <?php endif; ?>
                        <form method="post" action="/logout">
                            <input type="hidden" name="_token" value="<?= e(Csrf::token()) ?>">
                            <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-sm font-semibold text-slate-600 transition hover:bg-red-50 hover:text-red-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6A2.25 2.25 0 0 0 5.25 5.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3-3H9m9 0-3-3m3 3-3 3"/></svg>
                                Keluar
                            </button>
                        </form>
                    </div>
                </nav>
            <?php else: ?>
                <span class="rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-500">Sistem internal</span>
            <?php endif; ?>
        </div>
    </header>
    <main id="konten" class="<?= $currentUser === null ? 'mx-auto flex min-h-[calc(100vh-9rem)] max-w-6xl items-center px-4 py-10 sm:px-6' : 'mx-auto max-w-6xl px-4 py-8 sm:px-6 sm:py-10' ?>">
        <div class="w-full">
            <?php if ($flash !== null): ?>
                <div role="status" class="mb-6 flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900 shadow-sm">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mt-0.5 h-5 w-5 shrink-0 text-emerald-600" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                    <span><?= e($flash) ?></span>
                </div>
            <?php endif; ?>
            <?php require $templatePath; ?>
        </div>
    </main>
    <footer class="border-t border-slate-200/70 bg-white/60">
        <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-2 px-4 py-5 text-xs text-slate-500 sm:px-6">
            <span><?= e(config('APP_NAME')) ?> · Administrasi surat masuk</span>
            <span>Sistem internal · Akses terbatas</span>
        </div>
    </footer>
</body>
</html>
