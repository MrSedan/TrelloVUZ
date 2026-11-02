<?php

declare(strict_types=1);

namespace App\Library\Api;

use App\Library\Database\Database;
use App\Library\Framework\Routing\ControllerAbstract;
use App\Library\Framework\Routing\Response;
use Throwable;

final class HealthController extends ControllerAbstract
{
  public function index(): Response
  {
    $checks = ['php' => 'ok', 'database' => 'ok'];
    $status = 200;

    try {
      Database::connection()->select('SELECT 1');
    } catch (Throwable $e) {
      $checks['database'] = 'fail';
      $status = 503;
    }

    return $this->json([
      'status' => $status === 200 ? 'ok' : 'fail',
      'checks' => $checks,
    ], $status);
  }
}
