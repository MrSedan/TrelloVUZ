<?php

declare(strict_types=1);

namespace App\Library\Api\Action;

use App\Library\Auth\Auth;
use App\Library\Framework\Metrics;
use App\Library\Framework\Routing\ActionAbstract;
use App\Library\Framework\Routing\ResponseModel;
use App\Library\Framework\Routing\SuccessResponse;

final class LogoutAction extends ActionAbstract
{
  protected function rules(): array
  {
    return [];
  }

  protected function handle(array $input): ResponseModel
  {
    Auth::logout();
    Metrics::logout();

    return new SuccessResponse();
  }
}
