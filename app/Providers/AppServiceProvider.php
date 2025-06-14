<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\UserMiddleware;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\StaffMiddleware;
use App\Http\Middleware\AdminOrStaffMiddleware;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    

public function boot(): void
{
    
    Route::aliasMiddleware('admin', AdminMiddleware::class);
    Route::aliasMiddleware('staff', StaffMiddleware::class);
    Route::aliasMiddleware('user', UserMiddleware::class);
    Route::aliasMiddleware('adminorstaff', AdminOrStaffMiddleware::class);

    parent::boot();

    $this->routes(function () {
        Route::middleware('api')->group(base_path('routes/apiUser.php'));
        Route::middleware('api')->group(base_path('routes/apiAdmin.php'));
        Route::middleware('web')->group(base_path('routes/web.php'));
    });
}

}
