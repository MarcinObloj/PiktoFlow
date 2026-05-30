<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TemplateSetPictogram extends Model
{
    protected $fillable = ['template_set_id', 'name', 'image_path', 'category_name', 'position'];

    public function templateSet()
    {
        return $this->belongsTo(TemplateSet::class);
    }
}
