<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Admin\VariantController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\Admin\BannerController;
use App\Http\Controllers\Api\Admin\DanhGiaController;
use App\Http\Controllers\Api\Admin\ProductController;
use App\Http\Controllers\Api\Admin\CategoryController;
use App\Http\Controllers\Api\Admin\AdminAuthController;

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
        Route::post('products/{id}', [ProductController::class, 'update'])->name('products.update');
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

    //quản lý đánh giá
    Route::prefix('reviews')->group(function () {
        Route::get('/', [DanhGiaController::class, 'index']);
        Route::get('/{id}', [DanhGiaController::class, 'show']);
        Route::patch('/{id}/toggle-visibility', [DanhGiaController::class, 'toggleVisibility']);
    });
    // Quản lý biến thể sản phẩm
    Route::prefix('variants')->group(function () {
        Route::get('/{id}', [VariantController::class, 'show']);
        Route::post('/{productId}', [VariantController::class, 'store']);
        Route::post('update/{id}', [VariantController::class, 'update']);
        Route::delete('/{id}', [VariantController::class, 'destroy']);
        Route::delete('/product/{productId}', [VariantController::class, 'deleteByProductId']);
        Route::patch('/restore/{id}', [VariantController::class, 'restore']);
        Route::patch('/restore/product/{productId}', [VariantController::class, 'restoreByProductId']);
        Route::delete('/force-delete/{id}', [VariantController::class, 'forceDelete']);
        Route::delete('/force-delete/product/{productId}', [VariantController::class, 'forceDeleteByProductId']);
    });
});
