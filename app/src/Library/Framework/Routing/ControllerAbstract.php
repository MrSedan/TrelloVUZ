<?php

declare(strict_types=1);

namespace App\Library\Framework\Routing;

abstract class ControllerAbstract
{
  protected function json(array $data, int $status = 200): Response
  {
    return new Response(
      json_encode($data, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
      $status,
      ['Content-Type' => 'application/json; charset=utf-8'],
    );
  }

  protected function run(ActionAbstract $action): Response
  {
    return $action->execute($this->input())->toResponse();
  }

  /** Тело запроса: JSON, если пришёл JSON-объект, иначе $_POST. */
  private function input(): array
  {
    $json = json_decode((string) file_get_contents('php://input'), true);

    return is_array($json) ? $json : $_POST;
  }
}
