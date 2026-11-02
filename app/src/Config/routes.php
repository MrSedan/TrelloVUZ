<?php

declare(strict_types=1);

use App\Library\Api\FrontendController;
use App\Library\Api\HealthController;
use App\Library\Framework\Routing\Router;

$router = new Router();
$router->add('GET', '/', [new FrontendController(), 'index']);
$router->add('GET', '/index.php', [new FrontendController(), 'index']);
$router->add('GET', '/health', [new HealthController(), 'index']);
$router->add('GET', '/health.php', [new HealthController(), 'index']);

return $router;
