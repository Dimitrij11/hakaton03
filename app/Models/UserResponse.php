<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'question_id',
        'answer_id',
        'text_response',
        'is_correct',
    ];

    /**
     * Get the user that owns the response
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the question that owns the response
     */
    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * Get the answer that the user selected
     */
    public function answer()
    {
        return $this->belongsTo(Answer::class);
    }
}
