<?php

namespace App\Http\Controllers;

use App\Http\Concerns\ApiResponse;
use App\Services\TheSportsDbService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeagueController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly TheSportsDbService $theSportsDbService) {}

    public function index(Request $request): JsonResponse
    {
        $leagues = $this->theSportsDbService->getLeagues($request->string('sport')->value() ?: null);

        return $this->success($leagues, 'Leagues retrieved successfully.');
    }

    public function teams(string $league): JsonResponse
    {
        $teams = $this->theSportsDbService->getTeamsByLeague($league);

        return $this->success($teams, 'Teams retrieved successfully.');
    }

    public function table(Request $request, string $league): JsonResponse
    {
        $table = $this->theSportsDbService->getLeagueTable(
            $league,
            $request->string('season')->value() ?: null
        );

        return $this->success($table, 'League table retrieved successfully.');
    }
}
