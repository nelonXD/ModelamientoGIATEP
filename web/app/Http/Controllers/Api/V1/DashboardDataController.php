<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use App\Support\DashboardData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class DashboardDataController extends Controller
{
    public function __invoke(Request $request, DashboardData $dashboardData): JsonResponse
    {
        /** @var User $user */
        $user = $request->user()->load(['roles', 'establishments']);

        return response()->json([
            'user' => new UserResource($user),
            'profile' => $dashboardData->profileFor($user),
            'actions' => $dashboardData->actionsFor($user)->map(
                fn (array $action): array => Arr::except($action, ['permission', 'route']),
            ),
        ]);
    }
}
