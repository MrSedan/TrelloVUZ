<?php

declare(strict_types=1);

namespace App\Library\Framework\Routing;

final class ErrorResponse extends ResponseModel
{
  public function __construct(private string $error, private int $status = 400) {}

  public function status(): int
  {
    return $this->status;
  }

  public function toArray(): array
  {
    return ['status' => 'fail', 'error' => $this->error];
  }
}
