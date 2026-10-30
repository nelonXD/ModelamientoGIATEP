<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Establishment;
use App\Models\RegistrationRequest;
use App\Models\Role;
use App\Models\User;
use App\Support\DashboardData;
use App\Support\DemoWorkspace;
use App\Support\OriginalGiatepSchema;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, DashboardData $dashboardData, DemoWorkspace $workspace): View
    {
        $user = $request->user()->load(['roles', 'establishments']);
        $dashboardProfile = $dashboardData->profileFor($user);
        $availableActions = $dashboardData->actionsFor($user);

        $demo = $workspace->data(includeMeasures: true);
        $metrics = $workspace->metrics($demo);
        $adminMetrics = [
            'pending_requests' => OriginalGiatepSchema::isActive() ? 0 : RegistrationRequest::query()->where('status', 'pending')->count(),
            'active_users' => User::query()->where(User::columnFor('status'), 'active')->count(),
            'roles' => Role::query()->count(),
            'active_establishments' => OriginalGiatepSchema::isActive()
                ? Establishment::query()->count()
                : Establishment::query()->where('is_active', true)->count(),
        ];

        return view('dashboard-demo', compact('user', 'dashboardProfile', 'availableActions', 'demo', 'metrics', 'adminMetrics'));
    }
}
