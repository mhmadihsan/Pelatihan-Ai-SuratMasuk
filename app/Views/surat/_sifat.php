<?php

declare(strict_types=1);

$badge = match ($sifat) {
    'penting' => ['bg-amber-50 text-amber-800 ring-amber-200', 'bg-amber-500'],
    'segera' => ['bg-red-50 text-red-700 ring-red-200', 'bg-red-500'],
    'rahasia' => ['bg-purple-50 text-purple-800 ring-purple-200', 'bg-purple-500'],
    default => ['bg-slate-100 text-slate-700 ring-slate-200', 'bg-slate-400'],
};
?>
<span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset <?= e($badge[0]) ?>">
    <span class="h-1.5 w-1.5 rounded-full <?= e($badge[1]) ?>" aria-hidden="true"></span>
    <?= e(ucfirst($sifat)) ?>
</span>
