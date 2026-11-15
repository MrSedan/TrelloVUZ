<?php

declare(strict_types=1);

namespace App\Library\Framework\Routing;

use App\Library\Framework\Validation\ValidatorInterface;

abstract class ActionAbstract
{
  /** @return array<string, list<ValidatorInterface>> поле => валидаторы */
  abstract protected function rules(): array;

  /** Полезная нагрузка. Вызывается только после успешной валидации. */
  abstract protected function handle(array $input): ResponseModel;

  public function execute(array $input): ResponseModel
  {
    $errors = $this->validate($input);

    return $errors === [] ? $this->handle($input) : new ValidationErrorResponse($errors);
  }

  /** @return array<string, list<string>> */
  private function validate(array $input): array
  {
    $errors = [];

    foreach ($this->rules() as $field => $validators) {
      foreach ($validators as $validator) {
        $error = $validator->validate($input[$field] ?? null);

        if ($error !== null) {
          $errors[$field][] = $error;
          break;
        }
      }
    }

    return $errors;
  }
}
