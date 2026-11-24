<?php

declare(strict_types=1);

use App\Library\Api\DemoController;
use App\Library\Api\FrontendController;
use App\Library\Api\HealthController;
use App\Library\Framework\Routing\Router;

$router = new Router();
$frontend = new FrontendController();
$router->add('GET', '/', [$frontend, 'index']);
$router->add('GET', '/index.php', [$frontend, 'index']);
$router->add('GET', '/login', [$frontend, 'index']);
$router->add('GET', '/register', [$frontend, 'index']);
$router->add('GET', '/auth', [$frontend, 'auth']);
$router->add('POST', '/note-created', [$frontend, 'noteCreated']);
$router->add('POST', '/register', [$frontend, 'register']);
$router->add('POST', '/login', [$frontend, 'login']);
$router->add('POST', '/logout', [$frontend, 'logout']);
$router->add('GET', '/health', [new HealthController(), 'index']);
$router->add('GET', '/health.php', [new HealthController(), 'index']);
$router->add('GET', '/demo/error.php', [new DemoController(), 'error']);
$router->add('GET', '/demo/slow.php', [new DemoController(), 'slow']);
$router->add('GET', '/demo/cpu.php', [new DemoController(), 'cpu']);

return $router;
