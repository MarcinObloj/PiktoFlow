<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TemplateSet extends Model
{
    protected $fillable = ['name', 'description', 'icon', 'color'];

    public function items()
    {
        return $this->hasMany(TemplateSetPictogram::class)->orderBy('position');
    }
}
