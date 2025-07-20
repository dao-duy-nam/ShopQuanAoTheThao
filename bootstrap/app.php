<?php

        use Illuminate\Foundation\Application;
        use Illuminate\Foundation\Configuration\Exceptions;
        use Illuminate\Foundation\Configuration\Middleware;

        return Application::configure(basePath: dirname(__DIR__))
            ->withRouting(
                web: __DIR__ . '/../routes/web.php',
                api: [
                    __DIR__ . '/../routes/apiAdmin.php',
                    __DIR__ . '/../routes/apiUser.php',
                ],
                commands: __DIR__ . '/../routes/console.php',
                health: '/up',
            )
            ->withMiddleware(function (Middleware $middleware) {
                $middleware->alias([
                    'admin' => \App\Http\Middleware\AdminMiddleware::class,
                    'staff' => \App\Http\Middleware\StaffMiddleware::class,
                    'user' => \App\Http\Middleware\UserMiddleware::class,
                    'adminorstaff' => \App\Http\Middleware\AdminOrStaffMiddleware::class,
                ]);
            })
            ->withExceptions(function (Exceptions $exceptions) {
                //
            })->create();

            