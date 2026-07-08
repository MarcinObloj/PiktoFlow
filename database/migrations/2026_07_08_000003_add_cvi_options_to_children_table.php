<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('children', function (Blueprint $table) {
            // CVI accent colour (border/highlight) and grid density for the board.
            $table->string('cvi_accent_color', 7)->default('#facc15')->after('is_cvi_mode');
            $table->string('cvi_grid_density')->default('normal')->after('cvi_accent_color');
        });
    }

    public function down(): void
    {
        Schema::table('children', function (Blueprint $table) {
            $table->dropColumn(['cvi_accent_color', 'cvi_grid_density']);
        });
    }
};
