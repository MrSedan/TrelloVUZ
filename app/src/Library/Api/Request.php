<?php

declare(strict_types=1);

namespace App\Library\Api;

use App\Library\Framework\Routing\RequestAbstract;

final class Request extends RequestAbstract
{
  public static function fromGlobals(): static
  {
    return new static($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/');
  }
}
