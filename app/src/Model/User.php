<?php

declare(strict_types=1);

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

final class User extends Model
{
  protected $table = 'users';

  protected $primaryKey = 'user_id';

  const CREATED_AT = 'created_datetime';

  const UPDATED_AT = 'updated_datetime';

  protected $fillable = ['email', 'password_hash', 'name'];

  protected $hidden = ['password_hash'];
}
