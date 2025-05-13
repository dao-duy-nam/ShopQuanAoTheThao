<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Admin\ProductController;

Route::prefix('admin')->group(function () {
    //products 
    Route::get('products/trashed', [ProductController::class, 'trashed']);
    Route::patch('products/restore/{id}', [ProductController::class, 'restore']);
    Route::delete('products/force-delete/{id}', [ProductController::class, 'forceDelete']);
    Route::apiResource('products', ProductController::class);

});



