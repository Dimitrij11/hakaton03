<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ForumThread;
use App\Models\ForumComment;
use App\Models\User;

class ForumAdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    // Forum Threads
    public function indexThreads(Request $request)
    {
        $threads = ForumThread::with(['user', 'comments'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json($threads);
    }

    public function showThread($id)
    {
        $thread = ForumThread::with(['user', 'comments.user'])
            ->findOrFail($id);

        return response()->json($thread);
    }

    public function storeThread(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'user_id' => 'required|exists:users,id',
            'is_pinned' => 'sometimes|boolean',
            'is_locked' => 'sometimes|boolean',
        ]);

        // Default values
        if (!isset($validated['is_pinned'])) {
            $validated['is_pinned'] = false;
        }
        if (!isset($validated['is_locked'])) {
            $validated['is_locked'] = false;
        }

        $thread = ForumThread::create($validated);

        return response()->json($thread, 201);
    }

    public function updateThread(Request $request, $id)
    {
        $thread = ForumThread::findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
            'user_id' => 'sometimes|exists:users,id',
            'is_pinned' => 'sometimes|boolean',
            'is_locked' => 'sometimes|boolean',
        ]);

        $thread->update($validated);

        return response()->json($thread);
    }

    public function destroyThread($id)
    {
        $thread = ForumThread::findOrFail($id);
        $thread->delete();

        return response()->json(['message' => 'Forum thread deleted successfully']);
    }

    // Forum Comments
    public function indexComments(Request $request)
    {
        $threadId = $request->query('thread_id');

        if ($threadId) {
            $comments = ForumComment::where('thread_id', $threadId)
                ->with('user')
                ->orderBy('created_at', 'asc')
                ->get();
        } else {
            $comments = ForumComment::with(['user', 'thread'])
                ->orderBy('created_at', 'desc')
                ->paginate(15);
        }

        return response()->json($comments);
    }

    public function showComment($id)
    {
        $comment = ForumComment::with(['user', 'thread'])
            ->findOrFail($id);

        return response()->json($comment);
    }

    public function storeComment(Request $request)
    {
        $validated = $request->validate([
            'content' => 'required|string',
            'user_id' => 'required|exists:users,id',
            'thread_id' => 'required|exists:forum_threads,id',
        ]);

        $comment = ForumComment::create($validated);

        return response()->json($comment, 201);
    }

    public function updateComment(Request $request, $id)
    {
        $comment = ForumComment::findOrFail($id);

        $validated = $request->validate([
            'content' => 'sometimes|string',
            'user_id' => 'sometimes|exists:users,id',
            'thread_id' => 'sometimes|exists:forum_threads,id',
        ]);

        $comment->update($validated);

        return response()->json($comment);
    }

    public function destroyComment($id)
    {
        $comment = ForumComment::findOrFail($id);
        $comment->delete();

        return response()->json(['message' => 'Forum comment deleted successfully']);
    }

    // Moderation actions
    public function pinThread($id)
    {
        $thread = ForumThread::findOrFail($id);
        $thread->is_pinned = true;
        $thread->save();

        return response()->json(['message' => 'Thread pinned successfully']);
    }

    public function unpinThread($id)
    {
        $thread = ForumThread::findOrFail($id);
        $thread->is_pinned = false;
        $thread->save();

        return response()->json(['message' => 'Thread unpinned successfully']);
    }

    public function lockThread($id)
    {
        $thread = ForumThread::findOrFail($id);
        $thread->is_locked = true;
        $thread->save();

        return response()->json(['message' => 'Thread locked successfully']);
    }

    public function unlockThread($id)
    {
        $thread = ForumThread::findOrFail($id);
        $thread->is_locked = false;
        $thread->save();

        return response()->json(['message' => 'Thread unlocked successfully']);
    }
}
