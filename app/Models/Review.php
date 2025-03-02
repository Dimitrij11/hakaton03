<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;  // ✅ Step 1: Import SoftDeletes

class Review extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'course_id',
        'rating',
        'comment',
    ];

    /**
     * Get the user that wrote the review.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the course that was reviewed.
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    use SoftDeletes; // ✅ Step 2: Enable SoftDeletes

    protected $dates = ['deleted_at']; // ✅ Step 3: Define deleted_at column
}
