<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SentenceLog extends Model
{
    protected $fillable = [
        'child_id',
        'pictogram_ids',
        'length',
        'source',
    ];

    protected $casts = [
        'pictogram_ids' => 'array',
        'length' => 'integer',
    ];

    public function child()
    {
        return $this->belongsTo(Child::class);
    }
}
