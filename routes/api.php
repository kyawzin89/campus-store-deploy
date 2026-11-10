<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\NoteController;
use Illuminate\Support\Facades\Route;

/*
| All routes here are prefixed with /api and return JSON.
| Run `php artisan route:list --path=api` to see them all.
*/

// Public, rate limited: at most 6 attempts per minute per IP (a security control)
Route::middleware('throttle:6,1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Requires "Authorization: Bearer <token>"
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Example resource. Add your own resources below it.
    Route::apiResource('notes', NoteController::class);
});
