<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;  // ✅ Step 1: Import SoftDeletes

class ForumThread extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'title',
        'content',
    ];

    /**
     * Get the user who created the thread.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the comments for the thread.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(ForumComment::class, 'thread_id');
    }

    use SoftDeletes; // ✅ Step 2: Enable SoftDeletes

    protected $dates = ['deleted_at']; // ✅ Step 3: Define deleted_at column
}
