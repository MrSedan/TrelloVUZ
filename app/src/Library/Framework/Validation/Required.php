<?php

declare(strict_types=1);

namespace App\Library\Framework\Validation;

final class Required implements ValidatorInterface
{
  public function validate(mixed $value): ?string
  {
    return $value === null || (is_string($value) && trim($value) === '') ? 'is required' : null;
  }
}
