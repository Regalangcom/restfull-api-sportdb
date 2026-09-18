<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MatchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource['idEvent'] ?? null,
            'event' => $this->resource['strEvent'] ?? null,
            'league' => $this->resource['strLeague'] ?? null,
            'home_team' => $this->resource['strHomeTeam'] ?? null,
            'away_team' => $this->resource['strAwayTeam'] ?? null,
            'home_score' => $this->resource['intHomeScore'] ?? null,
            'away_score' => $this->resource['intAwayScore'] ?? null,
            'match_time_wib' => $this->matchTimeInWib(),
            'venue' => $this->resource['strVenue'] ?? null,
        ];
    }

    private function matchTimeInWib(): ?string
    {
        $date = $this->resource['dateEvent'] ?? null;
        $time = $this->resource['strTime'] ?? null;

        if ($date === null) {
            return null;
        }

        return Carbon::parse(trim("{$date} {$time}"), 'UTC')
            ->setTimezone('Asia/Jakarta')
            ->toDateTimeString();
    }
}
