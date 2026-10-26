<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;
use App\Core\View;
use App\Repositories\UserRepository;

final class PenggunaController
{
    public function index(Request $request, ?array $user): Response
    {
        Auth::requireRole('admin');
        return View::render('pengguna/index', ['title' => 'Pengguna', 'currentUser' => $user,
            'users' => $this->repository()->all()]);
    }

    public function create(Request $request, ?array $user): Response
    {
        Auth::requireRole('admin');
        return $this->form($user, ['peran' => 'operator']);
    }

    public function edit(Request $request, ?array $user): Response
    {
        Auth::requireRole('admin');
        $target = $this->target($request);
        return $this->form($user, $target, id: $target['id']);
    }

    public function store(Request $request, ?array $user): Response
    {
        Auth::requireRole('admin');
        return $this->save($request, $user);
    }

    public function update(Request $request, ?array $user): Response
    {
        Auth::requireRole('admin');
        return $this->save($request, $user, $this->target($request)['id']);
    }

    public function resetPassword(Request $request, ?array $user): Response
    {
        Auth::requireRole('admin');
        $target = $this->target($request);
        $validator = new Validator();
        $password = $validator->string($request->post, 'password', 'Kata sandi baru', trim: false);
        $validator->password($password);
        if ($validator->errors() !== []) {
            return $this->form($user, $target, $validator->errors(), $target['id']);
        }
        $this->repository()->resetPassword($target['id'], password_hash($password, PASSWORD_DEFAULT));
        Session::flash('Kata sandi direset. Pengguna wajib menggantinya saat masuk.');
        return Response::redirect('/pengguna');
    }

    public function toggleStatus(Request $request, ?array $user): Response
    {
        Auth::requireRole('admin');
        $target = $this->target($request);
        try {
            $this->repository()->manage($target['id'], $user['id']);
        } catch (\DomainException $exception) {
            return $this->form($user, $target, ['peran' => $exception->getMessage()], $target['id']);
        }
        Session::flash('Status pengguna berhasil diubah.');
        return Response::redirect('/pengguna');
    }

    private function save(Request $request, ?array $user, ?int $id = null): Response
    {
        $validator = new Validator();
        $data = [
            'username' => $validator->string($request->post, 'username', 'Username', max: 50),
            'nama_lengkap' => $validator->string($request->post, 'nama_lengkap', 'Nama lengkap', max: 100),
            'peran' => $validator->choice($request->post, 'peran', 'Peran', ['admin', 'operator']),
        ];
        $validator->username($data['username']);
        $password = '';
        if ($id === null) {
            $password = $validator->string($request->post, 'password', 'Kata sandi awal', trim: false);
            $validator->password($password);
        }
        $existing = $this->repository()->findByUsername($data['username']);
        if ($existing !== null && $existing['id'] !== $id) {
            $validator->add('username', 'Username sudah digunakan.');
        }
        if ($validator->errors() !== []) {
            return $this->form($user, $data, $validator->errors(), $id);
        }
        try {
            if ($id === null) {
                $this->repository()->create($data, password_hash($password, PASSWORD_DEFAULT));
            } else {
                $this->repository()->manage($id, $user['id'], $data);
            }
        } catch (\DomainException $exception) {
            return $this->form($user, $data, ['peran' => $exception->getMessage()], $id);
        } catch (\PDOException $exception) {
            if (($exception->errorInfo[1] ?? null) !== 1062) {
                throw $exception;
            }
            return $this->form($user, $data, ['username' => 'Username sudah digunakan.'], $id);
        }
        Session::flash($id === null ? 'Pengguna berhasil ditambahkan.' : 'Pengguna berhasil diubah.');
        return Response::redirect('/pengguna');
    }

    private function repository(): UserRepository
    {
        return new UserRepository(Database::connection());
    }

    private function target(Request $request): array
    {
        $validator = new Validator();
        $id = $validator->integer($request->params, 'id', 'ID pengguna', 1, PHP_INT_MAX);
        $target = $id === null ? null : $this->repository()->findById($id);
        if ($target === null) {
            throw new HttpException(404);
        }
        return $target;
    }

    private function form(?array $user, array $old, array $errors = [], ?int $id = null): Response
    {
        return View::render('pengguna/form', ['title' => $id === null ? 'Tambah pengguna' : 'Ubah pengguna',
            'currentUser' => $user, 'old' => $old, 'errors' => $errors, 'id' => $id], $errors === [] ? 200 : 422);
    }
}
