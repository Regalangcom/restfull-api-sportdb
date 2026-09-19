<?php

use App\Models\User;

it('registers a new user and sets the token as an HttpOnly cookie', function () {
    $response = $this->postJson('/api/register', [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertCreated()
        ->assertJson(['success' => true])
        ->assertJsonStructure(['data' => ['user']])
        ->assertJsonMissingPath('data.token')
        ->assertCookie('api_token', encrypted: false);

    expect($response->getCookie('api_token', false)->isHttpOnly())->toBeTrue();

    $this->assertDatabaseHas('users', ['email' => 'jane@example.com']);
});

it('rejects registration with an already used email', function () {
    User::factory()->create(['email' => 'jane@example.com']);

    $response = $this->postJson('/api/register', [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(422)->assertJson(['success' => false]);
});

it('logs in with valid credentials', function () {
    $user = User::factory()->create(['password' => bcrypt('password123')]);

    $response = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password123',
    ]);

    $response->assertOk()
        ->assertJson(['success' => true])
        ->assertJsonStructure(['data' => ['user']])
        ->assertJsonMissingPath('data.token')
        ->assertCookie('api_token', encrypted: false);

    expect($response->getCookie('api_token', false)->isHttpOnly())->toBeTrue();
});

it('authenticates protected requests using the cookie', function () {
    $user = User::factory()->create();
    $token = $user->createToken('api-token')->plainTextToken;

    $this->withCredentials()
        ->withUnencryptedCookie('api_token', $token)
        ->getJson('/api/me')
        ->assertOk()
        ->assertJsonPath('data.email', $user->email);
});

it('rejects state-changing cookie requests without the X-Requested-With header', function () {
    $user = User::factory()->create();
    $token = $user->createToken('api-token')->plainTextToken;

    $this->withCredentials()
        ->withUnencryptedCookie('api_token', $token)
        ->postJson('/api/logout')
        ->assertForbidden()
        ->assertJson(['success' => false]);

    expect($user->tokens()->count())->toBe(1);
});

it('logs out via cookie, revoking the token and expiring the cookie', function () {
    $user = User::factory()->create();
    $token = $user->createToken('api-token')->plainTextToken;

    $response = $this->withCredentials()
        ->withUnencryptedCookie('api_token', $token)
        ->withHeader('X-Requested-With', 'XMLHttpRequest')
        ->postJson('/api/logout');

    $response->assertOk()->assertCookieExpired('api_token');

    expect($user->tokens()->count())->toBe(0);
});

it('falls back to the cookie when the Bearer header is empty', function () {
    $user = User::factory()->create();
    $token = $user->createToken('api-token')->plainTextToken;

    $this->withCredentials()
        ->withUnencryptedCookie('api_token', $token)
        ->withHeader('Authorization', 'Bearer ')
        ->getJson('/api/me')
        ->assertOk();
});

it('rejects an invalid cookie token', function () {
    $this->withCredentials()
        ->withUnencryptedCookie('api_token', '1|not-a-real-token')
        ->getJson('/api/me')
        ->assertStatus(401);
});

it('rejects login with invalid credentials', function () {
    $user = User::factory()->create(['password' => bcrypt('password123')]);

    $response = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $response->assertStatus(401)->assertJson(['success' => false]);
});

it('logs out the authenticated user', function () {
    $user = User::factory()->create();
    $token = $user->createToken('api-token')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/logout');

    $response->assertOk()->assertJson(['success' => true]);
});

it('returns the authenticated user profile', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'sanctum')->getJson('/api/me')
        ->assertOk()
        ->assertJsonPath('data.email', $user->email)
        ->assertJsonMissingPath('data.password');
});

it('rejects profile access without authentication', function () {
    $this->getJson('/api/me')->assertStatus(401)->assertJson(['success' => false]);
});

it('rejects logout without authentication', function () {
    $response = $this->postJson('/api/logout');

    $response->assertStatus(401)->assertJson(['success' => false]);
});
