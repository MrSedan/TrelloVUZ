<?php

declare(strict_types=1);

namespace App\Library\Api\Action;

use App\Library\Api\Response\UserResponse;
use App\Library\Auth\Auth;
use App\Library\Framework\Routing\ActionAbstract;
use App\Library\Framework\Routing\ErrorResponse;
use App\Library\Framework\Routing\ResponseModel;
use App\Library\Framework\Validation\MaxLength;
use App\Library\Framework\Validation\Required;

final class LoginAction extends ActionAbstract
{
  protected function rules(): array
  {
    return [
      'email' => [new Required(), new MaxLength(255)],
      'password' => [new Required(), new MaxLength(255)],
    ];
  }

  protected function handle(array $input): ResponseModel
  {
    $user = Auth::login((string) $input['email'], (string) $input['password']);

    return $user === null ? new ErrorResponse('invalid email or password', 401) : new UserResponse($user);
  }
}
