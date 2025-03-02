<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\StudentController;
use App\Http\Controllers\API\QuizAttemptController;
use App\Http\Controllers\API\ForumInteractionController;
use App\Http\Controllers\API\ProfessorCourseController;
use App\Http\Controllers\API\ProfessorQuizController;
use App\Http\Controllers\API\ProfessorForumController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\Auth\ApiAuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Student API Routes (Protected)
Route::middleware('auth:sanctum')->group(function () {
    // Course routes
    Route::get('/courses', [StudentController::class, 'availableCourses']);
    Route::get('/courses/{id}', [StudentController::class, 'courseDetails']);
    Route::post('/courses/enroll', [StudentController::class, 'enrollCourse']);
    Route::get('/my-courses', [StudentController::class, 'myEnrolledCourses']);
    Route::post('/lesson/complete', [StudentController::class, 'completedLesson']);

    // Wishlist routes
    Route::post('/wishlist/add', [StudentController::class, 'addToWishlist']);
    Route::delete('/wishlist/{courseId}', [StudentController::class, 'removeFromWishlist']);
    Route::get('/wishlist', [StudentController::class, 'getWishlist']);

    // Profile routes
    Route::post('/profile/update', [StudentController::class, 'updateProfile']);

    // Quiz routes
    Route::get('/quiz/{id}', [QuizAttemptController::class, 'getQuiz']);
    Route::post('/quiz/attempt/{attemptId}', [QuizAttemptController::class, 'submitQuiz']);
    Route::get('/quiz/attempts/{quizId?}', [QuizAttemptController::class, 'attemptHistory']);
    Route::get('/quiz/attempt/{attemptId}/details', [QuizAttemptController::class, 'attemptDetails']);

    // Forum routes
    Route::get('/forums', [ForumInteractionController::class, 'getForums']);
    Route::get('/forums/{id}', [ForumInteractionController::class, 'getForum']);
    Route::post('/forums/post', [ForumInteractionController::class, 'createPost']);
    Route::post('/forums/comment', [ForumInteractionController::class, 'addComment']);
    Route::post('/forums/post/{postId}/like', [ForumInteractionController::class, 'toggleLike']);
    Route::delete('/forums/post/{postId}', [ForumInteractionController::class, 'deletePost']);
    Route::delete('/forums/comment/{commentId}', [ForumInteractionController::class, 'deleteComment']);
    Route::put('/forums/post/{postId}', [ForumInteractionController::class, 'updatePost']);
    Route::put('/forums/comment/{commentId}', [ForumInteractionController::class, 'updateComment']);

    // Course review routes
    Route::post('/courses/{courseId}/review', [StudentController::class, 'addReview']);
    Route::put('/reviews/{reviewId}', [StudentController::class, 'updateReview']);
    Route::delete('/reviews/{reviewId}', [StudentController::class, 'deleteReview']);
});

// Professor API Routes (Protected)
Route::middleware(['auth:sanctum', 'professor'])->prefix('professor')->group(function () {
    // Course management routes
    Route::get('/courses', [ProfessorCourseController::class, 'index']);
    Route::post('/courses', [ProfessorCourseController::class, 'store']);
    Route::get('/courses/{id}', [ProfessorCourseController::class, 'show']);
    Route::put('/courses/{id}', [ProfessorCourseController::class, 'update']);
    Route::delete('/courses/{id}', [ProfessorCourseController::class, 'destroy']);

    // Module management routes
    Route::post('/courses/{courseId}/modules', [ProfessorCourseController::class, 'addModule']);
    Route::put('/modules/{moduleId}', [ProfessorCourseController::class, 'updateModule']);
    Route::delete('/modules/{moduleId}', [ProfessorCourseController::class, 'deleteModule']);

    // Lesson management routes
    Route::post('/modules/{moduleId}/lessons', [ProfessorCourseController::class, 'addLesson']);
    Route::put('/lessons/{lessonId}', [ProfessorCourseController::class, 'updateLesson']);
    Route::delete('/lessons/{lessonId}', [ProfessorCourseController::class, 'deleteLesson']);

    // Student enrollment management
    Route::get('/courses/{courseId}/students', [ProfessorCourseController::class, 'getEnrolledStudents']);

    // Quiz management routes
    Route::get('/modules/{moduleId}/quizzes', [ProfessorQuizController::class, 'getQuizzesByModule']);
    Route::post('/quizzes', [ProfessorQuizController::class, 'store']);
    Route::get('/quizzes/{id}', [ProfessorQuizController::class, 'show']);
    Route::put('/quizzes/{id}', [ProfessorQuizController::class, 'update']);
    Route::delete('/quizzes/{id}', [ProfessorQuizController::class, 'destroy']);

    // Quiz question management
    Route::post('/quizzes/{quizId}/questions', [ProfessorQuizController::class, 'addQuestion']);
    Route::put('/questions/{questionId}', [ProfessorQuizController::class, 'updateQuestion']);
    Route::delete('/questions/{questionId}', [ProfessorQuizController::class, 'deleteQuestion']);

    // Quiz option management
    Route::post('/questions/{questionId}/options', [ProfessorQuizController::class, 'addOption']);
    Route::put('/options/{optionId}', [ProfessorQuizController::class, 'updateOption']);
    Route::delete('/options/{optionId}', [ProfessorQuizController::class, 'deleteOption']);

    // Quiz attempt monitoring
    Route::get('/quizzes/{quizId}/attempts', [ProfessorQuizController::class, 'getQuizAttempts']);
    Route::get('/attempts/{attemptId}', [ProfessorQuizController::class, 'getAttemptDetails']);
    Route::get('/quizzes/{quizId}/statistics', [ProfessorQuizController::class, 'getQuizStatistics']);

    // Forum management for professors
    Route::get('/forums', [ProfessorForumController::class, 'index']);
    Route::post('/forums', [ProfessorForumController::class, 'store']);
    Route::get('/forums/{id}', [ProfessorForumController::class, 'show']);
    Route::put('/forums/{id}', [ProfessorForumController::class, 'update']);
    Route::delete('/forums/{id}', [ProfessorForumController::class, 'destroy']);

    // Student progress tracking
    Route::get('/courses/{courseId}/students/progress', [ProfessorCourseController::class, 'getCourseStudentsProgress']);
    Route::get('/students/{studentId}/courses/{courseId}/progress', [ProfessorCourseController::class, 'getStudentCourseProgress']);

    // Enrollment management
    Route::put('/enrollments/{enrollId}', [ProfessorCourseController::class, 'updateEnrollment']);
    Route::delete('/enrollments/{enrollId}', [ProfessorCourseController::class, 'removeEnrollment']);
});

// Category management
Route::prefix('admin')->middleware('admin')->group(function () {
    Route::apiResource('categories', CategoryController::class);
});

// Authentication endpoints
Route::post('/register', [ApiAuthController::class, 'register']);
Route::post('/login', [ApiAuthController::class, 'login']);
Route::post('/logout', [ApiAuthController::class, 'logout'])->middleware('auth:sanctum');
