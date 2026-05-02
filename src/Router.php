<?php

declare(strict_types=1);

namespace Elonn\Site;

final class Router
{
    /** @var array<string, array<string, callable(): void>> */
    private array $routes = [];

    /**
     * @param callable(): void $handler
     */
    public function get(string $path, callable $handler): void
    {
        $this->routes['GET'][$this->normalize($path)] = $handler;
    }

    /**
     * @param callable(): void $handler
     */
    public function post(string $path, callable $handler): void
    {
        $this->routes['POST'][$this->normalize($path)] = $handler;
    }

    public function dispatch(string $method, string $path): void
    {
        $handler = $this->routes[strtoupper($method)][$this->normalize($path)] ?? null;
        if ($handler === null) {
            Response::json(['error' => 'Not Found'], 404);
            return;
        }

        $handler();
    }

    private function normalize(string $path): string
    {
        $normalized = '/' . trim($path, '/');
        return $normalized === '//' ? '/' : $normalized;
    }
}
