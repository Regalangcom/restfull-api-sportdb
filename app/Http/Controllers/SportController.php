<?php

namespace App\Http\Controllers;

use App\Http\Concerns\ApiResponse;
use App\Services\TheSportsDbService;
use Illuminate\Http\JsonResponse;

class SportController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly TheSportsDbService $theSportsDbService) {}

    public function index(): JsonResponse
    {
        return $this->success($this->theSportsDbService->getSports(), 'Sports retrieved successfully.');
    }
}
