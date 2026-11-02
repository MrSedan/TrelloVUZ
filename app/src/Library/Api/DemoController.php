<?php

declare(strict_types=1);

namespace App\Library\Api;

use App\Library\Database\Database;
use App\Library\Framework\Routing\ControllerAbstract;
use App\Library\Framework\Routing\Response;
use App\Library\Super;
use Throwable;

final class DemoController extends ControllerAbstract
{
  private const MAX_SECONDS = 10.0;
  private const MAX_MS = 2000;

  public function error(): Response
  {
    $rate = (float) ($_GET['rate'] ?? 1);
    $rate = max(0.0, min($rate, 1.0));

    if (mt_rand() / mt_getrandmax() < $rate) {
      Super::log()->error('simulated failure', ['status' => 500]);

      return $this->json(['status' => 'fail', 'error' => 'simulated failure'], 500);
    }

    return $this->json(['status' => 'ok']);
  }

  public function slow(): Response
  {
    $seconds = (float) ($_GET['seconds'] ?? 1);
    $seconds = max(0.0, min($seconds, self::MAX_SECONDS));

    try {
      Database::connection()->select('SELECT pg_sleep(:seconds)', ['seconds' => $seconds]);
    } catch (Throwable $e) {
      Super::log()->error('slow query failed', ['error' => $e->getMessage()]);

      return $this->json(['status' => 'fail', 'error' => 'database unavailable'], 503);
    }

    Super::log()->info('slow query finished', ['seconds' => $seconds]);

    return $this->json(['status' => 'ok', 'slept_seconds' => $seconds]);
  }

  public function cpu(): Response
  {
    $ms = (int) ($_GET['ms'] ?? 200);
    $ms = max(0, min($ms, self::MAX_MS));

    $deadline = hrtime(true) + $ms * 1_000_000;
    $iterations = 0;
    $hash = '';

    while (hrtime(true) < $deadline) {
      $hash = hash('sha256', $hash . $iterations);
      $iterations++;
    }

    return $this->json(['status' => 'ok', 'burned_ms' => $ms, 'iterations' => $iterations]);
  }
}
