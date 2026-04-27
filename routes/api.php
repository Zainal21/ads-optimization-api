<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CampaignAnalysisController;
use App\Http\Controllers\Api\CampaignManagementController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Campaign routes
    Route::post('/campaigns/bulk-upload', [CampaignManagementController::class, 'bulkUpload']);
    Route::get('/campaigns/bulk-upload/template', [CampaignManagementController::class, 'bulkUploadTemplate']);
    Route::get('/campaigns/summary/{campaign}', [CampaignManagementController::class, 'summary']);
    Route::apiResource('campaigns', CampaignManagementController::class);

    // Analysis routes
    Route::post('/analyze', [CampaignAnalysisController::class, 'analyze']);
    Route::get('/analyses', [CampaignAnalysisController::class, 'index']);
    Route::get('/analyses/{id}', [CampaignAnalysisController::class, 'show']);
    Route::get('/analyses/{id}/export-pdf', [CampaignAnalysisController::class, 'exportPDF']);
    Route::get('/analyses/comparison', [CampaignAnalysisController::class, 'compare']);
});
