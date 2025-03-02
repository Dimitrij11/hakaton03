<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Forum;
use App\Models\ForumPost;
use App\Models\ForumComment;
use App\Models\ForumPostLike;
use App\Models\Enroll;
use Illuminate\Support\Facades\Auth;

class ForumInteractionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // Get all forums for enrolled courses
    public function getForums()
    {
        // Get all courses the user is enrolled in
        $enrolledCourseIds = Enroll::where('user_id', Auth::id())
            ->pluck('course_id');

        // Get all forums for those courses
        $forums = Forum::with('course')
            ->whereIn('course_id', $enrolledCourseIds)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($forums);
    }

    // Get a single forum with its posts
    public function getForum($id)
    {
        $forum = Forum::with(['course', 'posts' => function ($query) {
            $query->with(['user', 'comments' => function ($q) {
                $q->with('user')->orderBy('created_at', 'asc');
            }])
                ->withCount('likes')
                ->orderBy('created_at', 'desc');
        }])->findOrFail($id);

        // Check if user is enrolled in the course
        $isEnrolled = Enroll::where('user_id', Auth::id())
            ->where('course_id', $forum->course_id)
            ->exists();

        if (!$isEnrolled) {
            return response()->json(['message' => 'You are not enrolled in this course'], 403);
        }

        // For each post, check if the user has liked it
        foreach ($forum->posts as $post) {
            $post->is_liked_by_user = ForumPostLike::where('post_id', $post->id)
                ->where('user_id', Auth::id())
                ->exists();
        }

        return response()->json($forum);
    }

    // Create a new forum post
    public function createPost(Request $request)
    {
        $validated = $request->validate([
            'forum_id' => 'required|exists:forums,id',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $forum = Forum::findOrFail($validated['forum_id']);

        // Check if user is enrolled in the course
        $isEnrolled = Enroll::where('user_id', Auth::id())
            ->where('course_id', $forum->course_id)
            ->exists();

        if (!$isEnrolled) {
            return response()->json(['message' => 'You are not enrolled in this course'], 403);
        }

        // Create the post
        $post = ForumPost::create([
            'forum_id' => $validated['forum_id'],
            'user_id' => Auth::id(),
            'title' => $validated['title'],
            'content' => $validated['content'],
        ]);

        return response()->json([
            'message' => 'Post created successfully',
            'post' => $post->load('user')
        ], 201);
    }

    // Add a comment to a post
    public function addComment(Request $request)
    {
        $validated = $request->validate([
            'post_id' => 'required|exists:forum_posts,id',
            'content' => 'required|string',
        ]);

        $post = ForumPost::with('forum.course')->findOrFail($validated['post_id']);

        // Check if user is enrolled in the course
        $isEnrolled = Enroll::where('user_id', Auth::id())
            ->where('course_id', $post->forum->course_id)
            ->exists();

        if (!$isEnrolled) {
            return response()->json(['message' => 'You are not enrolled in this course'], 403);
        }

        // Create the comment
        $comment = ForumComment::create([
            'post_id' => $validated['post_id'],
            'user_id' => Auth::id(),
            'content' => $validated['content'],
        ]);

        return response()->json([
            'message' => 'Comment added successfully',
            'comment' => $comment->load('user')
        ], 201);
    }

    // Like or unlike a post
    public function toggleLike($postId)
    {
        $post = ForumPost::with('forum.course')->findOrFail($postId);

        // Check if user is enrolled in the course
        $isEnrolled = Enroll::where('user_id', Auth::id())
            ->where('course_id', $post->forum->course_id)
            ->exists();

        if (!$isEnrolled) {
            return response()->json(['message' => 'You are not enrolled in this course'], 403);
        }

        // Check if user already liked the post
        $existingLike = ForumPostLike::where('post_id', $postId)
            ->where('user_id', Auth::id())
            ->first();

        if ($existingLike) {
            // Unlike the post
            $existingLike->delete();
            $action = 'unliked';
        } else {
            // Like the post
            ForumPostLike::create([
                'post_id' => $postId,
                'user_id' => Auth::id(),
            ]);
            $action = 'liked';
        }

        // Get updated like count
        $likeCount = ForumPostLike::where('post_id', $postId)->count();

        return response()->json([
            'message' => "Post $action successfully",
            'like_count' => $likeCount,
            'is_liked' => $action === 'liked'
        ]);
    }

    // Delete a post (only if it belongs to the authenticated user)
    public function deletePost($postId)
    {
        $post = ForumPost::findOrFail($postId);

        // Check if the post belongs to the authenticated user
        if ($post->user_id !== Auth::id()) {
            return response()->json(['message' => 'You are not authorized to delete this post'], 403);
        }

        // Delete the post and all related comments and likes
        $post->comments()->delete();
        $post->likes()->delete();
        $post->delete();

        return response()->json(['message' => 'Post deleted successfully']);
    }

    // Delete a comment (only if it belongs to the authenticated user)
    public function deleteComment($commentId)
    {
        $comment = ForumComment::findOrFail($commentId);

        // Check if the comment belongs to the authenticated user
        if ($comment->user_id !== Auth::id()) {
            return response()->json(['message' => 'You are not authorized to delete this comment'], 403);
        }

        // Delete the comment
        $comment->delete();

        return response()->json(['message' => 'Comment deleted successfully']);
    }
}
