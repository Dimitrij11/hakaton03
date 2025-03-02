<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'quiz_id',
        'start_time',
        'end_time',
        'score',
        'is_passed',
        'status',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'score' => 'float',
        'is_passed' => 'boolean',
    ];

    // Relationship to User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relationship to Quiz
    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    // Relationship to StudentAnswers
    public function answers()
    {
        return $this->hasMany(StudentAnswer::class, 'attempt_id');
    }
}
