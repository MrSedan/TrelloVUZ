<?php

declare(strict_types=1);

namespace App\Library\Database;

use Illuminate\Database\Eloquent\Model;

final class BoardColumn extends Model
{
  protected $table = 'board_columns';

  protected $primaryKey = 'column_id';

  public $timestamps = false;

  protected $fillable = ['board_id', 'ordinal_position', 'title', 'color'];
}
