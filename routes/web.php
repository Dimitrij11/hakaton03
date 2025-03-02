<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\StudentDataController;
use App\Models\Achievement;
use App\Models\Category;
use App\Models\Course;
use App\Models\CourseProfessor;
use App\Models\Enrollment;
use App\Models\ForumComment;
use App\Models\ForumThread;
use App\Models\Interest;
use App\Models\Lesson;
use App\Models\Message;
use App\Models\Module;
use App\Models\NewsletterSubscription;
use App\Models\ProfessorData;
use App\Models\Review;
use App\Models\Role;
use App\Models\StudentData;
use App\Models\StudentInterest;
use App\Models\User;
use App\Models\UserProgress;
use App\Models\View;

Route::get('/hello', function () {
    return 'Hello wOrld';
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);
Route::get('/user', [AuthController::class, 'user'])->middleware('auth');
Route::get('/users', [UserController::class, 'getUsers']);
Route::get('/views', [UserController::class, 'getViews']);
Route::get('/students', [StudentDataController::class, 'index']);
Route::post('/students', [StudentDataController::class, 'store']);
Route::get('/students/{id}', [StudentDataController::class, 'show']);
Route::put('/students/{id}', [StudentDataController::class, 'update']);
Route::delete('/students/{id}', [StudentDataController::class, 'destroy']);

// Simple admin login page - for frontend to connect later
Route::get('/admin/login', function () {
    return \Illuminate\Support\Facades\View::make('admin.login');
});

Route::get('/seed', function () {
    $path = \Illuminate\Support\Facades\App::basePath('storage/app/json_file/achievements.json');
    $achievements = file_get_contents($path);

    foreach (json_decode($achievements, true) as $achievement) {
        Achievement::create(['name' => $achievement['name']]);
    }

    $path = \Illuminate\Support\Facades\App::basePath('storage/app/json_file/categories.json');
    $categories = file_get_contents($path);

    foreach (json_decode($categories, true) as $category) {
        Category::create(['name' => $category['name']]);
    }

    // $answers = file_get_contents(base_path('storage/app/json_file/answers.json'));

    // foreach (json_decode($answers, true) as $answer) {
    //     Answer::create(['name' => $answer['name']]);
    // }

    $path = \Illuminate\Support\Facades\App::basePath('storage/app/json_file/course_professors.json');
    $CourseProfessors = file_get_contents($path);

    foreach (json_decode($CourseProfessors, true) as $courseProfessor) {
        CourseProfessor::create(['name' => $courseProfessor['name']]);
    }

    $path = \Illuminate\Support\Facades\App::basePath('storage/app/json_file/courses.json');
    $courses = file_get_contents($path);

    foreach (json_decode($courses, true) as $course) {
        Course::create(['name' => $course['name']]);
    }

    $path = \Illuminate\Support\Facades\App::basePath('storage/app/json_file/forum_comments.json');
    $ForumComments = file_get_contents($path);

    foreach (json_decode($ForumComments, true) as $comment) {
        ForumComment::create(['name' => $comment['name']]);
    }

    $path = \Illuminate\Support\Facades\App::basePath('storage/app/json_file/forum_threads.json');
    $ForumThreads = file_get_contents($path);

    foreach (json_decode($ForumThreads, true) as $thread) {
        ForumThread::create(['name' => $thread['name']]);
    }

    $path = \Illuminate\Support\Facades\App::basePath('storage/app/json_file/interests.json');
    $Interests = file_get_contents($path);

    foreach (json_decode($Interests, true) as $Interest) {
        Interest::create(['name' => $Interest['name']]);
    }

    $path = \Illuminate\Support\Facades\App::basePath('storage/app/json_file/lessons.json');
    $Lessons = file_get_contents($path);

    foreach (json_decode($Lessons, true) as $Lesson) {
        Lesson::create(['name' => $Lesson['name']]);
    }

    $path = \Illuminate\Support\Facades\App::basePath('storage/app/json_file/messages.json');
    $Messages = file_get_contents($path);

    foreach (json_decode($Messages, true) as $Message) {
        Message::create(['name' => $Message['name']]);
    }

    $path = \Illuminate\Support\Facades\App::basePath('storage/app/json_file/modules.json');
    $Modules = file_get_contents($path);

    foreach (json_decode($Modules, true) as $Module) {
        Module::create(['name' => $Module['name']]);
    }

    $path = \Illuminate\Support\Facades\App::basePath('storage/app/json_file/newsletter_substriptions.json');
    $NewsletterSubscriptions = file_get_contents($path);

    foreach (json_decode($NewsletterSubscriptions, true) as $Subscription) {
        NewsletterSubscription::create(['name' => $Subscription['name']]);
    }

    $path = \Illuminate\Support\Facades\App::basePath('storage/app/json_file/professor_data.json');
    $ProfessorData = file_get_contents($path);

    foreach (json_decode($ProfessorData, true) as $ProfData) {
        ProfessorData::create(['name' => $ProfData['name']]);
    }

    // $Questions = file_get_contents(base_path('storage/app/json_file/questions.json'));

    // foreach (json_decode($Questions, true) as $Question) {
    //     Questions::create(['name' => $Question['name']]);
    // }

    // $Quizzes = file_get_contents(base_path('storage/app/json_file/quizzes.json'));

    // foreach (json_decode($Quizzes, true) as $Quiz) {
    //     Quizzes::create(['name' => $Quiz['name']]);
    // }

    $path = \Illuminate\Support\Facades\App::basePath('storage/app/json_file/reviewes.json');
    $Reviewes = file_get_contents($path);

    foreach (json_decode($Reviewes, true) as $Review) {
        Review::create(['name' => $Review['name']]);
    }

    $path = \Illuminate\Support\Facades\App::basePath('storage/app/json_file/roles.json');
    $Roles = file_get_contents($path);

    foreach (json_decode($Roles, true) as $Role) {
        Role::create(['name' => $Role['name']]);
    }

    $path = \Illuminate\Support\Facades\App::basePath('storage/app/json_file/student_data.json');
    $StudentData = file_get_contents($path);

    foreach (json_decode($StudentData, true) as $SttData) {
        StudentData::create(['name' => $SttData['name']]);
    }

    $path = \Illuminate\Support\Facades\App::basePath('storage/app/json_file/student_interests.json');
    $StudentInterests = file_get_contents($path);

    foreach (json_decode($StudentInterests, true) as $SttInterest) {
        StudentInterest::create(['name' => $SttInterest['name']]);
    }

    $path = \Illuminate\Support\Facades\App::basePath('storage/app/json_file/user_progress.json');
    $UserProgress = file_get_contents($path);

    foreach (json_decode($UserProgress, true) as $UssProgress) {
        UserProgress::create(['name' => $UssProgress['name']]);
    }

    // $UserResponses = file_get_contents(base_path('storage/app/json_file/user_responses.json'));

    // foreach (json_decode($UserResponses, true) as $UssResponse) {
    //     UserResponse::create(['name' => $UssResponse['name']]);
    // }

    $path = \Illuminate\Support\Facades\App::basePath('storage/app/json_file/users.json');
    $Users = file_get_contents($path);

    foreach (json_decode($Users, true) as $User) {
        User::create(['name' => $User['name']]);
    }

    $path = \Illuminate\Support\Facades\App::basePath('storage/app/json_file/views.json');
    $Views = file_get_contents($path);

    foreach (json_decode($Views, true) as $View) {
        View::create(['name' => $View['name']]);
    }

    // $categories = file_get_contents(base_path('storage/app/json_file/categories.json'));

    // foreach (json_decode($categories, true) as $category) {
    //     Category::create(['name' => $category['name']]);
    // }
});

// Admin routes
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Admin\AdminDashboardController::class, 'index'])->name('admin.dashboard');
});

// Professor routes
Route::middleware(['auth', 'professor'])->prefix('professor')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Professor\ProfessorDashboardController::class, 'index'])->name('professor.dashboard');
});

// Student routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Student\StudentDashboardController::class, 'index'])->name('student.dashboard');
});
