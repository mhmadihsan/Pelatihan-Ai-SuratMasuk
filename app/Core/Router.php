<?php

declare(strict_types=1);

namespace App\Core;

final class Router
{
    public function __construct(private readonly array $routes)
    {
    }

    public function dispatch(Request $request): Response
    {
        if ($request->method === 'POST' && !Csrf::verify($request->post['_token'] ?? null)) {
            return View::error(419);
        }

        $user = Auth::user();
        if ($request->path !== '/login' && $user === null) {
            return Response::redirect('/login');
        }
        if ($user !== null) {
            if ($user['wajib_ganti_password'] && !in_array($request->path, ['/ganti-password', '/logout'], true)) {
                return Response::redirect('/ganti-password');
            }
            if ($request->path === '/login') {
                return Response::redirect('/');
            }
        }

        $handler = $this->routes[$request->method . ' ' . $request->path] ?? null;
        if ($handler === null) {
            foreach ($this->routes as $route => $candidate) {
                $pattern = str_replace('\\{id\\}', '(?<id>[0-9]+)', preg_quote($route, '~'));
                if (preg_match('~^' . $pattern . '$~D', $request->method . ' ' . $request->path, $matches)) {
                    $handler = $candidate;
                    $request = new Request($request->method, $request->path, $request->post, $request->query,
                        ['id' => $matches['id']]);
                    break;
                }
            }
        }
        if ($handler === null) {
            return View::error(404);
        }
        [$controller, $method] = $handler;
        return (new $controller())->$method($request, $user);
    }
}
