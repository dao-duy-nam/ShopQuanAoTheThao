<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Admin\ProductController;
use App\Http\Controllers\Api\Admin\CategoryController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\Admin\BannerController;

Route::prefix('admin')->group(function () {
    //products 
    Route::get('products/trashed', [ProductController::class, 'trashed']);
    Route::patch('products/restore/{id}', [ProductController::class, 'restore']);
    Route::delete('products/force-delete/{id}', [ProductController::class, 'forceDelete']);
    Route::apiResource('products', ProductController::class);
    //categories
    Route::prefix('category')->group(function () {
        Route::apiResource('/', CategoryController::class)->parameters(['' => 'id'])->except(['create', 'edit']);
        // Routes cho soft delete
        Route::get('/trash/list', [CategoryController::class, 'trash']);
        Route::post('/restore/{id}', [CategoryController::class, 'restore']);
        Route::delete('/force-delete/{id}', [CategoryController::class, 'forceDelete']);
    });

    //quanlytaikhoan
    Route::patch('users/{id}/status', [UserController::class, 'updateStatus']);
    Route::apiResource('users', UserController::class)->only([
        'index',
        'store',
        'show'
    ]); // Các route chuẩn: index, store, show, update, destroy

    // Quản lý banner
Route::prefix('banner')->group(function () {
    Route::apiResource('/', BannerController::class)->parameters(['' => 'id'])->except(['create', 'edit']);

    Route::get('/trash/list', [BannerController::class, 'trash']);
    Route::post('/restore/{id}', [BannerController::class, 'restore']);
    Route::delete('/force-delete/{id}', [BannerController::class, 'forceDelete']);
});
});
