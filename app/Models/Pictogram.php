<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pictogram extends Model
{
    protected $fillable = ['name', 'image_path', 'category_id', 'is_custom'];

    public function children()
    {
        return $this->belongsToMany(Child::class);
    }
}
