<?php

declare(strict_types=1);

namespace App\Library\Framework\Validation;

final class MaxLength implements ValidatorInterface
{
  public function __construct(private int $max) {}

  public function validate(mixed $value): ?string
  {
    return is_string($value) && mb_strlen($value) <= $this->max ? null : "must be at most {$this->max} characters";
  }
}
