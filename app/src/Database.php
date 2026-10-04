<?php

declare(strict_types=1);

namespace App;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\ConnectionInterface;

final class Database
{
  private static ?Capsule $capsule = null;

  public static function boot(): Capsule
  {
    if (self::$capsule !== null) {
      return self::$capsule;
    }

    $capsule = new Capsule();
    $capsule->addConnection([
      'driver' => 'pgsql',
      'host' => getenv('DB_HOST') ?: 'postgres',
      'port' => getenv('DB_PORT') ?: '5432',
      'database' => getenv('DB_NAME') ?: 'app',
      'username' => getenv('DB_USER') ?: 'app',
      'password' => getenv('DB_PASSWORD') ?: '',
      'charset' => 'utf8',
      'prefix' => '',
      'schema' => 'public',
      // Не ждём дольше 3 секунд, если БД недоступна
      'options' => [
        \PDO::ATTR_TIMEOUT => 3,
      ],
    ]);
    $capsule->setAsGlobal();
    $capsule->bootEloquent();

    self::$capsule = $capsule;

    return $capsule;
  }

  public static function connection(): ConnectionInterface
  {
    return self::boot()->getConnection();
  }
}
