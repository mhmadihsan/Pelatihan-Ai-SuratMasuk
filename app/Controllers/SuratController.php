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
use App\Repositories\SuratRepository;
use App\Support\AgendaNumber;

final class SuratController
{
    public function index(Request $request, ?array $user): Response
    {
        $validator = new Validator();
        $filters = [];
        $filters['q'] = $validator->string($request->query, 'q', 'Pencarian', required: false);
        $filters['sifat'] = $validator->choice($request->query + ['sifat' => ''], 'sifat', 'Sifat',
            ['', 'biasa', 'penting', 'segera', 'rahasia'], required: false);
        foreach (['dari' => 'Dari tanggal', 'sampai' => 'Sampai tanggal'] as $field => $label) {
            $filters[$field] = $validator->string($request->query, $field, $label, required: false);
            if ($filters[$field] !== '') {
                $validator->date([$field => $filters[$field]], $field, $label);
            }
        }
        $errors = $validator->errors();
        if (!isset($errors['dari']) && !isset($errors['sampai']) && $filters['dari'] !== ''
            && $filters['sampai'] !== '' && $filters['dari'] > $filters['sampai']) {
            $validator->add('sampai', 'Tanggal akhir tidak boleh lebih awal dari tanggal awal.');
        }
        $page = $validator->integer($request->query + ['page' => '1'], 'page', 'Halaman', 1, PHP_INT_MAX);
        $pagination = ['items' => [], 'total' => 0, 'page' => 1, 'pages' => 1];
        if ($validator->errors() === []) {
            $pagination = (new SuratRepository(Database::connection()))->paginate($filters, $page, config('PER_PAGE'));
        }
        return View::render('surat/index', [
            'title' => 'Surat masuk', 'currentUser' => $user,
            'suratList' => $pagination['items'], 'pagination' => $pagination,
            'filters' => $filters, 'errors' => $validator->errors(),
        ]);
    }

    public function create(Request $request, ?array $user): Response
    {
        return $this->form($user, ['tanggal_diterima' => date('Y-m-d'), 'sifat' => 'biasa', 'jumlah_lampiran' => '0']);
    }

    public function store(Request $request, ?array $user): Response
    {
        return $this->save($request, $user);
    }

    public function edit(Request $request, ?array $user): Response
    {
        $surat = $this->findSurat($request);
        return $this->form($user, $surat, id: $surat['id']);
    }

    public function update(Request $request, ?array $user): Response
    {
        return $this->save($request, $user, $this->findSurat($request)['id']);
    }

    public function destroy(Request $request, ?array $user): Response
    {
        Auth::requireRole('admin');
        $surat = $this->findSurat($request);
        if (!(new SuratRepository(Database::connection()))->softDelete($surat['id'], $user['id'])) {
            throw new HttpException(404);
        }
        Session::flash('Surat masuk berhasil dihapus.');
        return Response::redirect('/surat');
    }

    private function save(Request $request, ?array $user, ?int $id = null): Response
    {
        $validator = new Validator();
        $data = [];
        foreach (['nomor_surat' => ['Nomor surat', 100], 'asal_surat' => ['Asal surat', 150],
            'perihal' => ['Perihal', 255]] as $field => [$label, $max]) {
            $data[$field] = $validator->string($request->post, $field, $label, max: $max);
        }
        $data['tanggal_surat'] = $validator->date($request->post, 'tanggal_surat', 'Tanggal surat');
        $data['tanggal_diterima'] = $validator->date($request->post, 'tanggal_diterima', 'Tanggal diterima');
        $data['sifat'] = $validator->choice($request->post + ['sifat' => 'biasa'], 'sifat', 'Sifat',
            ['biasa', 'penting', 'segera', 'rahasia']);
        $data['jumlah_lampiran'] = $validator->integer($request->post + ['jumlah_lampiran' => '0'],
            'jumlah_lampiran', 'Jumlah lampiran', 0, 65535);
        $data['keterangan'] = $validator->string($request->post, 'keterangan', 'Keterangan', required: false);
        if (strlen($data['keterangan']) > 65535) {
            $validator->add('keterangan', 'Keterangan maksimal 65535 byte.');
        }
        $confirmation = $validator->choice($request->post + ['konfirmasi_duplikat' => '0'],
            'konfirmasi_duplikat', 'Konfirmasi duplikat', ['0', '1']);
        $errors = $validator->errors();
        if (!isset($errors['tanggal_surat']) && !isset($errors['tanggal_diterima'])) {
            if ($data['tanggal_diterima'] < $data['tanggal_surat']) {
                $validator->add('tanggal_diterima', 'Tanggal diterima tidak boleh lebih awal dari tanggal surat.');
            }
            if ($data['tanggal_diterima'] > date('Y-m-d')) {
                $validator->add('tanggal_diterima', 'Tanggal diterima tidak boleh di masa depan.');
            }
        }
        $old = $data;
        $old['jumlah_lampiran'] = is_string($request->post['jumlah_lampiran'] ?? null)
            ? $request->post['jumlah_lampiran'] : '0';
        if ($validator->errors() !== []) {
            return $this->form($user, $old, $validator->errors(), id: $id);
        }
        $repository = new SuratRepository(Database::connection());
        if ($confirmation !== '1' && $repository->hasDuplicate($data['nomor_surat'], $data['asal_surat'], $id ?? 0)) {
            return $this->form($user, $old, [], true, $id);
        }
        if ($id !== null) {
            if (!$repository->update($id, $data)) {
                throw new HttpException(404);
            }
            Session::flash('Surat masuk berhasil diubah.');
            return Response::redirect('/surat/' . $id);
        }
        $id = $repository->create($data, $user['id']);
        $surat = $repository->find($id);
        Session::flash('Surat masuk berhasil dicatat dengan nomor agenda '
            . AgendaNumber::format($surat['nomor_urut'], $surat['tahun_agenda']) . '.');
        return Response::redirect('/surat/' . $id);
    }

    public function show(Request $request, ?array $user): Response
    {
        $surat = $this->findSurat($request);
        return View::render('surat/show', ['title' => 'Detail surat masuk', 'currentUser' => $user, 'surat' => $surat]);
    }

    private function findSurat(Request $request): array
    {
        $validator = new Validator();
        $id = $validator->integer($request->params, 'id', 'ID surat', 1, PHP_INT_MAX);
        if ($id === null) {
            throw new HttpException(404);
        }
        $surat = (new SuratRepository(Database::connection()))->find($id);
        if ($surat === null) {
            throw new HttpException(404);
        }
        return $surat;
    }

    private function form(?array $user, array $old, array $errors = [], bool $duplicate = false, ?int $id = null): Response
    {
        return View::render('surat/create', [
            'title' => $id === null ? 'Tambah surat masuk' : 'Ubah surat masuk', 'currentUser' => $user,
            'old' => $old, 'errors' => $errors, 'duplicate' => $duplicate, 'id' => $id,
        ]);
    }
}
