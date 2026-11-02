<?php

declare(strict_types=1);

namespace App\Library\Framework;

abstract class CronTaskAbstract
{
  abstract public function run(): void;

  public function __invoke(): void
  {
    $this->run();
  }
}
