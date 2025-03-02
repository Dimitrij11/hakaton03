<?php

namespace App\Http\Controllers\Professor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Course;
use App\Models\Enroll;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Forum;
use App\Models\ForumPost;

class ProfessorDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('professor');
    }

    /**
     * Display professor dashboard data
     */
    public function index()
    {
        $professorId = Auth::id();

        // Get courses taught by the professor
        $courses = Course::where('professor_id', $professorId)->get();
        $courseIds = $courses->pluck('id')->toArray();

        // Count statistics for dashboard
        $totalCourses = count($courses);
        $totalModules = 0;
        $totalLessons = 0;
        $totalQuizzes = 0;

        foreach ($courses as $course) {
            $totalModules += $course->modules()->count();

            foreach ($course->modules as $module) {
                $totalLessons += $module->lessons()->count();
                $totalQuizzes += $module->quizzes()->count();
            }
        }

        // Total students enrolled in professor's courses
        $totalStudents = Enroll::whereIn('course_id', $courseIds)
            ->distinct('user_id')
            ->count('user_id');

        // Recent enrollments
        $recentEnrollments = Enroll::with(['user', 'course'])
            ->whereIn('course_id', $courseIds)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Recent quiz attempts
        $quizIds = Quiz::whereIn('module_id', function ($query) use ($courseIds) {
            $query->select('id')
                ->from('modules')
                ->whereIn('course_id', $courseIds);
        })->pluck('id');

        $recentQuizAttempts = QuizAttempt::with(['user', 'quiz'])
            ->whereIn('quiz_id', $quizIds)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Popular courses (most enrollments)
        $popularCourses = Course::where('professor_id', $professorId)
            ->withCount('enrollments')
            ->orderBy('enrollments_count', 'desc')
            ->take(5)
            ->get();

        // Forums in professor's courses
        $totalForums = Forum::whereIn('course_id', $courseIds)->count();
        $forumIds = Forum::whereIn('course_id', $courseIds)->pluck('id');
        $totalForumPosts = ForumPost::whereIn('forum_id', $forumIds)->count();

        return view('professor.dashboard', compact(
            'totalCourses',
            'totalModules',
            'totalLessons',
            'totalQuizzes',
            'totalStudents',
            'totalForums',
            'totalForumPosts',
            'recentEnrollments',
            'recentQuizAttempts',
            'popularCourses',
            'courses'
        ));
    }

    /**
     * API endpoint for dashboard data
     */
    public function dashboardData()
    {
        $professorId = Auth::id();

        // Get courses taught by the professor
        $courses = Course::where('professor_id', $professorId)->get();
        $courseIds = $courses->pluck('id')->toArray();

        // Count statistics for dashboard
        $totalCourses = count($courses);
        $totalStudents = Enroll::whereIn('course_id', $courseIds)
            ->distinct('user_id')
            ->count('user_id');

        $quizIds = Quiz::whereIn('module_id', function ($query) use ($courseIds) {
            $query->select('id')
                ->from('modules')
                ->whereIn('course_id', $courseIds);
        })->pluck('id');

        $totalQuizzes = count($quizIds);
        $totalAttempts = QuizAttempt::whereIn('quiz_id', $quizIds)->count();

        // Recent activity
        $recentEnrollments = Enroll::with(['user:id,name', 'course:id,title'])
            ->whereIn('course_id', $courseIds)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Course statistics
        $courseStats = Course::where('professor_id', $professorId)
            ->withCount(['enrollments', 'modules'])
            ->get(['id', 'title', 'enrollments_count', 'modules_count', 'created_at']);

        return response()->json([
            'statistics' => [
                'totalCourses' => $totalCourses,
                'totalStudents' => $totalStudents,
                'totalQuizzes' => $totalQuizzes,
                'totalAttempts' => $totalAttempts,
            ],
            'recentActivity' => [
                'enrollments' => $recentEnrollments
            ],
            'courses' => $courseStats
        ]);
    }
}
