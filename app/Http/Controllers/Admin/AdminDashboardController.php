<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Course;
use App\Models\Enroll;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Forum;
use App\Models\ForumPost;

class AdminDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    /**
     * Display admin dashboard data
     */
    public function index()
    {
        // Count statistics for dashboard
        $totalUsers = User::count();
        $totalStudents = User::where('role_id', 3)->count(); // Assuming role_id 3 is for students
        $totalProfessors = User::where('role_id', 2)->count(); // Assuming role_id 2 is for professors
        $totalCourses = Course::count();
        $totalEnrollments = Enroll::count();
        $totalQuizzes = Quiz::count();
        $totalQuizAttempts = QuizAttempt::count();
        $totalForums = Forum::count();
        $totalForumPosts = ForumPost::count();

        // Recent users
        $recentUsers = User::orderBy('created_at', 'desc')->take(5)->get();

        // Recent courses
        $recentCourses = Course::with('professor')->orderBy('created_at', 'desc')->take(5)->get();

        // Recent enrollments
        $recentEnrollments = Enroll::with(['user', 'course'])->orderBy('created_at', 'desc')->take(5)->get();

        // Popular courses (most enrollments)
        $popularCourses = Course::withCount('enrollments')
            ->orderBy('enrollments_count', 'desc')
            ->take(5)
            ->get();

        // Activity statistics
        $activeQuizzes = Quiz::where('is_published', true)->count();
        $completedAttempts = QuizAttempt::where('is_completed', true)->count();
        $activeForums = Forum::where('is_active', true)->count();

        return view('admin.dashboard', compact(
            'totalUsers',
            'totalStudents',
            'totalProfessors',
            'totalCourses',
            'totalEnrollments',
            'totalQuizzes',
            'totalQuizAttempts',
            'totalForums',
            'totalForumPosts',
            'recentUsers',
            'recentCourses',
            'recentEnrollments',
            'popularCourses',
            'activeQuizzes',
            'completedAttempts',
            'activeForums'
        ));
    }

    /**
     * API endpoint for dashboard data
     */
    public function dashboardData()
    {
        // Count statistics for dashboard
        $totalUsers = User::count();
        $totalStudents = User::where('role_id', 3)->count();
        $totalProfessors = User::where('role_id', 2)->count();
        $totalCourses = Course::count();
        $totalEnrollments = Enroll::count();
        $totalQuizzes = Quiz::count();
        $totalQuizAttempts = QuizAttempt::count();
        $totalForums = Forum::count();

        // Recent activity
        $recentEnrollments = Enroll::with(['user:id,name', 'course:id,title'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Popular courses
        $popularCourses = Course::withCount('enrollments')
            ->orderBy('enrollments_count', 'desc')
            ->take(5)
            ->get(['id', 'title', 'enrollments_count']);

        return response()->json([
            'statistics' => [
                'totalUsers' => $totalUsers,
                'totalStudents' => $totalStudents,
                'totalProfessors' => $totalProfessors,
                'totalCourses' => $totalCourses,
                'totalEnrollments' => $totalEnrollments,
                'totalQuizzes' => $totalQuizzes,
                'totalQuizAttempts' => $totalQuizAttempts,
                'totalForums' => $totalForums,
            ],
            'recentActivity' => [
                'enrollments' => $recentEnrollments
            ],
            'popularCourses' => $popularCourses
        ]);
    }
}
