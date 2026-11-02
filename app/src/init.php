<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Library\Framework\Routing\Response;
use App\Library\Super;

define('ROOT_DIR', __DIR__);

// Преобразуем ошибки, включённые в error_reporting, в исключения.
set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
  if (!(error_reporting() & $severity)) {
    return false;
  }

  throw new ErrorException($message, 0, $severity, $file, $line);
});

set_exception_handler(static function (Throwable $e): void {
  Super::log()->error('uncaught exception', ['exception' => $e->getMessage()]);

  if (PHP_SAPI === 'cli') {
    exit(1);
  }

  (new Response('Internal Server Error', 500, ['Content-Type' => 'text/plain; charset=utf-8']))
    ->send(($_SERVER['REQUEST_METHOD'] ?? '') === 'HEAD');
});
