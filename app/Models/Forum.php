<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Forum extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'title',
        'description',
        'status',
    ];

    // Relationship to Course
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    // Relationship to ForumPosts
    public function posts()
    {
        return $this->hasMany(ForumPost::class);
    }
}
