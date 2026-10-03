<?php

namespace App\Http\Controllers;

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

        return view('dashboard-demo', compact('user', 'dashboardProfile', 'availableActions', 'demo', 'metrics'));
    }
}
