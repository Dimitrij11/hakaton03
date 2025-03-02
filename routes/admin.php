<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CourseAdminController;
use App\Http\Controllers\ModuleAdminController;
use App\Http\Controllers\LessonAdminController;
use App\Http\Controllers\CategoryAdminController;
use App\Http\Controllers\QuizAdminController;
use App\Http\Controllers\ForumAdminController;
use App\Http\Controllers\ReviewAdminController;

// All routes in this file will be prefixed with 'api/admin'
// and will have the 'auth' and 'admin' middleware applied

// Admin Dashboard
Route::get('/dashboard', [AdminController::class, 'dashboard']);

// Users Management
Route::get('/users', [AdminController::class, 'getUsers']);
Route::get('/users/{id}', [AdminController::class, 'getUserById']);
Route::put('/users/{id}', [AdminController::class, 'updateUser']);
Route::delete('/users/{id}', [AdminController::class, 'deleteUser']);

// Category Management
Route::get('/categories', [CategoryAdminController::class, 'index']);
Route::get('/categories/{id}', [CategoryAdminController::class, 'show']);
Route::post('/categories', [CategoryAdminController::class, 'store']);
Route::put('/categories/{id}', [CategoryAdminController::class, 'update']);
Route::delete('/categories/{id}', [CategoryAdminController::class, 'destroy']);

// Course Management
Route::get('/courses', [CourseAdminController::class, 'index']);
Route::get('/courses/{id}', [CourseAdminController::class, 'show']);
Route::post('/courses', [CourseAdminController::class, 'store']);
Route::put('/courses/{id}', [CourseAdminController::class, 'update']);
Route::delete('/courses/{id}', [CourseAdminController::class, 'destroy']);
Route::get('/available-professors', [CourseAdminController::class, 'getProfessors']);

// Module Management
Route::get('/modules', [ModuleAdminController::class, 'index']);
Route::get('/modules/{id}', [ModuleAdminController::class, 'show']);
Route::post('/modules', [ModuleAdminController::class, 'store']);
Route::put('/modules/{id}', [ModuleAdminController::class, 'update']);
Route::delete('/modules/{id}', [ModuleAdminController::class, 'destroy']);
Route::post('/modules/reorder', [ModuleAdminController::class, 'reorder']);

// Lesson Management
Route::get('/lessons', [LessonAdminController::class, 'index']);
Route::get('/lessons/{id}', [LessonAdminController::class, 'show']);
Route::post('/lessons', [LessonAdminController::class, 'store']);
Route::put('/lessons/{id}', [LessonAdminController::class, 'update']);
Route::delete('/lessons/{id}', [LessonAdminController::class, 'destroy']);
Route::post('/lessons/reorder', [LessonAdminController::class, 'reorder']);

// Quiz Management
Route::get('/quizzes', [QuizAdminController::class, 'indexQuizzes']);
Route::get('/quizzes/{id}', [QuizAdminController::class, 'showQuiz']);
Route::post('/quizzes', [QuizAdminController::class, 'storeQuiz']);
Route::put('/quizzes/{id}', [QuizAdminController::class, 'updateQuiz']);
Route::delete('/quizzes/{id}', [QuizAdminController::class, 'destroyQuiz']);

// Question Management
Route::get('/questions', [QuizAdminController::class, 'indexQuestions']);
Route::get('/questions/{id}', [QuizAdminController::class, 'showQuestion']);
Route::post('/questions', [QuizAdminController::class, 'storeQuestion']);
Route::put('/questions/{id}', [QuizAdminController::class, 'updateQuestion']);
Route::delete('/questions/{id}', [QuizAdminController::class, 'destroyQuestion']);

// Answer Management
Route::get('/answers', [QuizAdminController::class, 'indexAnswers']);
Route::get('/answers/{id}', [QuizAdminController::class, 'showAnswer']);
Route::post('/answers', [QuizAdminController::class, 'storeAnswer']);
Route::put('/answers/{id}', [QuizAdminController::class, 'updateAnswer']);
Route::delete('/answers/{id}', [QuizAdminController::class, 'destroyAnswer']);

// User Responses
Route::get('/user-responses', [QuizAdminController::class, 'getUserResponses']);

// Forum Management
Route::get('/forum/threads', [ForumAdminController::class, 'indexThreads']);
Route::get('/forum/threads/{id}', [ForumAdminController::class, 'showThread']);
Route::post('/forum/threads', [ForumAdminController::class, 'storeThread']);
Route::put('/forum/threads/{id}', [ForumAdminController::class, 'updateThread']);
Route::delete('/forum/threads/{id}', [ForumAdminController::class, 'destroyThread']);
Route::post('/forum/threads/{id}/pin', [ForumAdminController::class, 'pinThread']);
Route::post('/forum/threads/{id}/unpin', [ForumAdminController::class, 'unpinThread']);
Route::post('/forum/threads/{id}/lock', [ForumAdminController::class, 'lockThread']);
Route::post('/forum/threads/{id}/unlock', [ForumAdminController::class, 'unlockThread']);

Route::get('/forum/comments', [ForumAdminController::class, 'indexComments']);
Route::get('/forum/comments/{id}', [ForumAdminController::class, 'showComment']);
Route::post('/forum/comments', [ForumAdminController::class, 'storeComment']);
Route::put('/forum/comments/{id}', [ForumAdminController::class, 'updateComment']);
Route::delete('/forum/comments/{id}', [ForumAdminController::class, 'destroyComment']);

// Review Management
Route::get('/reviews', [ReviewAdminController::class, 'index']);
Route::get('/reviews/{id}', [ReviewAdminController::class, 'show']);
Route::post('/reviews', [ReviewAdminController::class, 'store']);
Route::put('/reviews/{id}', [ReviewAdminController::class, 'update']);
Route::delete('/reviews/{id}', [ReviewAdminController::class, 'destroy']);
Route::post('/reviews/{id}/approve', [ReviewAdminController::class, 'approve']);
Route::post('/reviews/{id}/reject', [ReviewAdminController::class, 'reject']);
