<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\Enroll;
use App\Models\UserProgress;
use App\Models\User;
use App\Models\StudentData;
use App\Models\Lesson;
use App\Models\Wishlist;
use Illuminate\Support\Facades\Auth;

class StudentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // Get all available courses for catalog
    public function availableCourses(Request $request)
    {
        $query = Course::with(['category', 'professors.user'])
            ->where('is_published', true);

        // Filter by category if provided
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by level if provided
        if ($request->has('level')) {
            $query->where('level', $request->level);
        }

        // Search by title if provided
        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $courses = $query->paginate(10);

        return response()->json($courses);
    }

    // Get course details
    public function courseDetails($id)
    {
        $course = Course::with(['category', 'professors.user', 'modules.lessons', 'reviews.user'])
            ->findOrFail($id);

        // Check if the user is enrolled in this course
        $isEnrolled = false;
        if (Auth::check()) {
            $isEnrolled = Enroll::where('user_id', Auth::id())
                ->where('course_id', $id)
                ->exists();
        }

        // Get user progress if enrolled
        $progress = null;
        if ($isEnrolled) {
            $completedLessons = UserProgress::where('user_id', Auth::id())
                ->where('course_id', $id)
                ->where('is_completed', true)
                ->count();

            $totalLessons = Lesson::whereHas('module', function ($q) use ($id) {
                $q->where('course_id', $id);
            })->count();

            $progress = [
                'completed_lessons' => $completedLessons,
                'total_lessons' => $totalLessons,
                'percentage' => $totalLessons > 0 ? ($completedLessons / $totalLessons) * 100 : 0
            ];
        }

        return response()->json([
            'course' => $course,
            'is_enrolled' => $isEnrolled,
            'progress' => $progress
        ]);
    }

    // Enroll in a course
    public function enrollCourse(Request $request)
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
        ]);

        // Check if already enrolled
        $existing = Enroll::where('user_id', Auth::id())
            ->where('course_id', $validated['course_id'])
            ->first();

        if ($existing) {
            return response()->json(['message' => 'Already enrolled in this course'], 422);
        }

        // Create enrollment
        $enroll = Enroll::create([
            'user_id' => Auth::id(),
            'course_id' => $validated['course_id'],
            'enrollment_date' => now(),
            'status' => 'active'
        ]);

        return response()->json([
            'message' => 'Successfully enrolled in the course',
            'enrollment' => $enroll
        ], 201);
    }

    // Get all enrolled courses for authenticated user
    public function myEnrolledCourses()
    {
        $enrolledCourses = Enroll::with(['course.category', 'course.professors.user'])
            ->where('user_id', Auth::id())
            ->orderBy('enrollment_date', 'desc')
            ->get();

        foreach ($enrolledCourses as &$enrollment) {
            // Calculate progress
            $completedLessons = UserProgress::where('user_id', Auth::id())
                ->where('course_id', $enrollment->course_id)
                ->where('is_completed', true)
                ->count();

            $totalLessons = Lesson::whereHas('module', function ($q) use ($enrollment) {
                $q->where('course_id', $enrollment->course_id);
            })->count();

            $enrollment->progress = [
                'completed_lessons' => $completedLessons,
                'total_lessons' => $totalLessons,
                'percentage' => $totalLessons > 0 ? ($completedLessons / $totalLessons) * 100 : 0
            ];
        }

        return response()->json($enrolledCourses);
    }

    // Mark a lesson as completed
    public function completedLesson(Request $request)
    {
        $validated = $request->validate([
            'lesson_id' => 'required|exists:lessons,id',
        ]);

        $lesson = Lesson::with('module.course')->findOrFail($validated['lesson_id']);
        $courseId = $lesson->module->course_id;

        // Check if enrolled in the course
        $isEnrolled = Enroll::where('user_id', Auth::id())
            ->where('course_id', $courseId)
            ->exists();

        if (!$isEnrolled) {
            return response()->json(['message' => 'You are not enrolled in this course'], 403);
        }

        // Update or create progress record
        $progress = UserProgress::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'course_id' => $courseId,
                'lesson_id' => $validated['lesson_id']
            ],
            [
                'is_completed' => true,
                'completion_date' => now()
            ]
        );

        return response()->json([
            'message' => 'Lesson marked as completed',
            'progress' => $progress
        ]);
    }

    // Add a course to wishlist
    public function addToWishlist(Request $request)
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
        ]);

        // Check if already in wishlist
        $existing = Wishlist::where('user_id', Auth::id())
            ->where('course_id', $validated['course_id'])
            ->first();

        if ($existing) {
            return response()->json(['message' => 'Course already in wishlist'], 422);
        }

        // Add to wishlist
        $wishlist = Wishlist::create([
            'user_id' => Auth::id(),
            'course_id' => $validated['course_id'],
        ]);

        return response()->json([
            'message' => 'Course added to wishlist',
            'wishlist' => $wishlist
        ], 201);
    }

    // Remove a course from wishlist
    public function removeFromWishlist($courseId)
    {
        $wishlist = Wishlist::where('user_id', Auth::id())
            ->where('course_id', $courseId)
            ->first();

        if (!$wishlist) {
            return response()->json(['message' => 'Course not found in wishlist'], 404);
        }

        $wishlist->delete();

        return response()->json(['message' => 'Course removed from wishlist']);
    }

    // Get wishlist
    public function getWishlist()
    {
        $wishlist = Wishlist::with(['course.category', 'course.professors.user'])
            ->where('user_id', Auth::id())
            ->get();

        return response()->json($wishlist);
    }

    // Update profile
    public function updateProfile(Request $request)
    {
        $userId = Auth::id();

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $userId,
            'bio' => 'sometimes|string',
            'interests' => 'sometimes|array',
            'interests.*' => 'exists:interests,id',
        ]);

        // Update user basic info
        $user = User::findOrFail($userId);

        if (isset($validated['name'])) {
            $user->name = $validated['name'];
        }

        if (isset($validated['email'])) {
            $user->email = $validated['email'];
        }

        $user->save();

        // Update student data
        $studentData = StudentData::where('user_id', $userId)->first();

        if ($studentData && isset($validated['bio'])) {
            $studentData->bio = $validated['bio'];
            $studentData->save();
        }

        // Update interests if provided
        if (isset($validated['interests'])) {
            $user->interests()->sync($validated['interests']);
        }

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user->load(['studentData', 'interests'])
        ]);
    }
}
