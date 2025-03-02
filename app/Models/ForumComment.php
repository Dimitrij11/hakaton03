<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;  // ✅ Step 1: Import SoftDeletes

class ForumComment extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'thread_id',
        'content',
        'parent_id',
    ];

    /**
     * Get the user who wrote the comment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the thread that the comment belongs to.
     */
    public function thread(): BelongsTo
    {
        return $this->belongsTo(ForumThread::class);
    }

    /**
     * Get the parent comment if this is a reply.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(ForumComment::class, 'parent_id');
    }

    /**
     * Get the child comments (replies) to this comment.
     */
    public function replies(): HasMany
    {
        return $this->hasMany(ForumComment::class, 'parent_id');
    }

    use SoftDeletes; // ✅ Step 2: Enable SoftDeletes

    protected $dates = ['deleted_at']; // ✅ Step 3: Define deleted_at column
}
