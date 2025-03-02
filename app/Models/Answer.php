<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    use HasFactory;

    protected $fillable = [
        'content',
        'question_id',
        'is_correct',
        'explanation',
    ];

    /**
     * Get the question that owns the answer
     */
    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * Get the user responses for this answer
     */
    public function userResponses()
    {
        return $this->hasMany(UserResponse::class);
    }
}
