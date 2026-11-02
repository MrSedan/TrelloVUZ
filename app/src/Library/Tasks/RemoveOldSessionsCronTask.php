<?php

declare(strict_types=1);

namespace App\Library\Tasks;

use App\Library\Auth\SessionHandler;
use App\Library\Database\Database;
use App\Library\Framework\CronTaskAbstract;

final class RemoveOldSessionsCronTask extends CronTaskAbstract
{
  public function run(): void
  {
    $handler = new SessionHandler(Database::connection());
    $handler->gc((int) ini_get('session.gc_maxlifetime'));
  }
}
