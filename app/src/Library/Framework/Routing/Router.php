<?php

declare(strict_types=1);

namespace App\Library\Framework\Routing;

final class Router
{
  private array $routes = [];

  public function add(string $method, string $path, callable $handler): void
  {
    $this->routes[$path][strtoupper($method)] = $handler;
  }

  public function dispatch(RequestAbstract $request): Response
  {
    $method = $request->method;
    $routes = $this->routes[$request->path()] ?? [];

    if ($routes === []) {
      return new Response('Not Found', 404, ['Content-Type' => 'text/plain; charset=utf-8']);
    }

    $handler = $routes[$method] ?? ($method === 'HEAD' ? ($routes['GET'] ?? null) : null);

    if ($handler !== null) {
      return $handler();
    }

    $allowed = array_keys($routes);
    if (isset($routes['GET']) && !isset($routes['HEAD'])) {
      $allowed[] = 'HEAD';
    }

    return new Response('Method Not Allowed', 405, [
      'Content-Type' => 'text/plain; charset=utf-8',
      'Allow' => implode(', ', $allowed),
    ]);
  }
}
