<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'attempt_id',
        'question_id',
        'option_id',
    ];

    // Relationship to QuizAttempt
    public function attempt()
    {
        return $this->belongsTo(QuizAttempt::class, 'attempt_id');
    }

    // Relationship to QuizQuestion
    public function question()
    {
        return $this->belongsTo(QuizQuestion::class, 'question_id');
    }

    // Relationship to selected QuestionOption
    public function selectedOption()
    {
        return $this->belongsTo(QuestionOption::class, 'option_id');
    }
}
