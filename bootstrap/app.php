<?php

use App\Exceptions\ApiExceptions;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (Exception $e, Request $request): ?JsonResponse {
            $className = get_class($e);
            $handlers = ApiExceptions::$handlers;
    
            if (array_key_exists($className, $handlers)) {
                $method = $handlers[$className];
                return ApiExceptions::$method($e, $request);
            }
    
            return response()->json([
                'status' => 500,
                'type' => class_basename($e),
                'error' => $e->getMessage(),
            ], 500);
        });

        
    })->create();
