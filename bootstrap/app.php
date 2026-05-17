<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (ValidationException $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_FAILED',
                    'message' => 'Gonderilen veriler gecersiz.',
                    'fields' => $exception->errors(),
                ],
            ], 422);
        });

        $exceptions->render(function (AuthenticationException $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'AUTH_UNAUTHENTICATED',
                    'message' => 'Bu islem icin giris yapmalisiniz.',
                    'fields' => (object) [],
                ],
            ], 401);
        });

        $exceptions->render(function (\Throwable $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            $statusCode = $exception instanceof HttpExceptionInterface ? $exception->getStatusCode() : 500;
            $message = $statusCode >= 500 ? 'Sunucu tarafinda beklenmeyen bir hata olustu.' : $exception->getMessage();

            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'API_EXCEPTION',
                    'message' => $message,
                    'fields' => (object) [],
                ],
            ], $statusCode);
        });
    })->create();
