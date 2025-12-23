<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\StatisticsChatbotController;


Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});

//admin services
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/services', [ServiceController::class, 'index'])->middleware('permission:view services');
    Route::get('/services/{id}', [ServiceController::class, 'show']);
    Route::post('/services', [ServiceController::class, 'store'])->middleware('permission:create services');
    Route::put('/services/update/{id}', [ServiceController::class, 'update'])->middleware('permission:update services');
    Route::delete('/services/{id}', [ServiceController::class, 'destroy'])->middleware('permission:delete services');
    
});


Route::get('/agents', [ServiceController::class, 'listActiveServices']);
Route::get('/agents/{slug}', [ServiceController::class, 'agentBySlug']);

//user chatbot
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/chatbot', [ChatbotController::class, 'index']);
    Route::post('/chatbot', [ChatbotController::class, 'store']);
    Route::get('/chatbot/{id}', [ChatbotController::class, 'show']);
    Route::post('/chatbot/update/{public_key}',[ChatbotController::class, 'update'] );
    Route::get('/my-agents', [ChatbotController::class, 'myAgents']);
    Route::post('/my-agents/{public_key}', [ChatbotController::class, 'delete']);
    Route::post('/my-agents/toggle-status/{public_key}', [ChatbotController::class, 'toggleStatus']);
    Route::get('/my-agents/{public_key}',[ChatbotController::class, 'show']);
});

//user statistics
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/statistics', [StatisticsChatbotController::class, 'dashboardStats']);
    Route::get('/statistics/{public_key}/seven-days', [StatisticsChatbotController::class, 'getSevenDaysStats']);
    Route::get('/statistics/{public_key}/basic-stats', [StatisticsChatbotController::class, 'basicStats']);
    Route::get('/statistics/{public_key}', [StatisticsChatbotController::class, 'chatbotStats']);
});

Route::middleware('auth:sanctum')->get('/auth/check', function () {
    return response()->json([
        'authenticated' => true,
        'user' => auth()->user()
    ]);
});
