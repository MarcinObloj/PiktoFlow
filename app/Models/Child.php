<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Child extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'age',
        'hobbies',
        'avatar_path',
        'is_cvi_mode',
        'cvi_accent_color',
        'cvi_grid_density',
        'daily_plan',
        'tts_voice',
        'tts_rate',
        'tts_pitch',
        'tts_volume',
    ];

    protected $casts = [
        'is_cvi_mode' => 'boolean',
        'daily_plan' => 'array',
        'tts_rate' => 'float',
        'tts_pitch' => 'float',
        'tts_volume' => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pictograms()
    {
        return $this->belongsToMany(Pictogram::class, 'child_pictogram')->withPivot('position');
    }
    public function quizResults()
    {
        return $this->hasMany(QuizResult::class);
    }

    public function sentenceLogs()
    {
        return $this->hasMany(SentenceLog::class);
    }
}
