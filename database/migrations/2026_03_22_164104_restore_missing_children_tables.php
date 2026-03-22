<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Odtwarzamy tabelę dzieci (jeśli jej nie ma)
        if (!Schema::hasTable('children')) {
            Schema::create('children', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('name');
                $table->timestamps();
            });
        }

        // 2. Odtwarzamy tabelę łączącą dzieci z piktogramami (jeśli jej nie ma)
        if (!Schema::hasTable('child_pictogram')) {
            Schema::create('child_pictogram', function (Blueprint $table) {
                $table->id();
                $table->foreignId('child_id')->constrained()->onDelete('cascade');
                $table->foreignId('pictogram_id')->constrained()->onDelete('cascade');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('child_pictogram');
        Schema::dropIfExists('children');
    }
};
