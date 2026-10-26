<?php

declare(strict_types=1);

use App\Support\AgendaNumber;
?>
<div class="overflow-x-auto">
    <table class="w-full border-collapse text-left text-sm">
        <caption class="sr-only">Pratinjau buku agenda surat masuk</caption>
        <thead class="bg-slate-50/80 text-[11px] uppercase tracking-wider text-slate-500">
            <tr>
                <?php foreach (['No. Agenda', 'No. Surat', 'Tgl Surat', 'Tgl Diterima', 'Asal Surat', 'Perihal', 'Sifat', 'Lampiran', 'Keterangan'] as $heading): ?>
                    <th scope="col" class="whitespace-nowrap border-b border-slate-200 px-4 py-3 font-semibold"><?= e($heading) ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            <?php foreach ($rows as $row): ?>
                <tr class="transition hover:bg-blue-50/40">
                    <td class="whitespace-nowrap px-4 py-3 font-semibold text-blue-700"><?= e(AgendaNumber::format($row['nomor_urut'], $row['tahun_agenda'])) ?></td>
                    <td class="px-4 py-3 text-slate-700"><?= e($row['nomor_surat']) ?></td>
                    <td class="whitespace-nowrap px-4 py-3 text-slate-600"><?= e(tanggal($row['tanggal_surat'])) ?></td>
                    <td class="whitespace-nowrap px-4 py-3 text-slate-600"><?= e(tanggal($row['tanggal_diterima'])) ?></td>
                    <td class="px-4 py-3 text-slate-700"><?= e($row['asal_surat']) ?></td>
                    <td class="px-4 py-3 text-slate-700"><?= e($row['perihal']) ?></td>
                    <td class="px-4 py-3 text-slate-700"><?= e(ucfirst($row['sifat'])) ?></td>
                    <td class="px-4 py-3 text-slate-700"><?= e($row['jumlah_lampiran']) ?></td>
                    <td class="px-4 py-3 text-slate-700"><?= e($row['keterangan']) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if ($rows === []): ?>
                <tr><td colspan="9" class="px-4 py-10 text-center text-sm text-slate-500">Tidak ada surat pada periode ini.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
