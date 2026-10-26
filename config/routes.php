<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\SuratController;
use App\Controllers\LaporanController;
use App\Controllers\PenggunaController;

return [
    'GET /login' => [AuthController::class, 'form'],
    'POST /login' => [AuthController::class, 'login'],
    'POST /logout' => [AuthController::class, 'logout'],
    'GET /ganti-password' => [AuthController::class, 'formGanti'],
    'POST /ganti-password' => [AuthController::class, 'ganti'],
    'GET /' => [DashboardController::class, 'index'],
    'GET /pengguna' => [PenggunaController::class, 'index'],
    'GET /pengguna/tambah' => [PenggunaController::class, 'create'],
    'POST /pengguna' => [PenggunaController::class, 'store'],
    'GET /pengguna/{id}/ubah' => [PenggunaController::class, 'edit'],
    'POST /pengguna/{id}' => [PenggunaController::class, 'update'],
    'POST /pengguna/{id}/reset-password' => [PenggunaController::class, 'resetPassword'],
    'POST /pengguna/{id}/status' => [PenggunaController::class, 'toggleStatus'],
    'GET /laporan' => [LaporanController::class, 'index'],
    'GET /laporan/cetak' => [LaporanController::class, 'cetak'],
    'GET /laporan/ekspor' => [LaporanController::class, 'ekspor'],
    'GET /surat' => [SuratController::class, 'index'],
    'GET /surat/tambah' => [SuratController::class, 'create'],
    'POST /surat' => [SuratController::class, 'store'],
    'GET /surat/{id}' => [SuratController::class, 'show'],
    'GET /surat/{id}/ubah' => [SuratController::class, 'edit'],
    'POST /surat/{id}' => [SuratController::class, 'update'],
    'POST /surat/{id}/hapus' => [SuratController::class, 'destroy'],
];
