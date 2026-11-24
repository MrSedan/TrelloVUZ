<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/init.php';

use App\Library\Api\Request;
use App\Library\Framework\Routing\Response;
use App\Library\Super;

// Тела ответов этих маршрутов содержат email и имя пользователя — в лог их не пишем.
const REDACTED_BODY_PATHS = ['/register', '/login', '/auth'];

$logger = Super::log();
$request = Request::fromGlobals();

$devAuthRoute = (getenv('APP_ENV') ?: 'dev') === 'dev'
  && in_array($request->path(), ['/auth', '/login', '/register', '/logout'], true);
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$corsHeaders = [];
if ($devAuthRoute && in_array($origin, ['http://localhost:5173', 'http://127.0.0.1:5173'], true)) {
  $corsHeaders = [
    'Access-Control-Allow-Origin' => $origin,
    'Access-Control-Allow-Credentials' => 'true',
  ];
}

try {
  if ($request->method === 'OPTIONS' && $corsHeaders !== []) {
    $response = new Response(status: 204, headers: [
      'Access-Control-Allow-Methods' => 'GET, POST',
      'Access-Control-Allow-Headers' => 'Content-Type',
    ]);
  } else {
    $router = require ROOT_DIR . '/Config/routes.php';
    $response = $router->dispatch($request);
  }

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
  'body' => in_array($request->path(), REDACTED_BODY_PATHS, true) ? '[redacted]' : $response->body,
]);

if ($devAuthRoute) {
  $vary = isset($response->headers['Vary']) ? $response->headers['Vary'] . ', Origin' : 'Origin';
  $headers = ['Vary' => $vary] + $corsHeaders + $response->headers;
  $response = new Response($response->body, $response->status, $headers);
}

$response->send($request->method === 'HEAD');
