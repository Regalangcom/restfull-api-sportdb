<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateFromCookie
{
    public const COOKIE_NAME = 'api_token';

    public function handle(Request $request, Closure $next): Response
    {
        if (filled($request->bearerToken())) {
            return $next($request);
        }

        $token = $request->cookie(self::COOKIE_NAME);

        if (! is_string($token) || $token === '') {
            return $next($request);
        }

        // Cookies are sent automatically by browsers, so state-changing
        // requests must prove they come from our own frontend (a custom
        // header cannot be set cross-site without a CORS preflight).
        if (! $request->isMethodSafe() && ! $request->hasHeader('X-Requested-With')) {
            return response()->json([
                'success' => false,
                'message' => 'Missing X-Requested-With header.',
                'data' => null,
            ], 403);
        }

        $request->headers->set('Authorization', 'Bearer '.$token);

        return $next($request);
    }
}
