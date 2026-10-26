<?php

declare(strict_types=1);

namespace App\Core;

use App\Repositories\UserRepository;

final class Auth
{
    public static function user(): ?array
    {
        if (!isset($_SESSION['user_id'])) {
            return null;
        }
        $user = (new UserRepository(Database::connection()))->findById((int) $_SESSION['user_id']);
        if ($user === null || !$user['aktif']) {
            Session::destroy();
            return null;
        }
        return $user;
    }

    public static function attempt(string $username, string $password): ?array
    {
        $user = (new UserRepository(Database::connection()))->findByUsername($username);
        $hash = $user['password_hash'] ?? '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.';
        if (!password_verify($password, $hash) || $user === null || !$user['aktif']) {
            return null;
        }
        self::establish($user['id']);
        return $user;
    }

    public static function establish(int $userId): void
    {
        session_regenerate_id(true);
        $_SESSION = ['user_id' => $userId, 'last_activity' => time()];
        Csrf::rotate();
    }

    public static function requireRole(string $role): void
    {
        if ((self::user()['peran'] ?? null) !== $role) {
            throw new HttpException(403);
        }
    }
}
