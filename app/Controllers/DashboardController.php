<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Database;
use App\Repositories\SuratRepository;
use App\Core\Response;
use App\Core\View;

final class DashboardController
{
    public function index(Request $request, ?array $user): Response
    {
        return View::render('dashboard/index', ['title' => 'Dashboard', 'currentUser' => $user,
            'summary' => (new SuratRepository(Database::connection()))->dashboard()]);
    }
}
