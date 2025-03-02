<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForumPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'forum_id',
        'user_id',
        'title',
        'content',
        'status',
    ];

    // Relationship to Forum
    public function forum()
    {
        return $this->belongsTo(Forum::class);
    }

    // Relationship to User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relationship to ForumComments
    public function comments()
    {
        return $this->hasMany(ForumComment::class, 'post_id');
    }

    // Relationship to ForumPostLikes
    public function likes()
    {
        return $this->hasMany(ForumPostLike::class, 'post_id');
    }
}
