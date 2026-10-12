<?php

declare(strict_types=1);

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

final class Board extends Model
{
  protected $table = 'boards';

  protected $primaryKey = 'board_id';

  const CREATED_AT = 'created_datetime';

  const UPDATED_AT = 'updated_datetime';

  protected $fillable = ['title', 'owner_user_id', 'is_public'];
}
