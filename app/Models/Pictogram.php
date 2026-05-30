<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pictogram extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'image_path',
        'audio_path',
        'category_id',
        'is_custom',
        'user_id',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function children()
    {
        return $this->belongsToMany(Child::class, 'child_pictogram')->withPivot('position');
    }
}
