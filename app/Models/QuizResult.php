<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizResult extends Model
{
    protected $fillable = ['child_id', 'score', 'total_questions', 'created_at'];

    public function child()
    {
        return $this->belongsTo(Child::class);
    }
}
