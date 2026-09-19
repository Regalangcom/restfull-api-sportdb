<?php

use App\Exceptions\TheSportsDbException;
use App\Http\Middleware\AuthenticateFromCookie;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(prepend: [
            AuthenticateFromCookie::class,
        ]);

        $middleware->prependToPriorityList(
            before: AuthenticatesRequests::class,
            prepend: AuthenticateFromCookie::class,
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(function (ValidationException $e, Request $request) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => $e->errors(),
            ], 422);
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
                'data' => null,
            ], 401);
        });

        $exceptions->render(function (ModelNotFoundException $e, Request $request) {
            return response()->json([
                'success' => false,
                'message' => 'Resource not found.',
                'data' => null,
            ], 404);
        });

        $exceptions->render(function (TheSportsDbException $e, Request $request) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null,
            ], 502);
        });
    })->create();
