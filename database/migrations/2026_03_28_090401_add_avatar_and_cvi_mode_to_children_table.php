<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('children', function (Blueprint $table) {
            $table->string('avatar_path')->nullable()->after('name');
            $table->boolean('is_cvi_mode')->default(false)->after('avatar_path');
        });
    }

    public function down(): void
    {
        Schema::table('children', function (Blueprint $table) {
            $table->dropColumn(['avatar_path', 'is_cvi_mode']);
        });
    }
};
