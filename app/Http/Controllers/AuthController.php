<?php

namespace App\Http\Controllers;

use App\Http\Concerns\ApiResponse;
use App\Http\Middleware\AuthenticateFromCookie;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Cookie;

class AuthController extends Controller
{
    use ApiResponse;

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->string('name'),
            'email' => $request->string('email'),
            'password' => Hash::make($request->string('password')),
        ]);

        return $this->success(['user' => $user], 'Registered successfully.', 201)
            ->withCookie($this->tokenCookie($user));
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->string('email'))->first();

        if (! $user || ! Hash::check($request->string('password'), $user->password)) {
            return $this->error('Invalid credentials.', 401);
        }

        return $this->success(['user' => $user], 'Logged in successfully.')
            ->withCookie($this->tokenCookie($user));
    }

    public function me(Request $request): JsonResponse
    {
        return $this->success($request->user(), 'Profile retrieved successfully.');
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return $this->success(null, 'Logged out successfully.')
            ->withoutCookie(AuthenticateFromCookie::COOKIE_NAME);
    }

    private function tokenCookie(User $user): Cookie
    {
        return cookie(
            name: AuthenticateFromCookie::COOKIE_NAME,
            value: $user->createToken('api-token')->plainTextToken,
            minutes: (int) config('sanctum.expiration'),
            path: '/',
            domain: null,
            secure: app()->isProduction(),
            httpOnly: true,
            raw: false,
            sameSite: 'lax',
        );
    }
}
