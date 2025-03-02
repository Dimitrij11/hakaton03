<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    use HasFactory;

    protected $fillable = [
        'module_id',
        'title',
        'description',
        'time_limit',
        'passing_score',
        'is_published',
        'max_attempts',
        'due_date',
    ];

    protected $casts = [
        'time_limit' => 'integer',
        'passing_score' => 'float',
        'is_published' => 'boolean',
        'max_attempts' => 'integer',
        'due_date' => 'datetime',
    ];

    /**
     * Get the module that owns the quiz
     */
    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    /**
     * Get the questions for the quiz
     */
    public function questions()
    {
        return $this->hasMany(QuizQuestion::class);
    }

    /**
     * Get the attempts for the quiz
     */
    public function attempts()
    {
        return $this->hasMany(QuizAttempt::class);
    }
}
