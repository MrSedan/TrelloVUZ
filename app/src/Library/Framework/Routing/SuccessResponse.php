<?php

declare(strict_types=1);

namespace App\Library\Framework\Routing;

final class SuccessResponse extends ResponseModel
{
  public function __construct(private array $data = [], private int $status = 200) {}

  public function status(): int
  {
    return $this->status;
  }

  public function toArray(): array
  {
    return ['status' => 'ok'] + $this->data;
  }
}
