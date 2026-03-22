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
        'category_id',
        'is_custom'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
