<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\Admin\ProductController;
use App\Http\Controllers\Api\Admin\CategoryController;
use App\Http\Controllers\Api\Admin\AdminAuthController;
use App\Http\Controllers\Api\Admin\BannerController;

Route::prefix('admin')->group(function () {

    // Đăng nhập
    Route::post('login', [AdminAuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AdminAuthController::class, 'logout']);
    });

    // Quản lý sản phẩm, danh mục, người dùng (admin)
    Route::middleware(['auth:sanctum', 'admin'])->group(function () {
        // Quản lý sản phẩm 
        Route::get('products/trash', [ProductController::class, 'trashed']);
        Route::patch('products/restore/{id}', [ProductController::class, 'restore']);
        Route::delete('products/force-delete/{id}', [ProductController::class, 'forceDelete']);
        Route::apiResource('products', ProductController::class)->except(['index', 'show']);

        // Quản lý danh mục
        Route::prefix('category')->group(function () {
            Route::apiResource('/', CategoryController::class)->parameters(['' => 'id'])->except(['create', 'edit']);
            Route::get('/trash/list', [CategoryController::class, 'trash']);
            Route::post('/restore/{id}', [CategoryController::class, 'restore']);
            Route::delete('/force-delete/{id}', [CategoryController::class, 'forceDelete']);
        });

        // Quản lý tài khoản
        Route::prefix('users')->group(function () {
            Route::get('/', [UserController::class, 'index']);
            Route::post('/', [UserController::class, 'store']);
            Route::get('/{id}', [UserController::class, 'show']);
            Route::patch('/{id}/role', [UserController::class, 'updateRole']);
            Route::patch('/{id}/block', [UserController::class, 'block']);
        });
    });

    // Quản lý sản phẩm (cho cả admin hoặc staff)
    Route::middleware(['auth:sanctum', 'adminorstaff'])->group(function () {
        Route::get('products', [ProductController::class, 'index']);
        Route::get('products/{id}', [ProductController::class, 'show']);
    });

    // Quản lý banner
    Route::prefix('banner')->group(function () {
        Route::apiResource('/', BannerController::class)->parameters(['' => 'id'])->except(['create', 'edit']);
        Route::get('/trash/list', [BannerController::class, 'trash']);
        Route::post('/restore/{id}', [BannerController::class, 'restore']);
        Route::delete('/force-delete/{id}', [BannerController::class, 'forceDelete']);
    });

});
