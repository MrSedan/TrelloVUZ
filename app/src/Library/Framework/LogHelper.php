<?php

declare(strict_types=1);

namespace App\Library\Framework;

use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Monolog\LogRecord;

final class LogHelper
{
  private static ?Logger $logger = null;

  public static function logger(): Logger
  {
    if (self::$logger !== null) {
      return self::$logger;
    }

    $dir = __DIR__ . '/../../../var/log';
    if (!is_dir($dir)) {
      mkdir($dir, 0775, true);
    }

    $logger = new Logger('app');
    $stream = new StreamHandler('php://stdout', Level::Debug);
    $stream->setFormatter(new class extends JsonFormatter {
      protected function normalizeRecord(LogRecord $record): array
      {
        $normalized = parent::normalizeRecord($record);

        // log_level не конфликтует с числовым полем level в GELF.
        return [
          'time' => $record->datetime->format(DATE_ATOM),
          'log_level' => strtolower($record->level->name),
          'message' => $normalized['message'],
          'request_id' => (string) ($_SERVER['HTTP_X_REQUEST_ID'] ?? ''),
          'route' => (string) ($_SERVER['SCRIPT_NAME'] ?? ''),
        ] + (array) $normalized['context'];
      }
    });
    $logger->pushHandler($stream);
    $logger->pushHandler(new RotatingFileHandler($dir . '/app.log', 7, Level::Debug));

    self::$logger = $logger;

    return $logger;
  }
}
