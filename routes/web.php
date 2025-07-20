<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Payment\ZaloPayController;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/dat-hang', [ZaloPayController::class, 'redirectView']);
