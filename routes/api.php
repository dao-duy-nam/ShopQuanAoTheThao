<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Admin\ProductController;
use App\Http\Controllers\Api\Admin\CategoryController;

Route::prefix('admin')->group(function () {
    //products 
    Route::get('products/trashed', [ProductController::class, 'trashed']);
    Route::patch('products/restore/{id}', [ProductController::class, 'restore']);
    Route::delete('products/force-delete/{id}', [ProductController::class, 'forceDelete']);
    Route::apiResource('products', ProductController::class);
    //categories
    Route::prefix('category')->group(function () {
        Route::get('/', [CategoryController::class, 'index']);
        Route::post('/', [CategoryController::class, 'store']);
        Route::get('/{id}', [CategoryController::class, 'show']);
        Route::put('/{id}', [CategoryController::class, 'update']);
        Route::delete('/{id}', [CategoryController::class, 'destroy']);

        // Soft delete routes
        Route::get('/trash/list', [CategoryController::class, 'trash']);
        Route::post('/restore/{id}', [CategoryController::class, 'restore']);
        Route::delete('/force-delete/{id}', [CategoryController::class, 'forceDelete']);
    });
});



