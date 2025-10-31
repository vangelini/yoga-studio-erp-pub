<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\CoursesController;
use App\Http\Controllers\TeachersController;
use App\Http\Controllers\BookingsController;
use App\Http\Controllers\SubscriptionsController;
use App\Http\Controllers\GeminiController;

Route::post('/auth/login', [AuthController::class, 'login']);

Route::post('/dashboard-data', [DashboardController::class, 'show']);

Route::put('/users/{user}', [UsersController::class, 'updateRoleAndStatus']);
Route::put('/users/{user}/password', [UsersController::class, 'resetPassword']);
Route::post('/users', [UsersController::class, 'store']);

Route::post('/courses', [CoursesController::class, 'store']);
Route::put('/courses/{course}', [CoursesController::class, 'update']);

Route::put('/teachers/{teacher}', [TeachersController::class, 'updateProfile']);
Route::put('/teachers/{teacher}/availability', [TeachersController::class, 'updateAvailability']);

Route::post('/bookings', [BookingsController::class, 'store']);
Route::delete('/bookings/{booking}', [BookingsController::class, 'destroy']);

Route::post('/subscriptions', [SubscriptionsController::class, 'store']);
Route::put('/subscriptions/{subscription}/toggle-renew', [SubscriptionsController::class, 'toggleRenewal']);

Route::post('/gemini/generate-course-description', [GeminiController::class, 'generateCourseDescription']);
Route::get('/gemini/pose-of-the-day', [GeminiController::class, 'poseOfTheDay']);
