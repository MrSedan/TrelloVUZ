<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('sessions', function (Blueprint $table) {
      $table->string('id', 256)->primary();
      $table->text('payload');
      $table->bigInteger('expires_at')->index();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('sessions');
  }
};
