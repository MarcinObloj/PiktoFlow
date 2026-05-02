<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('children', function (Blueprint $table) {
            $table->string('tts_voice')->nullable()->after('hobbies');
            $table->float('tts_rate')->default(0.9)->after('tts_voice');
            $table->float('tts_pitch')->default(1.0)->after('tts_rate');
            $table->float('tts_volume')->default(1.0)->after('tts_pitch');
        });
    }

    public function down(): void
    {
        Schema::table('children', function (Blueprint $table) {
            $table->dropColumn(['tts_voice', 'tts_rate', 'tts_pitch', 'tts_volume']);
        });
    }
};
