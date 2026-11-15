<?php

declare(strict_types=1);

namespace App\Library\Api\Response;

use App\Library\Database\User;
use App\Library\Framework\Routing\ResponseModel;

final class UserResponse extends ResponseModel
{
  public function __construct(private User $user, private int $status = 200) {}

  public function status(): int
  {
    return $this->status;
  }

  public function toArray(): array
  {
    return [
      'status' => 'ok',
      'user' => [
        'id' => $this->user->user_id,
        'email' => $this->user->email,
        'name' => $this->user->name,
      ],
    ];
  }
}
