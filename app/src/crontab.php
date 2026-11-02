<?php

declare(strict_types=1);

require_once __DIR__ . '/init.php';

use App\Library\Database\Database;
use App\Library\Framework\TaskRunner;
use App\Library\Super;

$tasks = require __DIR__ . '/Config/cron.php';
$name = $argv[1] ?? '';

if ($name === '--list') {
  echo implode(PHP_EOL, array_keys($tasks)) . PHP_EOL;
  exit(0);
}

if (!isset($tasks[$name])) {
  fwrite(STDERR, "Usage: php src/crontab.php <task>|--list\n");
  exit(2);
}

$runner = new TaskRunner(Database::connection(), Super::log(), $tasks);
exit($runner->run($name));
