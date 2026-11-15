<?php

declare(strict_types=1);

namespace App\Library\Framework\Validation;

final class Email implements ValidatorInterface
{
  public function validate(mixed $value): ?string
  {
    return is_string($value) && filter_var($value, FILTER_VALIDATE_EMAIL) !== false ? null : 'must be a valid email';
  }
}
