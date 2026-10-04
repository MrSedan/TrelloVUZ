<?php

declare(strict_types=1);

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

final class BoardColumn extends Model
{
  protected $table = 'columns';

  public $timestamps = false;

  protected $fillable = ['title'];
}
