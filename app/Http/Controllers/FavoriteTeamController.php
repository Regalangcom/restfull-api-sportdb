<?php

namespace App\Http\Controllers;

use App\Http\Concerns\ApiResponse;
use App\Http\Requests\StoreFavoriteTeamRequest;
use App\Http\Resources\FavoriteTeamResource;
use App\Models\FavoriteTeam;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavoriteTeamController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $favorites = $request->user()->favoriteTeams()->latest()->get();

        return $this->success(
            FavoriteTeamResource::collection($favorites)->resolve(),
            'Favorite teams retrieved successfully.'
        );
    }

    public function store(StoreFavoriteTeamRequest $request): JsonResponse
    {
        if ($request->user()->favoriteTeams()->where('team_id', $request->string('team_id'))->exists()) {
            return $this->error('This team is already in your favorites.', 409);
        }

        $favorite = $request->user()->favoriteTeams()->create([
            'team_id' => $request->string('team_id'),
            'team_name' => $request->string('team_name'),
            'team_badge' => $request->string('team_badge')->value() ?: null,
        ]);

        return $this->success(
            new FavoriteTeamResource($favorite),
            'Team added to favorites.',
            201
        );
    }

    public function destroy(Request $request, FavoriteTeam $favorite): JsonResponse
    {
        $favorite = $request->user()
            ->favoriteTeams()
            ->whereKey($favorite->id)
            ->firstOrFail();

        $favorite->delete();

        return $this->success(null, 'Team removed from favorites.');
    }
}
