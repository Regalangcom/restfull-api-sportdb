<?php

use Illuminate\Support\Facades\Http;

it('returns leagues from thesportsdb through the gateway', function () {
    Http::preventStrayRequests();

    Http::fake([
        '*/all_leagues.php' => Http::response([
            'leagues' => [
                ['idLeague' => '4328', 'strLeague' => 'English Premier League', 'strSport' => 'Soccer'],
                ['idLeague' => '4380', 'strLeague' => 'NBA', 'strSport' => 'Basketball'],
                ['idLeague' => '4329', 'strLeague' => 'English League Championship', 'strSport' => 'Soccer'],
            ],
        ]),
        '*/search_all_leagues.php*' => Http::response([
            'countries' => [
                ['idLeague' => '4328', 'strBadge' => 'https://example.com/epl.png'],
            ],
        ]),
    ]);

    $response = $this->getJson('/api/leagues?sport=Soccer');

    $response->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.strBadge', 'https://example.com/epl.png')
        ->assertJsonPath('data.1.strBadge', null);

    Http::assertSent(fn ($request) => str_contains($request->url(), 'all_leagues.php'));
});

it('returns the list of sports through the gateway', function () {
    Http::preventStrayRequests();

    Http::fake([
        '*/all_sports.php' => Http::response([
            'sports' => [['idSport' => '102', 'strSport' => 'Soccer']],
        ]),
    ]);

    $this->getJson('/api/sports')
        ->assertOk()
        ->assertJsonPath('data.0.strSport', 'Soccer');
});

it('returns a not found response when the external team does not exist', function () {
    Http::preventStrayRequests();

    Http::fake([
        '*/lookupteam.php*' => Http::response(['teams' => null]),
    ]);

    $response = $this->getJson('/api/teams/999999');

    $response->assertStatus(404)->assertJson(['success' => false]);
});

it('returns a gateway error when thesportsdb is unavailable', function () {
    Http::preventStrayRequests();

    Http::fake([
        '*/lookupteam.php*' => Http::failedConnection(),
    ]);

    $response = $this->getJson('/api/teams/133604');

    $response->assertStatus(502)->assertJson(['success' => false]);
});

it('converts previous match times to WIB', function () {
    Http::preventStrayRequests();

    Http::fake([
        '*/eventslast.php*' => Http::response([
            'results' => [
                [
                    'idEvent' => '1',
                    'strEvent' => 'Arsenal vs Chelsea',
                    'strHomeTeam' => 'Arsenal',
                    'strAwayTeam' => 'Chelsea',
                    'dateEvent' => '2026-01-01',
                    'strTime' => '12:00:00',
                ],
            ],
        ]),
    ]);

    $response = $this->getJson('/api/teams/133604/previous-matches');

    $response->assertOk()->assertJsonPath('data.0.match_time_wib', '2026-01-01 19:00:00');
});
