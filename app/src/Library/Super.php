<?php

declare(strict_types=1);

namespace App\Library;

use App\Library\Config\Config;
use App\Library\Framework\LogHelper;
use Monolog\Logger;

final class Super
{
  private static ?Config $config = null;

  public static function config(): Config
  {
    return self::$config ??= new Config();
  }

  public static function log(): Logger
  {
    return LogHelper::logger();
  }
}
