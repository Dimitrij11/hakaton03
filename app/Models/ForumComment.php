<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ForumComment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'post_id',
        'user_id',
        'content',
        'status',
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

    // Relationship to ForumPost
    public function post()
    {
        return $this->belongsTo(ForumPost::class, 'post_id');
    }
}
