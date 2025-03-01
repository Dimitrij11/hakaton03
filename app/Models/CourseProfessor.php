<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseProfessor extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'course_professor';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'course_id',
        'professor_id',
    ];

    /**
     * Get the course that the professor is teaching.
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the professor that is teaching the course.
     */
    public function professor(): BelongsTo
    {
        return $this->belongsTo(ProfessorData::class, 'professor_id');
    }
}
