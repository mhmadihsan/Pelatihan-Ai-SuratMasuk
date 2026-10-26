<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;
use App\Core\View;
use App\Repositories\UserRepository;
use App\Support\Logger;

final class AuthController
{
    public function form(Request $request, ?array $user): Response
    {
        return View::render('auth/login', ['title' => 'Masuk']);
    }

    public function login(Request $request, ?array $user): Response
    {
        $validator = new Validator();
        $username = $validator->string($request->post, 'username', 'Username');
        $password = $validator->string($request->post, 'password', 'Kata sandi', false);
        $validator->username($username);
        if ($validator->errors() === []) {
            $user = Auth::attempt($username, $password);
            if ($user !== null) {
                return Response::redirect($user['wajib_ganti_password'] ? '/ganti-password' : '/');
            }
            $validator->add('password', 'Username atau kata sandi salah, atau akun tidak aktif.');
        }
        Logger::write('WARNING', 'Login gagal');
        return View::render('auth/login', [
            'title' => 'Masuk', 'errors' => $validator->errors(), 'old' => ['username' => $username],
        ]);
    }

    public function logout(Request $request, ?array $user): Response
    {
        Session::destroy();
        return Response::redirect('/login');
    }

    public function formGanti(Request $request, ?array $user): Response
    {
        return View::render('auth/ganti-password', ['title' => 'Ganti kata sandi', 'currentUser' => $user]);
    }

    public function ganti(Request $request, ?array $user): Response
    {
        $validator = new Validator();
        $current = $validator->string($request->post, 'password_lama', 'Kata sandi saat ini', false);
        $password = $validator->string($request->post, 'password_baru', 'Kata sandi baru', false);
        $confirmation = $validator->string($request->post, 'konfirmasi_password', 'Konfirmasi kata sandi', false);
        $validator->password($password, 'password_baru');
        if ($password !== $confirmation) {
            $validator->add('konfirmasi_password', 'Konfirmasi kata sandi tidak cocok.');
        }
        if (!password_verify($current, $user['password_hash'])) {
            $validator->add('password_lama', 'Kata sandi saat ini salah.');
        }
        if (password_verify($password, $user['password_hash'])) {
            $validator->add('password_baru', 'Gunakan kata sandi yang berbeda dari kata sandi saat ini.');
        }
        if ($validator->errors() !== []) {
            return View::render('auth/ganti-password', [
                'title' => 'Ganti kata sandi', 'currentUser' => $user, 'errors' => $validator->errors(),
            ]);
        }
        (new UserRepository(Database::connection()))->changePassword(
            $user['id'], password_hash($password, PASSWORD_DEFAULT)
        );
        session_regenerate_id(true);
        Csrf::rotate();
        Session::flash('Kata sandi berhasil diganti. Selamat datang di SIMasuk.');
        return Response::redirect('/');
    }
}
