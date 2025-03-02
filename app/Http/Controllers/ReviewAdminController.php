<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Review;
use App\Models\Course;

class ReviewAdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    public function index(Request $request)
    {
        $courseId = $request->query('course_id');

        if ($courseId) {
            $reviews = Review::where('course_id', $courseId)
                ->with(['user', 'course'])
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            $reviews = Review::with(['user', 'course'])
                ->orderBy('created_at', 'desc')
                ->paginate(15);
        }

        return response()->json($reviews);
    }

    public function show($id)
    {
        $review = Review::with(['user', 'course'])->findOrFail($id);
        return response()->json($review);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'course_id' => 'required|exists:courses,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string',
            'is_published' => 'sometimes|boolean',
        ]);

        if (!isset($validated['is_published'])) {
            $validated['is_published'] = true;
        }

        $review = Review::create($validated);

        // Update course average rating
        $this->updateCourseRating($validated['course_id']);

        return response()->json($review, 201);
    }

    public function update(Request $request, $id)
    {
        $review = Review::findOrFail($id);

        $validated = $request->validate([
            'user_id' => 'sometimes|exists:users,id',
            'course_id' => 'sometimes|exists:courses,id',
            'rating' => 'sometimes|integer|min:1|max:5',
            'comment' => 'sometimes|string',
            'is_published' => 'sometimes|boolean',
        ]);

        $oldCourseId = $review->course_id;
        $review->update($validated);

        // Update course average rating for old course if course_id changed
        if (isset($validated['course_id']) && $oldCourseId != $validated['course_id']) {
            $this->updateCourseRating($oldCourseId);
            $this->updateCourseRating($validated['course_id']);
        } else {
            $this->updateCourseRating($review->course_id);
        }

        return response()->json($review);
    }

    public function destroy($id)
    {
        $review = Review::findOrFail($id);
        $courseId = $review->course_id;

        $review->delete();

        // Update course average rating
        $this->updateCourseRating($courseId);

        return response()->json(['message' => 'Review deleted successfully']);
    }

    public function approve($id)
    {
        $review = Review::findOrFail($id);
        $review->is_published = true;
        $review->save();

        // Update course average rating
        $this->updateCourseRating($review->course_id);

        return response()->json(['message' => 'Review approved successfully']);
    }

    public function reject($id)
    {
        $review = Review::findOrFail($id);
        $review->is_published = false;
        $review->save();

        // Update course average rating
        $this->updateCourseRating($review->course_id);

        return response()->json(['message' => 'Review rejected successfully']);
    }

    private function updateCourseRating($courseId)
    {
        $course = Course::findOrFail($courseId);

        $averageRating = Review::where('course_id', $courseId)
            ->where('is_published', true)
            ->avg('rating');

        $course->average_rating = $averageRating ?? 0;
        $course->save();
    }
}
