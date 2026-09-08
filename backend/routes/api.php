<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ExportController;
use App\Http\Controllers\Api\PublicSurveyController;
use App\Http\Controllers\Api\QuestionController;
use App\Http\Controllers\Api\SurveyController;
use Illuminate\Support\Facades\Route;

// ── Auth ──────────────────────────────────────────────────────────────
Route::post('/login', [AuthController::class, 'login']);

// ── Public (no auth) — what respondents hit after scanning the QR ──────
Route::get('/public/surveys/{slug}', [PublicSurveyController::class, 'show']);
Route::post('/public/surveys/{slug}/responses', [PublicSurveyController::class, 'storeResponse']);

// ── Admin (Sanctum auth required) ───────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::apiResource('surveys', SurveyController::class);
    Route::get('/surveys/{survey}/qrcode', [SurveyController::class, 'qrCode']);

    Route::post('/surveys/{survey}/questions', [QuestionController::class, 'store']);
    Route::patch('/surveys/{survey}/questions/reorder', [QuestionController::class, 'reorder']);
    Route::put('/questions/{question}', [QuestionController::class, 'update']);
    Route::delete('/questions/{question}', [QuestionController::class, 'destroy']);

    Route::get('/surveys/{survey}/export/excel', [ExportController::class, 'excel']);
    Route::get('/surveys/{survey}/export/image', [ExportController::class, 'image']);
});
