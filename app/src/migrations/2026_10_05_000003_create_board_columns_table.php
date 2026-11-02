<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('board_columns', function (Blueprint $table) {
      $table->bigIncrements('column_id');
      $table->foreignId('board_id')->constrained('boards', 'board_id');
      $table->smallInteger('ordinal_position');
      $table->string('title', 255);
      $table->string('color', 6)->nullable();

      $table->index('board_id', 'idx_board_id');
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('board_columns');
  }
};
