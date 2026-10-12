<?php

declare(strict_types=1);

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

final class Card extends Model
{
  protected $table = 'cards';

  protected $primaryKey = 'card_id';

  const CREATED_AT = 'created_datetime';

  const UPDATED_AT = 'updated_datetime';

  protected $fillable = ['board_id', 'column_id', 'sort_index', 'title', 'content', 'color', 'is_archived'];
}
