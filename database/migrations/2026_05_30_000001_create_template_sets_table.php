<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('template_sets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('icon')->default('🧩');
            $table->string('color')->default('#3b82f6');
            $table->timestamps();
        });

        Schema::create('template_set_pictograms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_set_id')->constrained('template_sets')->onDelete('cascade');
            $table->string('name');
            $table->string('image_path');
            $table->string('category_name')->default('Podstawowe');
            $table->integer('position')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('template_set_pictograms');
        Schema::dropIfExists('template_sets');
    }
};
