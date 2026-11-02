<?php

declare(strict_types=1);

namespace App\Library\Framework;

use Illuminate\Database\ConnectionInterface;
use Psr\Log\LoggerInterface;
use Throwable;

final class TaskRunner
{
  public function __construct(
    private ConnectionInterface $connection,
    private LoggerInterface $logger,
    private array $tasks,
  ) {}

  public function run(string $name): int
  {
    if (!isset($this->tasks[$name])) {
      $this->logger->error('unknown cron task', ['task' => $name]);
      return 1;
    }

    $key = 'cron:' . $name;

    try {
      $lock = $this->connection->selectOne('SELECT pg_try_advisory_lock(hashtextextended(?, 0)) AS locked', [$key]);

      if (!$lock->locked) {
        $this->logger->info('cron task already running', ['task' => $name]);
        return 0;
      }

      try {
        ($this->tasks[$name])();
      } finally {
        $this->connection->select('SELECT pg_advisory_unlock(hashtextextended(?, 0))', [$key]);
      }

      $this->logger->info('cron task completed', ['task' => $name]);
      return 0;
    } catch (Throwable $e) {
      $this->logger->error('cron task failed', ['task' => $name, 'exception' => $e->getMessage()]);
      return 1;
    }
  }
}
