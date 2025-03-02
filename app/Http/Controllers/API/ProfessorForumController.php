<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Forum;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ProfessorForumController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('professor');
    }

    /**
     * Display a listing of forums.
     */
    public function index()
    {
        $forums = Forum::with('course')->get();
        return response()->json(['forums' => $forums]);
    }

    /**
     * Display the specified forum.
     */
    public function show($id)
    {
        $forum = Forum::with(['posts.user', 'posts.comments.user', 'posts.likes'])->findOrFail($id);
        return response()->json(['forum' => $forum]);
    }

    /**
     * Store a newly created forum in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'course_id' => 'required|exists:courses,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Check if professor is associated with the course
        $professorId = Auth::id();
        $course = \App\Models\Course::where('id', $request->course_id)
            ->where('professor_id', $professorId)
            ->first();

        if (!$course) {
            return response()->json(['error' => 'You can only create forums for courses you teach'], 403);
        }

        $forum = Forum::create([
            'course_id' => $request->course_id,
            'title' => $request->title,
            'description' => $request->description,
            'is_active' => $request->has('is_active') ? $request->is_active : true,
        ]);

        return response()->json(['forum' => $forum, 'message' => 'Forum created successfully'], 201);
    }

    /**
     * Update the specified forum in storage.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'is_active' => 'sometimes|required|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $forum = Forum::findOrFail($id);

        // Check if professor is associated with the course
        $professorId = Auth::id();
        $course = \App\Models\Course::where('id', $forum->course_id)
            ->where('professor_id', $professorId)
            ->first();

        if (!$course) {
            return response()->json(['error' => 'You can only update forums for courses you teach'], 403);
        }

        $forum->update($request->only(['title', 'description', 'is_active']));

        return response()->json(['forum' => $forum, 'message' => 'Forum updated successfully']);
    }

    /**
     * Remove the specified forum from storage.
     */
    public function destroy($id)
    {
        $forum = Forum::findOrFail($id);

        // Check if professor is associated with the course
        $professorId = Auth::id();
        $course = \App\Models\Course::where('id', $forum->course_id)
            ->where('professor_id', $professorId)
            ->first();

        if (!$course) {
            return response()->json(['error' => 'You can only delete forums for courses you teach'], 403);
        }

        // Delete related posts, comments, and likes
        foreach ($forum->posts as $post) {
            foreach ($post->comments as $comment) {
                $comment->delete();
            }
            foreach ($post->likes as $like) {
                $like->delete();
            }
            $post->delete();
        }

        $forum->delete();

        return response()->json(['message' => 'Forum deleted successfully']);
    }
}
