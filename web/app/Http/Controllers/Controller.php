<?php

namespace App\Http\Controllers;

use App\Support\DemoWorkspace;
use Illuminate\Http\Request;
use Illuminate\View\View;

abstract class Controller
{
    protected function renderModule(Request $request, string $permission, string $view, string $title, string $description): View
    {
        abort_unless($request->user()->hasPermission($permission), 403);

        $module = match ($permission) {
            'cases.read' => 'cases',
            'investigations.read' => 'investigations',
            'reviews.read' => 'reviews',
            'measures.read' => 'measures',
            'observations.read' => 'observations',
            'statistics.read' => 'statistics',
            'reports.export' => 'reports',
            'profile.read' => 'profile',
            'users.manage' => 'users',
            'roles.manage' => 'roles',
            'establishments.manage' => 'establishments',
            'settings.manage' => 'settings',
        };

        $needsCaseData = in_array($module, ['cases', 'investigations', 'reviews', 'measures', 'statistics', 'reports'], true);
        $includeMeasures = in_array($module, ['reviews', 'measures', 'statistics', 'reports'], true);
        $demo = $needsCaseData
            ? app(DemoWorkspace::class)->data(includeMeasures: $includeMeasures)
            : [
                'cases' => collect(),
                'investigations' => collect(),
                'measures' => collect(),
                'observations' => collect(),
                'establishments' => [],
                'activity' => [],
            ];

        return view('demo.workspace', [
            'module' => $module,
            'selected' => null,
            'mode' => 'index',
            'demo' => $demo,
            'title' => $title,
            'description' => $description,
        ]);
    }
}
