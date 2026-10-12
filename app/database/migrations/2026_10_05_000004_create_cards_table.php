<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('cards', function (Blueprint $table) {
      $table->bigIncrements('card_id');
      $table->foreignId('board_id')->constrained('boards', 'board_id');
      $table->foreignId('column_id')->constrained('board_columns', 'column_id');
      $table->smallInteger('sort_index');
      $table->string('title', 255);
      $table->text('content')->nullable();
      $table->string('color', 6)->nullable();
      $table->boolean('is_archived')->default(false);
      $table->timestamp('created_datetime');
      $table->timestamp('updated_datetime')->nullable();
    });

    // Частичный индекс: активные карточки (Illuminate не умеет WHERE в index)
    DB::statement('CREATE INDEX idx_active_board_id ON cards (board_id) WHERE is_archived = false');
  }

  public function down(): void
  {
    Schema::dropIfExists('cards');
  }
};
