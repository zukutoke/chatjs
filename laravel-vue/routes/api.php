<?php

use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\FileController;
use App\Http\Controllers\Api\ModelController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\VoteController;
use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Auth routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    // OAuth routes
    Route::get('/{provider}/redirect', [AuthController::class, 'redirectToProvider']);
    Route::get('/{provider}/callback', [AuthController::class, 'handleProviderCallback']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'user']);
    });
});

// Public routes
Route::get('/models', [ModelController::class, 'index']);

// Public chat viewing (for shared chats)
Route::get('/chats/{id}', [ChatController::class, 'show']);
Route::get('/chats/{id}/messages', [ChatController::class, 'messages']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Chats
    Route::get('/chats', [ChatController::class, 'index']);
    Route::post('/chats', [ChatController::class, 'store']);
    Route::patch('/chats/{id}', [ChatController::class, 'update']);
    Route::delete('/chats/{id}', [ChatController::class, 'destroy']);
    Route::post('/chats/{id}/messages', [ChatController::class, 'sendMessage']);
    Route::post('/chats/{chatId}/messages/{messageId}/stop', [ChatController::class, 'stopStream']);
    Route::delete('/chats/{chatId}/messages/{messageId}/trailing', [ChatController::class, 'deleteTrailingMessages']);

    // Projects
    Route::apiResource('projects', ProjectController::class);
    Route::get('/projects/{id}/chats', [ProjectController::class, 'chats']);

    // Models
    Route::patch('/models/{modelId}/preference', [ModelController::class, 'updatePreference']);

    // Votes
    Route::get('/chats/{chatId}/votes', [VoteController::class, 'index']);
    Route::post('/chats/{chatId}/messages/{messageId}/vote', [VoteController::class, 'vote']);
    Route::delete('/chats/{chatId}/messages/{messageId}/vote', [VoteController::class, 'removeVote']);

    // Files
    Route::post('/files/upload', [FileController::class, 'upload']);
    Route::get('/files/{id}', [FileController::class, 'show'])->name('api.files.show');
    Route::delete('/files/{id}', [FileController::class, 'destroy']);

    // Documents
    Route::apiResource('documents', DocumentController::class);
});
