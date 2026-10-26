<?php

declare(strict_types=1);

use App\Core\Csrf;
?>
<div class="mb-8 flex flex-wrap items-end justify-between gap-4">
    <div>
        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-700">Administrasi</p>
        <h1 class="mt-1 text-3xl font-bold tracking-tight text-slate-950">Pengguna</h1>
        <p class="mt-2 text-sm text-slate-600">Kelola akun, peran, status aktif, dan kata sandi pengguna.</p>
    </div>
    <a href="/pengguna/tambah" class="inline-flex items-center gap-2 rounded-xl bg-linear-to-r from-blue-700 to-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-blue-700/20 transition hover:from-blue-800 hover:to-blue-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3M6.75 7.5h.008v.008H6.75V7.5ZM9.75 7.5h.008v.008H9.75V7.5ZM12.75 7.5h.008v.008h-.008V7.5Z"/></svg>
        Tambah pengguna
    </a>
</div>
<div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-4 sm:px-6">
        <div>
            <h2 class="text-base font-bold text-slate-950">Daftar pengguna</h2>
            <p class="mt-0.5 text-xs text-slate-500"><?= e(count($users)) ?> akun terdaftar</p>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50/80 text-[11px] uppercase tracking-wider text-slate-500">
                <tr>
                    <?php foreach (['Username', 'Nama lengkap', 'Peran', 'Status', 'Aksi'] as $heading): ?>
                        <th scope="col" class="whitespace-nowrap px-4 py-3 font-semibold first:pl-5 last:pr-5 sm:first:pl-6 sm:last:pr-6"><?= e($heading) ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php foreach ($users as $item): ?>
                    <tr class="transition hover:bg-blue-50/40">
                        <td class="px-4 py-4 first:pl-5 sm:first:pl-6">
                            <div class="flex items-center gap-3">
                                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-linear-to-br from-slate-700 to-slate-900 text-xs font-bold text-white"><?= e(mb_strtoupper(mb_substr($item['nama_lengkap'], 0, 1, 'UTF-8'), 'UTF-8')) ?></span>
                                <span class="font-semibold text-slate-900"><?= e($item['username']) ?></span>
                            </div>
                        </td>
                        <td class="px-4 py-4 text-slate-700"><?= e($item['nama_lengkap']) ?></td>
                        <td class="px-4 py-4">
                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset <?= $item['peran'] === 'admin' ? 'bg-blue-50 text-blue-700 ring-blue-200' : 'bg-slate-100 text-slate-700 ring-slate-200' ?>"><?= e(ucfirst($item['peran'])) ?></span>
                        </td>
                        <td class="px-4 py-4">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset <?= $item['aktif'] ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-red-50 text-red-700 ring-red-200' ?>">
                                    <span class="h-1.5 w-1.5 rounded-full <?= $item['aktif'] ? 'bg-emerald-500' : 'bg-red-500' ?>" aria-hidden="true"></span>
                                    <?= e($item['aktif'] ? 'Aktif' : 'Nonaktif') ?>
                                </span>
                                <?php if ($item['wajib_ganti_password']): ?>
                                    <span class="rounded-full bg-amber-50 px-2.5 py-1 text-xs font-medium text-amber-800 ring-1 ring-inset ring-amber-200">Wajib ganti sandi</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="px-4 py-4 last:pr-5 sm:last:pr-6">
                            <div class="flex flex-wrap items-center gap-2">
                                <a class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 hover:text-slate-900" href="/pengguna/<?= e($item['id']) ?>/ubah">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-3.5 w-3.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z"/></svg>
                                    Ubah / reset sandi
                                </a>
                                <?php if ($item['id'] !== $currentUser['id']): ?>
                                    <form method="post" action="/pengguna/<?= e($item['id']) ?>/status" onsubmit="return confirm('Ubah status pengguna ini?')">
                                        <input type="hidden" name="_token" value="<?= e(Csrf::token()) ?>">
                                        <button class="inline-flex items-center rounded-lg px-2.5 py-2 text-xs font-semibold transition <?= $item['aktif'] ? 'text-red-600 hover:bg-red-50' : 'text-emerald-700 hover:bg-emerald-50' ?>"><?= e($item['aktif'] ? 'Nonaktifkan' : 'Aktifkan') ?></button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
