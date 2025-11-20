<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php', // [QUAN TRỌNG] Dòng này để kích hoạt route API
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        // Laravel 11 tự động thêm prefix '/api' cho các route trong file này
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Cấu hình middleware cho API nếu cần (ví dụ: throttle, sanctum, etc.)
        $middleware->api(prepend: [
            // \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);

        // Alias middleware nếu muốn dùng tên ngắn gọn trong route
        $middleware->alias([
            // 'auth' => \App\Http\Middleware\Authenticate::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Xử lý custom exception trả về JSON cho API
        $exceptions->shouldRenderJsonWhen(function (\Illuminate\Http\Request $request, Throwable $e) {
            if ($request->is('api/*')) {
                return true;
            }
            return $request->expectsJson();
        });
    })->create();