<?php

namespace App\Services;

use App\Exceptions\TheSportsDbException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class TheSportsDbService
{
    private const TTL_MINUTES = 10;

    private string $baseUrl;

    private string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.thesportsdb.base_url');
        $this->apiKey = config('services.thesportsdb.api_key');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getLeagues(?string $sport = null): array
    {
        $key = 'thesportsdb.leagues.'.($sport ?? 'all');

        return Cache::remember($key, now()->addMinutes(self::TTL_MINUTES), function () use ($sport) {
            $leagues = $this->fetch('all_leagues.php')['leagues'] ?? [];

            if ($sport === null) {
                return $leagues;
            }

            $leagues = array_values(array_filter(
                $leagues,
                fn (array $league) => strcasecmp($league['strSport'] ?? '', $sport) === 0
            ));

            $badges = collect($this->fetch('search_all_leagues.php', ['s' => $sport])['countries'] ?? [])
                ->pluck('strBadge', 'idLeague');

            return array_map(
                fn (array $league) => $league + ['strBadge' => $badges[$league['idLeague']] ?? null],
                $leagues
            );
        });
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getSports(): array
    {
        return Cache::remember(
            'thesportsdb.sports',
            now()->addMinutes(self::TTL_MINUTES),
            fn () => $this->fetch('all_sports.php')['sports'] ?? []
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getTeamsByLeague(string $leagueId): array
    {
        return Cache::remember(
            "thesportsdb.league_teams.{$leagueId}",
            now()->addMinutes(self::TTL_MINUTES),
            function () use ($leagueId) {
                $leagueName = $this->resolveLeagueName($leagueId);

                if ($leagueName === null) {
                    return [];
                }

                return $this->fetch('search_all_teams.php', ['l' => $leagueName])['teams'] ?? [];
            }
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getTeam(string $teamId): ?array
    {
        return Cache::remember(
            "thesportsdb.team.{$teamId}",
            now()->addMinutes(self::TTL_MINUTES),
            fn () => $this->fetch('lookupteam.php', ['id' => $teamId])['teams'][0] ?? null
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getPreviousMatches(string $teamId): array
    {
        return Cache::remember(
            "thesportsdb.team_previous.{$teamId}",
            now()->addMinutes(self::TTL_MINUTES),
            fn () => $this->fetch('eventslast.php', ['id' => $teamId])['results'] ?? []
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getLeagueTable(string $leagueId, ?string $season = null): array
    {
        $key = "thesportsdb.league_table.{$leagueId}.".($season ?? 'current');

        return Cache::remember(
            $key,
            now()->addMinutes(self::TTL_MINUTES),
            function () use ($leagueId, $season) {
                $params = ['l' => $leagueId];

                if ($season !== null) {
                    $params['s'] = $season;
                }

                return $this->fetch('lookuptable.php', $params)['table'] ?? [];
            }
        );
    }

    private function resolveLeagueName(string $leagueId): ?string
    {
        $league = collect($this->getLeagues())
            ->first(fn (array $league) => (string) ($league['idLeague'] ?? '') === $leagueId);

        return $league['strLeague'] ?? null;
    }

    /**
     * @param  array<string, string>  $params
     * @return array<string, mixed>
     */
    private function fetch(string $endpoint, array $params = []): array
    {
        try {
            $response = Http::connectTimeout(3)
                ->timeout(10)
                ->get("{$this->baseUrl}/{$this->apiKey}/{$endpoint}", $params);

            $response->throw();

            return $response->json() ?? [];
        } catch (ConnectionException|RequestException $e) {
            report($e);

            throw TheSportsDbException::unavailable();
        }
    }
}
