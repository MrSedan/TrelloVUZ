<?php

declare(strict_types=1);

namespace App\Library\Tasks;

use App\Library\Auth\SessionHandler;
use App\Library\Database\Database;
use App\Library\Framework\CronTaskAbstract;
use App\Library\Super;

final class RemoveOldSessionsCronTask extends CronTaskAbstract
{
  public function run(): void
  {
    $start = hrtime(true);
    $handler = new SessionHandler(Database::connection());
    $deleted = $handler->gc((int) ini_get('session.gc_maxlifetime'));

    Super::log()->info('session cleanup completed', [
      'task' => 'sessions:gc',
      'deleted_count' => $deleted,
      'duration_seconds' => (hrtime(true) - $start) / 1e9,
    ]);
  }
}
