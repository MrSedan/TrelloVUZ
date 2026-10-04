<?php

declare(strict_types=1);

namespace App;

use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;

final class Log
{
  private static ?Logger $logger = null;

  public static function logger(): Logger
  {
    if (self::$logger !== null) {
      return self::$logger;
    }

    $dir = __DIR__ . '/../var/log';
    if (!is_dir($dir)) {
      mkdir($dir, 0775, true);
    }

    $logger = new Logger('app');
    $logger->pushHandler(new StreamHandler('php://stdout', Level::Debug));
    $logger->pushHandler(new RotatingFileHandler($dir . '/app.log', 7, Level::Debug));

    self::$logger = $logger;

    return $logger;
  }
}
