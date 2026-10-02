<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

abstract class ModuleController extends Controller
{
    protected function renderModule(Request $request, string $permission, string $view, string $title, string $description): View
    {
        abort_unless($request->user()->hasPermission($permission), 403);

        return view($view, compact('title', 'description'));
    }
}
