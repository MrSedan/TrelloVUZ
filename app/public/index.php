<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/init.php';

use App\Library\Api\Request;
use App\Library\Framework\Routing\Response;
use App\Library\Super;

$logger = Super::log();
$request = Request::fromGlobals();

try {
  $router = require ROOT_DIR . '/Config/routes.php';
  $response = $router->dispatch($request);

  if (session_status() === PHP_SESSION_ACTIVE) {
    session_write_close();
  }
} catch (Throwable $e) {
  $logger->error('request failed', ['exception' => $e->getMessage()]);
  $response = new Response('Internal Server Error', 500, ['Content-Type' => 'text/plain; charset=utf-8']);
}

// Логируем каждый входящий запрос: метод, URI, статус и тело ответа
$logger->info('request', [
  'method' => $_SERVER['REQUEST_METHOD'] ?? '',
  'uri' => $_SERVER['REQUEST_URI'] ?? '',
  'status' => $response->status,
  'body' => $response->body,
]);

$response->send($request->method === 'HEAD');
