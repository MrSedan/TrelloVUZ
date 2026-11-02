<?php

declare(strict_types=1);

namespace App\Library\Framework\Routing;

abstract class RequestAbstract
{
  public readonly string $method;

  public function __construct(string $method, public readonly string $uri)
  {
    $this->method = strtoupper($method);
  }

  abstract public static function fromGlobals(): static;

  public function path(): string
  {
    return explode('?', $this->uri, 2)[0] ?: '/';
  }
}
