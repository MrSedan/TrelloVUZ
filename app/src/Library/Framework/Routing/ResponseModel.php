<?php

declare(strict_types=1);

namespace App\Library\Framework\Routing;

abstract class ResponseModel
{
  abstract public function status(): int;

  abstract public function toArray(): array;

  public function toResponse(): Response
  {
    return new Response(
      json_encode($this->toArray(), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
      $this->status(),
      ['Content-Type' => 'application/json; charset=utf-8'],
    );
  }
}
