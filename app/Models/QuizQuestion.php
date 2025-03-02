<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'quiz_id',
        'question_text',
        'points',
        'question_type',
    ];

    // Relationship to Quiz
    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    // Relationship to QuestionOptions
    public function options()
    {
        return $this->hasMany(QuestionOption::class, 'question_id');
    }

    // Relationship to StudentAnswers
    public function studentAnswers()
    {
        return $this->hasMany(StudentAnswer::class, 'question_id');
    }
}
