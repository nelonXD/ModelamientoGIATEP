<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Establishment;
use App\Models\RegistrationRequest;
use App\Models\Role;
use App\Models\User;
use App\Support\DashboardData;
use App\Support\DemoWorkspace;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, DashboardData $dashboardData, DemoWorkspace $workspace): View
    {
        $user = $request->user()->load(['roles', 'establishments']);
        $dashboardProfile = $dashboardData->profileFor($user);
        $availableActions = $dashboardData->actionsFor($user);

        $demo = $workspace->data();
        $metrics = $workspace->metrics();
        $adminMetrics = [
            'pending_requests' => RegistrationRequest::query()->where('status', 'pending')->count(),
            'active_users' => User::query()->where('status', 'active')->count(),
            'roles' => Role::query()->count(),
            'active_establishments' => Establishment::query()->where('is_active', true)->count(),
        ];

        return view('dashboard-demo', compact('user', 'dashboardProfile', 'availableActions', 'demo', 'metrics', 'adminMetrics'));
    }
}
