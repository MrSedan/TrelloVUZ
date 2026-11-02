<?php

declare(strict_types=1);

namespace App\Library\Framework\Routing;

abstract class ControllerAbstract
{
  protected function render(ViewAbstract $view, array $data = []): Response
  {
    return $view->render($data);
  }

  protected function json(array $data, int $status = 200): Response
  {
    return new Response(
      json_encode($data, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
      $status,
      ['Content-Type' => 'application/json; charset=utf-8'],
    );
  }
}
