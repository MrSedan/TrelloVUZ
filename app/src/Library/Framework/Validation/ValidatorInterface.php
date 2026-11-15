<?php

declare(strict_types=1);

namespace App\Library\Framework\Validation;

interface ValidatorInterface
{
  /** Возвращает текст ошибки или null, если значение корректно. */
  public function validate(mixed $value): ?string;
}
