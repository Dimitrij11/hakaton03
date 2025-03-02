<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfessorCourse extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'course_id',
    ];

    // Relationship to User (Professor)
    public function professor()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relationship to Course
    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
