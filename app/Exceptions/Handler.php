<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     */
    public function register(): void
    {
        // Optional: Add custom reporting logic here if needed.
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $exception): JsonResponse|\Symfony\Component\HttpFoundation\Response
    {
        if ($request->expectsJson() || $request->is('api/*') || $request->wantsJson()) {

            // Handle validation exceptions separately
            if ($exception instanceof ValidationException) {
                return response()->json([
                    'message' => 'Validation failed.',
                    'errors' => $exception->errors(),
                ], 422);
            }

            // Handle HTTP exceptions (404, 403, etc.)
            if ($exception instanceof HttpExceptionInterface) {
                return response()->json([
                    'message' => $exception->getMessage() ?: 'HTTP Error',
                    'status' => $exception->getStatusCode(),
                ], $exception->getStatusCode());
            }

            // Default fallback for other exceptions
            return response()->json([
                    'success' => false,
                    'message' => $exception->getMessage(),
                ], 500);
        }

        // For web routes (non-API), fallback to parent
        return parent::render($request, $exception);
    }

            // Handle HTTP exceptions (404, 403, etc.)
            if ($exception instanceof HttpExceptionInterface) {
                return response()->json([
                    'message' => $exception->getMessage() ?: 'HTTP Error',
                    'status' => $exception->getStatusCode(),
                ], $exception->getStatusCode());
            }

            // Default fallback for other exceptions
            return response()->json([
                    'success' => false,
                    'message' => $exception->getMessage(),
                ], 500);
            // force opcache reload 2026-06-23 pantries fix
        }

        // For web routes (non-API), fallback to parent
        return parent::render($request, $exception);
    }
}
