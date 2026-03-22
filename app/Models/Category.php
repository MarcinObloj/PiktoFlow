<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name', 'color', 'user_id'];

    public function pictograms()
    {
        return $this->hasMany(Pictogram::class);
    }
}
