<?php

namespace App\Http\Controllers\API;

use App\Models\Course;
use App\Models\User;
use App\Models\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CourseController extends BaseController
{
    /**
     * Display a listing of the courses.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = Course::with(['category', 'reviews', 'professors']);

        // Filter by category if provided
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Search by title if provided
        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        // Sort by rating if requested
        if ($request->has('sort_by_rating') && $request->sort_by_rating === 'true') {
            $courses = $query->get()->sortByDesc(function ($course) {
                return $course->getAverageRatingAttribute();
            });
        } else {
            $courses = $query->get();
        }

        return $this->sendResponse($courses, 'Courses retrieved successfully.');
    }

    /**
     * Store a newly created course in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Check if user is admin or professor
        $user = User::find(Auth::id());
        if (!$user || (!$user->isProfessor() && !$user->isAdmin())) {
            return $this->sendError('Unauthorized', ['error' => 'Only professors and admins can create courses'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors()->toArray(), 422);
        }

        $course = Course::create($request->all());

        // Associate professor with course if user is a professor
        if ($user->isProfessor()) {
            $professorData = $user->professorData;
            if ($professorData) {
                $course->professors()->attach($professorData->id);
            }
        }

        return $this->sendResponse($course, 'Course created successfully.');
    }

    /**
     * Display the specified course.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $course = Course::with([
            'category',
            'professors.user',
            'modules.lessons',
            'reviews.user',
            'modules.quizzes.questions.answers'
        ])->find($id);

        if (is_null($course)) {
            return $this->sendError('Course not found.');
        }

        // Record a view for the course if user is authenticated
        if (Auth::check()) {
            View::create([
                'user_id' => Auth::id(),
                'course_id' => $id
            ]);
        }

        return $this->sendResponse($course, 'Course retrieved successfully.');
    }

    /**
     * Update the specified course in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $course = Course::find($id);

        if (is_null($course)) {
            return $this->sendError('Course not found.');
        }

        // Check if user is admin or the professor of this course
        $user = User::find(Auth::id());
        if (!$user) {
            return $this->sendError('Unauthorized.', [], 401);
        }

        $canEdit = $user->isAdmin();

        if ($user->isProfessor()) {
            $professorData = $user->professorData;
            if ($professorData && $course->professors->contains($professorData->id)) {
                $canEdit = true;
            }
        }

        if (!$canEdit) {
            return $this->sendError('Unauthorized', ['error' => 'You cannot edit this course'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'category_id' => 'sometimes|required|exists:categories,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors()->toArray(), 422);
        }

        $course->update($request->all());

        return $this->sendResponse($course, 'Course updated successfully.');
    }

    /**
     * Remove the specified course from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        // Only admin can delete courses
        $user = User::find(Auth::id());
        if (!$user || !$user->isAdmin()) {
            return $this->sendError('Unauthorized', ['error' => 'Only administrators can delete courses'], 403);
        }

        $course = Course::find($id);

        if (is_null($course)) {
            return $this->sendError('Course not found.');
        }

        $course->delete();

        return $this->sendResponse([], 'Course deleted successfully.');
    }
}
