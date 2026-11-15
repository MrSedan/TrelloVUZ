<?php

declare(strict_types=1);

namespace App\Library\Api\Action;

use App\Library\Api\Response\UserResponse;
use App\Library\Auth\Auth;
use App\Library\Framework\Routing\ActionAbstract;
use App\Library\Framework\Routing\ErrorResponse;
use App\Library\Framework\Routing\ResponseModel;
use App\Library\Framework\Validation\Email;
use App\Library\Framework\Validation\MaxLength;
use App\Library\Framework\Validation\MinLength;
use App\Library\Framework\Validation\Required;

final class RegisterAction extends ActionAbstract
{
  protected function rules(): array
  {
    return [
      'email' => [new Required(), new Email(), new MaxLength(255)],
      'password' => [new Required(), new MinLength(8), new MaxLength(255)],
      'name' => [new Required(), new MaxLength(255)],
    ];
  }

  protected function handle(array $input): ResponseModel
  {
    $user = Auth::register($input['email'], $input['password'], trim($input['name']));

    return $user === null ? new ErrorResponse('email already registered', 409) : new UserResponse($user, 201);
  }
}
