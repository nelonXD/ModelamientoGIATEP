<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Support\DemoWorkspace;
use Illuminate\Http\Request;
use Illuminate\View\View;

abstract class ModuleController extends Controller
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

        return view('demo.workspace', [
            'module' => $module,
            'selected' => null,
            'mode' => 'index',
            'demo' => app(DemoWorkspace::class)->data(),
            'title' => $title,
            'description' => $description,
        ]);
    }
}
