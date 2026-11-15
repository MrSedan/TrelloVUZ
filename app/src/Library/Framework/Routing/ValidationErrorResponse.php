<?php

declare(strict_types=1);

namespace App\Library\Framework\Routing;

final class ValidationErrorResponse extends ResponseModel
{
  /** @param array<string, list<string>> $errors */
  public function __construct(private array $errors) {}

  public function status(): int
  {
    return 422;
  }

  public function toArray(): array
  {
    return ['status' => 'fail', 'error' => 'validation failed', 'errors' => $this->errors];
  }
}
