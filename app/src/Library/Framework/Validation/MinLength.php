<?php

declare(strict_types=1);

namespace App\Library\Framework\Validation;

final class MinLength implements ValidatorInterface
{
  public function __construct(private int $min) {}

  public function validate(mixed $value): ?string
  {
    return is_string($value) && mb_strlen($value) >= $this->min ? null : "must be at least {$this->min} characters";
  }
}
