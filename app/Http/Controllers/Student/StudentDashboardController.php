<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Enroll;
use App\Models\Course;
use App\Models\UserProgress;
use App\Models\Wishlist;
use App\Models\QuizAttempt;

class StudentDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display student dashboard data
     */
    public function index()
    {
        $userId = Auth::id();

        // Get enrolled courses
        $enrollments = Enroll::with('course.modules.lessons')
            ->where('user_id', $userId)
            ->get();

        $enrolledCourseIds = $enrollments->pluck('course_id')->toArray();

        // Get wishlist courses
        $wishlistItems = Wishlist::with('course')
            ->where('user_id', $userId)
            ->get();

        // Get user progress data
        $progressData = UserProgress::where('user_id', $userId)
            ->whereIn('course_id', $enrolledCourseIds)
            ->get();

        // Calculate completion percentage for each course
        $courseProgress = [];
        $overallProgress = 0;
        $totalLessons = 0;
        $completedLessons = 0;

        foreach ($enrollments as $enrollment) {
            $course = $enrollment->course;
            $lessonCount = 0;

            foreach ($course->modules as $module) {
                $lessonCount += $module->lessons->count();
            }

            $completed = $progressData
                ->where('course_id', $course->id)
                ->where('is_completed', true)
                ->count();

            $percentage = ($lessonCount > 0) ? round(($completed / $lessonCount) * 100) : 0;

            $courseProgress[] = [
                'course' => $course,
                'percentage' => $percentage,
                'completed_lessons' => $completed,
                'total_lessons' => $lessonCount
            ];

            $totalLessons += $lessonCount;
            $completedLessons += $completed;
        }

        $overallProgress = ($totalLessons > 0) ? round(($completedLessons / $totalLessons) * 100) : 0;

        // Get recent quiz attempts
        $recentQuizAttempts = QuizAttempt::with(['quiz.module.course'])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Get recommended courses (not enrolled, not in wishlist)
        $recommendedCourses = Course::whereNotIn('id', array_merge($enrolledCourseIds, $wishlistItems->pluck('course_id')->toArray()))
            ->inRandomOrder()
            ->limit(3)
            ->get();

        return view('student.dashboard', compact(
            'enrollments',
            'wishlistItems',
            'courseProgress',
            'overallProgress',
            'recentQuizAttempts',
            'recommendedCourses',
            'completedLessons',
            'totalLessons'
        ));
    }

    /**
     * API endpoint for dashboard data
     */
    public function dashboardData()
    {
        $userId = Auth::id();

        // Get enrolled courses with progress data
        $enrollments = Enroll::with(['course:id,title,description,image'])
            ->where('user_id', $userId)
            ->get();

        $enrolledCourseIds = $enrollments->pluck('course_id')->toArray();

        // Get completion percentage for each course
        $courses = [];
        $totalCompletedLessons = 0;
        $totalLessons = 0;

        foreach ($enrollments as $enrollment) {
            $courseId = $enrollment->course_id;

            $lessonCount = DB::table('lessons')
                ->join('modules', 'lessons.module_id', '=', 'modules.id')
                ->where('modules.course_id', $courseId)
                ->count();

            $completedCount = UserProgress::where('user_id', $userId)
                ->where('course_id', $courseId)
                ->where('is_completed', true)
                ->count();

            $progressPercentage = ($lessonCount > 0) ? round(($completedCount / $lessonCount) * 100) : 0;

            $course = $enrollment->course;
            $course->progress = $progressPercentage;
            $course->completed_lessons = $completedCount;
            $course->total_lessons = $lessonCount;

            $courses[] = $course;
            $totalCompletedLessons += $completedCount;
            $totalLessons += $lessonCount;
        }

        // Calculate overall progress
        $overallProgress = ($totalLessons > 0) ? round(($totalCompletedLessons / $totalLessons) * 100) : 0;

        // Get recent quiz attempts
        $recentQuizAttempts = QuizAttempt::with(['quiz:id,title,module_id'])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get(['id', 'quiz_id', 'score', 'max_score', 'is_completed', 'created_at']);

        // Get wishlist items
        $wishlist = Wishlist::with(['course:id,title,description,image'])
            ->where('user_id', $userId)
            ->get();

        return response()->json([
            'statistics' => [
                'enrolledCourses' => count($enrollments),
                'overallProgress' => $overallProgress,
                'completedLessons' => $totalCompletedLessons,
                'totalLessons' => $totalLessons,
                'wishlistCount' => count($wishlist)
            ],
            'courses' => $courses,
            'recentQuizAttempts' => $recentQuizAttempts,
            'wishlist' => $wishlist->pluck('course')
        ]);
    }
}
