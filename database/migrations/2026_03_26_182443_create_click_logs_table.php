<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('click_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_id')->constrained()->onDelete('cascade');
            $table->foreignId('pictogram_id')->constrained()->onDelete('cascade');
            $table->timestamps(); // To automatycznie zapisze nam datę i godzinę kliknięcia
        });
    }

    public function down(): void {
        Schema::dropIfExists('click_logs');
    }
};
