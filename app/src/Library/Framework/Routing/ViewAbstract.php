<?php

declare(strict_types=1);

namespace App\Library\Framework\Routing;

abstract class ViewAbstract
{
  abstract protected function template(): string;

  public function render(array $data = []): Response
  {
    ob_start();

    try {
      extract($data, EXTR_SKIP);
      require $this->template();

      return new Response(ob_get_contents());
    } finally {
      ob_end_clean();
    }
  }
}
