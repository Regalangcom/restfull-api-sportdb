<?php

namespace App\Http\Controllers;

use App\Http\Concerns\ApiResponse;
use App\Http\Resources\MatchResource;
use App\Services\TheSportsDbService;
use Illuminate\Http\JsonResponse;

class TeamController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly TheSportsDbService $theSportsDbService) {}

    public function show(string $team): JsonResponse
    {
        $team = $this->theSportsDbService->getTeam($team);

        if ($team === null) {
            return $this->error('Team not found.', 404);
        }

        return $this->success($team, 'Team retrieved successfully.');
    }

    public function previousMatches(string $team): JsonResponse
    {
        $matches = $this->theSportsDbService->getPreviousMatches($team);

        return $this->success(
            MatchResource::collection($matches)->resolve(),
            'Previous matches retrieved successfully.'
        );
    }
}
