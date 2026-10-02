<?php

namespace App\Http\Controllers;

use App\Support\DashboardData;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, DashboardData $dashboardData): View
    {
        $user = $request->user()->load(['roles', 'establishments']);
        $dashboardProfile = $dashboardData->profileFor($user);
        $availableActions = $dashboardData->actionsFor($user);

        return view('dashboard', compact('user', 'dashboardProfile', 'availableActions'));
    }
}
