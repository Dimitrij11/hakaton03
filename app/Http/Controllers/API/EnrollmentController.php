<?php

namespace App\Http\Controllers\API;

use App\Models\Enrollment;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class EnrollmentController extends BaseController
{
    /**
     * Display the authenticated user's enrollments.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function myEnrollments()
    {
        $enrollments = Enrollment::with(['course.category', 'course.professor'])
            ->where('user_id', Auth::id())
            ->get();

        $formattedEnrollments = $enrollments->map(function ($enrollment) {
            return [
                'id' => $enrollment->id,
                'course' => $enrollment->course,
                'enrolled_at' => $enrollment->created_at,
                'progress' => $enrollment->progress,
                'completed' => $enrollment->completed,
                'completion_date' => $enrollment->completion_date,
            ];
        });

        return $this->sendResponse($formattedEnrollments, 'Enrollments retrieved successfully.');
    }

    /**
     * Enroll the authenticated user in a course.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function enroll(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'course_id' => 'required|exists:courses,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors()->toArray(), 422);
        }

        // Check if the user is already enrolled in the course
        $existingEnrollment = Enrollment::where('user_id', Auth::id())
            ->where('course_id', $request->course_id)
            ->first();

        if ($existingEnrollment) {
            return $this->sendError(
                'Already enrolled',
                ['error' => 'You are already enrolled in this course'],
                422
            );
        }

        $course = Course::find($request->course_id);

        // Check if the course is free or if payment is required
        if (!$course->is_free) {
            // Here you would typically integrate with a payment gateway
            // For now, we'll just assume the payment was successful
            // In a real application, this would be handled by a payment service
        }

        $enrollment = new Enrollment();
        $enrollment->user_id = Auth::id();
        $enrollment->course_id = $request->course_id;
        $enrollment->progress = 0;
        $enrollment->completed = false;
        $enrollment->save();

        $enrollmentData = [
            'id' => $enrollment->id,
            'course' => $course->load('category', 'professor'),
            'enrolled_at' => $enrollment->created_at,
            'progress' => $enrollment->progress,
            'completed' => $enrollment->completed,
        ];

        return $this->sendResponse($enrollmentData, 'Enrolled in course successfully.');
    }

    /**
     * Update the progress of an enrollment.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProgress(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'progress' => 'required|integer|min:0|max:100',
            'completed' => 'boolean',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors()->toArray(), 422);
        }

        $enrollment = Enrollment::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$enrollment) {
            return $this->sendError('Enrollment not found.');
        }

        $enrollment->progress = $request->progress;

        if (isset($request->completed)) {
            $enrollment->completed = $request->completed;
            if ($request->completed) {
                $enrollment->completion_date = now();
            } else {
                $enrollment->completion_date = null;
            }
        } else if ($request->progress == 100) {
            $enrollment->completed = true;
            $enrollment->completion_date = now();
        }

        $enrollment->save();

        return $this->sendResponse($enrollment, 'Progress updated successfully.');
    }

    /**
     * Get all enrollments (admin only).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // Check if user is admin
        $user = User::find(Auth::id());
        if (!$user || !$user->isAdmin()) {
            return $this->sendError('Unauthorized.', ['error' => 'You do not have permission to view all enrollments'], 403);
        }

        $enrollments = Enrollment::with(['course', 'user'])
            ->paginate(15);

        return $this->sendResponse($enrollments, 'All enrollments retrieved successfully.');
    }

    /**
     * Get enrollments for a specific course (admin or professor only).
     *
     * @param  int  $courseId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByCourse($courseId)
    {
        $course = Course::find($courseId);

        if (!$course) {
            return $this->sendError('Course not found.');
        }

        // Check if user is admin or the professor of the course
        $user = User::find(Auth::id());
        if (!$user || (!$user->isAdmin() && !($user->isProfessor() && $course->professor_id == $user->id))) {
            return $this->sendError('Unauthorized.', ['error' => 'You do not have permission to view these enrollments'], 403);
        }

        $enrollments = Enrollment::with('user')
            ->where('course_id', $courseId)
            ->paginate(15);

        return $this->sendResponse($enrollments, 'Course enrollments retrieved successfully.');
    }
}
