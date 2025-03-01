<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForumPostLike extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'user_id',
    ];

    // Relationship to ForumPost
    public function post()
    {
        return $this->belongsTo(ForumPost::class, 'post_id');
    }

    // Relationship to User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
