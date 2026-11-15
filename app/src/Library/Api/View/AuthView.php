<?php

declare(strict_types=1);

namespace App\Library\Api\View;

use App\Library\Framework\Routing\ViewAbstract;

final class AuthView extends ViewAbstract
{
  protected function template(): string
  {
    return __DIR__ . '/auth.php';
  }
}
