<?php

use App\Models\FavoriteTeam;
use App\Models\User;

it('requires authentication to list favorites', function () {
    $this->getJson('/api/favorites')->assertStatus(401);
});

it('lists only the authenticated users favorites', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    FavoriteTeam::factory()->for($user)->create(['team_id' => '133604', 'team_name' => 'Arsenal']);
    FavoriteTeam::factory()->for($otherUser)->create(['team_id' => '133602', 'team_name' => 'Chelsea']);

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/favorites');

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.team_name', 'Arsenal');
});

it('adds a team to favorites', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/favorites', [
        'team_id' => '133604',
        'team_name' => 'Arsenal',
    ]);

    $response->assertCreated()->assertJson(['success' => true]);

    $this->assertDatabaseHas('favorite_teams', [
        'user_id' => $user->id,
        'team_id' => '133604',
    ]);
});

it('prevents duplicate favorites for the same user and team', function () {
    $user = User::factory()->create();
    FavoriteTeam::factory()->for($user)->create(['team_id' => '133604']);

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/favorites', [
        'team_id' => '133604',
        'team_name' => 'Arsenal',
    ]);

    $response->assertStatus(409)->assertJson(['success' => false]);

    expect(FavoriteTeam::where('user_id', $user->id)->where('team_id', '133604')->count())->toBe(1);
});

it('deletes only the authenticated users own favorite', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $favorite = FavoriteTeam::factory()->for($otherUser)->create();

    $response = $this->actingAs($user, 'sanctum')->deleteJson("/api/favorites/{$favorite->id}");

    $response->assertStatus(404);
    $this->assertDatabaseHas('favorite_teams', ['id' => $favorite->id]);
});

it('allows a user to remove their own favorite', function () {
    $user = User::factory()->create();
    $favorite = FavoriteTeam::factory()->for($user)->create();

    $response = $this->actingAs($user, 'sanctum')->deleteJson("/api/favorites/{$favorite->id}");

    $response->assertOk()->assertJson(['success' => true]);
    $this->assertDatabaseMissing('favorite_teams', ['id' => $favorite->id]);
});
