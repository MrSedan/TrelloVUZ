<?php

declare(strict_types=1);

namespace App\Library\Auth;

use App\Library\Database\Database;
use RuntimeException;

final class Session
{
  public static function start(): void
  {
    if (session_status() === PHP_SESSION_ACTIVE) {
      return;
    }

    session_set_save_handler(new SessionHandler(
      Database::connection(),
      (int) ini_get('session.gc_maxlifetime'),
    ), true);

    if (!session_start([
      'use_strict_mode' => 1,
      'use_only_cookies' => 1,
      'use_trans_sid' => 0,
      'cookie_path' => '/',
      'cookie_httponly' => true,
      'cookie_secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
      'cookie_samesite' => 'Lax',
      'gc_probability' => 0,
    ])) {
      throw new RuntimeException('Could not start session.');
    }
  }
}
