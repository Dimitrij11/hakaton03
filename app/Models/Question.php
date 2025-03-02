<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'content',
        'quiz_id',
        'type',
        'order',
        'points',
    ];

    /**
     * Get the quiz that owns the question
     */
    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    /**
     * Get the answers for the question
     */
    public function answers()
    {
        return $this->hasMany(Answer::class);
    }

    /**
     * Get the user responses for the question
     */
    public function userResponses()
    {
        return $this->hasMany(UserResponse::class);
    }
}
