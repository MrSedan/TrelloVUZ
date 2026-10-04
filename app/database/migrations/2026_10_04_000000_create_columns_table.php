<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('columns', function (Blueprint $table) {
      $table->bigIncrements('id');
      $table->string('title');
      $table->integer('position')->default(0);
      $table->timestamp('created_at')->useCurrent();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('columns');
  }
};
