<?php

declare(strict_types=1);

namespace App\Library\Api;

use App\Library\Api\View\HomeView;
use App\Library\Auth\Session;
use App\Library\Framework\Metrics;
use App\Library\Framework\Routing\ControllerAbstract;
use App\Library\Framework\Routing\Response;

final class FrontendController extends ControllerAbstract
{
  public function index(): Response
  {
    Session::start();

    return $this->render(new HomeView());
  }

  public function noteCreated(): Response
  {
    Metrics::noteCreated();

    return new Response(status: 204);
  }
}
