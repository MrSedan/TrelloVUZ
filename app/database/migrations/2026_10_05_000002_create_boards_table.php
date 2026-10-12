<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('boards', function (Blueprint $table) {
      $table->bigIncrements('board_id');
      $table->string('title', 255);
      $table->foreignId('owner_user_id')->constrained('users', 'user_id');
      $table->boolean('is_public')->default(false);
      $table->timestamp('created_datetime');
      $table->timestamp('updated_datetime')->nullable();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('boards');
  }
};
