<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\VerificationController;
use App\Http\Controllers\Api\ForgotPasswordController;


Route::prefix('auth')->group(function () {
    Route::post('register',   [AuthController::class, 'register']);
    Route::post('login',      [AuthController::class, 'login']);

    
    Route::post('email/resend', [VerificationController::class, 'resend'])
         ->middleware('auth:sanctum')
         ->name('verification.send');

    
    Route::get('email/verify/{id}/{hash}', [VerificationController::class, 'verify'])
         ->middleware(['auth:sanctum','signed']) 
         ->name('verification.verify');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);


    });
    Route::post('forgot-password', [ForgotPasswordController::class, 'sendResetLink']);
    Route::post('reset-password',  [ForgotPasswordController::class, 'resetPassword']);
});


Route::middleware(['auth:sanctum','verified','role:user'])->group(function () {
    // Route::post('/dat-hang',        [OrderController::class,   'store']);
    // Route::post('/binh-luan',       [CommentController::class, 'store']);
    // Route::post('/yeu-thich',       [FavoriteController::class,'store']);
    

});
