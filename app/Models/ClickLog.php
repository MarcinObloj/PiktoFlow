<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClickLog extends Model
{
    use HasFactory;

    protected $fillable = ['child_id', 'pictogram_id'];

    public function pictogram()
    {
        return $this->belongsTo(Pictogram::class);
    }
}
