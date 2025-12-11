<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ChatbotController;

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});


Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/services', [ServiceController::class, 'index'])->middleware('permission:view services');
    Route::get('/services/{id}', [ServiceController::class, 'show']);
    Route::post('/services', [ServiceController::class, 'store'])->middleware('permission:create services');
    Route::put('/services/update/{id}', [ServiceController::class, 'update'])->middleware('permission:update services');
    Route::delete('/services/{id}', [ServiceController::class, 'destroy'])->middleware('permission:delete services');
    
});


Route::get('/agents', [ServiceController::class, 'listActiveServices']);
Route::get('/agents/{slug}', [ServiceController::class, 'agentBySlug']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/chatbot', [ChatbotController::class, 'index']);
    Route::post('/chatbot', [ChatbotController::class, 'store']);
    Route::get('/chatbot/{id}', [ChatbotController::class, 'show']);
    Route::get('/my-agents', [ChatbotController::class, 'myAgents']);
});