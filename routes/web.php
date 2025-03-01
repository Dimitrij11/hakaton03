<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\StudentDataController;

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

