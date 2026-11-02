<?php

declare(strict_types=1);

namespace App\Library\Api\View;

use App\Library\Framework\Routing\ViewAbstract;

final class HomeView extends ViewAbstract
{
  protected function template(): string
  {
    return __DIR__ . '/home.php';
  }
}
