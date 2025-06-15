<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Client\AuthController;
use App\Http\Controllers\API\Client\ReviewController;
use App\Http\Controllers\Api\Client\ProductController;
use App\Http\Controllers\Api\Client\ClientAccountController;
use App\Http\Controllers\Api\Client\ForgotPasswordController;


Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/send-otp', [AuthController::class, 'sendOtp']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('forgot-password', [ForgotPasswordController::class, 'forgotPassword']);
    Route::post('resend-otp', [ForgotPasswordController::class, 'resendOtp']);
    Route::post('verify-otp-password', [ForgotPasswordController::class, 'verifyForgotOtp']);
    Route::post('reset-password',  [ForgotPasswordController::class, 'resetPassword']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
    });
});

Route::prefix('products')->group(function () {
    Route::get('/', [ProductController::class, 'index']);
    Route::get('{id}', [ProductController::class, 'show']);
});

Route::middleware(['auth:sanctum', 'user'])->group(function () {
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::put('/reviews/{id}', [ReviewController::class, 'update']);
    Route::delete('/reviews/{id}', [ReviewController::class, 'destroy']);
});
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/client/profile', [ClientAccountController::class, 'profile']);
    Route::put('/client/profile', [ClientAccountController::class, 'updateProfile']);
    Route::put('/client/change-password', [ClientAccountController::class, 'changePassword']);
    Route::post('/client/logout', [ClientAccountController::class, 'logout']);
});
