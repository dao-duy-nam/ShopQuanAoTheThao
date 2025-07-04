<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\Admin\OrderController;
use App\Http\Controllers\Api\Admin\BannerController;
use App\Http\Controllers\Api\Admin\DanhGiaController;
use App\Http\Controllers\Api\Admin\ProductController;
use App\Http\Controllers\Api\Admin\VariantController;
use App\Http\Controllers\Api\Admin\CategoryController;
use App\Http\Controllers\Api\Admin\AttributeValueController;
use App\Http\Controllers\Api\Admin\AdminAuthController;
use App\Http\Controllers\Api\Admin\AttributeController;
use App\Http\Controllers\Api\Admin\DiscountCodeController;

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
            Route::get('/ad', [UserController::class, 'listAdmins']);
            Route::get('/cus', [UserController::class, 'listCustomers']);
            Route::post('/', [UserController::class, 'store']);
            Route::get('/{id}', [UserController::class, 'show']);
            Route::patch('/{id}/role', [UserController::class, 'updateRole']);
            Route::patch('/{id}/block', [UserController::class, 'block']);
            Route::post('/{id}/unblock', [UserController::class, 'unblock']);
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
        Route::get('/list/{productId}', [VariantController::class, 'getByProductId']);
        Route::get('/deleted/{productId}', [VariantController::class, 'getDeletedByProductId']);
        Route::post('/{productId}', [VariantController::class, 'store']);
        Route::post('update/{id}', [VariantController::class, 'update']);
        Route::delete('/{id}', [VariantController::class, 'destroy']);
        Route::delete('/product/{productId}', [VariantController::class, 'deleteByProductId']);
        Route::patch('/restore/{id}', [VariantController::class, 'restore']);
        Route::patch('/restore/product/{productId}', [VariantController::class, 'restoreByProductId']);
        Route::delete('/force-delete/{id}', [VariantController::class, 'forceDelete']);
        Route::delete('/force-delete/product/{productId}', [VariantController::class, 'forceDeleteByProductId']);
    });

    Route::prefix('attributes')->group(function () {
        Route::get('/', [AttributeController::class, 'index']);
        Route::get('/deleted', [AttributeController::class, 'trashed']);
        Route::get('/{id}', [AttributeController::class, 'show']);
        Route::post('/', [AttributeController::class, 'store']);
        Route::post('/{id}', [AttributeController::class, 'update']);
        Route::delete('/{id}', [AttributeController::class, 'destroy']);
        Route::patch('/restore/{id}', [AttributeController::class, 'restore']);
        Route::delete('/force-delete/{id}', [AttributeController::class, 'forceDelete']);
    });
    Route::prefix('attribute-values')->group(function () {
        Route::get('/attribute/{attributeId}', [AttributeValueController::class, 'getByAttributeId']);
        Route::get('/', [AttributeValueController::class, 'index']);
        Route::post('/attribute/{attributeId}', [AttributeValueController::class, 'store']);
        Route::get('/{id}', [AttributeValueController::class, 'show']);
        Route::put('/{id}', [AttributeValueController::class, 'update']);
        Route::delete('/{id}', [AttributeValueController::class, 'destroy']);
        Route::get('/trash/list', [AttributeValueController::class, 'trash']);
        Route::post('/restore/{id}', [AttributeValueController::class, 'restore']);
    });
    Route::prefix('orders')->group(function () {
        Route::get('/', [OrderController::class, 'index']);
        Route::get('/{id}', [OrderController::class, 'show']);
        // Route::post('/', [OrderController::class, 'store']);
        Route::put('/{id}', [OrderController::class, 'update']);
        // Route::delete('/{id}', [OrderController::class, 'destroy']);
    });
    Route::prefix('discount-codes')->group(function () {
        Route::get('/', [DiscountCodeController::class, 'index']);
        Route::post('/', [DiscountCodeController::class, 'store']);
        Route::get('/{id}', [DiscountCodeController::class, 'show']);
        Route::put('/{id}', [DiscountCodeController::class, 'update']);
        Route::patch('/{id}/status', [DiscountCodeController::class, 'changeStatus']);
        Route::delete('/{id}', [DiscountCodeController::class, 'destroy']);
        Route::get('/trash/list', [DiscountCodeController::class, 'trash']);
        Route::post('/restore/{id}', [DiscountCodeController::class, 'restore']);
        Route::post('/{id}/send', [DiscountCodeController::class, 'sendToUsers']);
    });
});
