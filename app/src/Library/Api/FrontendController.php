<?php

declare(strict_types=1);

namespace App\Library\Api;

use App\Library\Api\Action\LoginAction;
use App\Library\Api\Action\LogoutAction;
use App\Library\Api\Action\RegisterAction;
use App\Library\Api\Response\UserResponse;
use App\Library\Auth\Auth;
use App\Library\Framework\Metrics;
use App\Library\Framework\Routing\ControllerAbstract;
use App\Library\Framework\Routing\ErrorResponse;
use App\Library\Framework\Routing\Response;

final class FrontendController extends ControllerAbstract
{
  public function index(): Response
  {
    $index = ROOT_DIR . '/../public/dist/index.html';
    if (!is_file($index)) {
      return new Response('Frontend is not built', 503, ['Content-Type' => 'text/plain; charset=utf-8']);
    }

    return new Response(file_get_contents($index));
  }

  public function auth(): Response
  {
    if (($_SERVER['HTTP_ACCEPT'] ?? '') === 'application/json') {
      $user = Auth::user();
      $response = ($user === null ? new ErrorResponse('User not found', 401) : new UserResponse($user))
        ->toResponse();
    } else {
      $response = $this->index();
    }

    return new Response($response->body, $response->status, $response->headers + ['Vary' => 'Accept']);
  }

  public function noteCreated(): Response
  {
    Metrics::noteCreated();

    return new Response(status: 204);
  }

  public function register(): Response
  {
    return $this->run(new RegisterAction());
  }

  public function login(): Response
  {
    return $this->run(new LoginAction());
  }

  public function logout(): Response
  {
    return $this->run(new LogoutAction());
  }
}
