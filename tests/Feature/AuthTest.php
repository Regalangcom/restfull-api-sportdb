<?php

use App\Models\User;

it('registers a new user and returns a token', function () {
    $response = $this->postJson('/api/register', [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertCreated()
        ->assertJson(['success' => true])
        ->assertJsonStructure(['data' => ['user', 'token']]);

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
        ->assertJsonStructure(['data' => ['user', 'token']]);
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
