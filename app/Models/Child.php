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
        'daily_plan'
    ];

    protected $casts = [
        'is_cvi_mode' => 'boolean',
        'daily_plan' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pictograms()
    {
        return $this->belongsToMany(Pictogram::class, 'child_pictogram')->withPivot('position');
    }
}
