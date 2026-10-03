<?php

namespace App\Http\Controllers;

use App\Support\DemoWorkspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DemoWorkspaceController extends Controller
{
    public function show(Request $request, DemoWorkspace $workspace, string $module, ?string $id = null): View
    {
        $permission = $workspace->permissions()[$module] ?? null;
        abort_unless($permission && $request->user()->hasPermission($permission), 403);

        $collection = match ($module) {
            'cases' => 'cases',
            'investigations', 'reviews' => 'investigations',
            'observations' => 'observations',
            'measures' => 'measures',
            default => null,
        };
        $selected = $id && $collection ? $workspace->find($collection, $id) : null;
        abort_if($id && $collection && ! $selected, 404);

        return view('demo.workspace', [
            'module' => $module,
            'selected' => $selected,
            'demo' => $workspace->data(),
            'mode' => $request->string('mode')->toString() ?: ($id ? 'detail' : 'index'),
        ]);
    }

    public function action(Request $request, DemoWorkspace $workspace, string $module): RedirectResponse
    {
        $permission = $workspace->permissions()[$module] ?? null;
        abort_unless($permission && $request->user()->hasPermission($permission), 403);

        $validated = $request->validate([
            'action' => ['required', 'string', 'max:80'],
            'return_to' => ['nullable', 'string', 'max:500'],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);
        $changes = $request->session()->get(DemoWorkspace::SESSION_KEY, []);
        $changes[] = ['module' => $module, 'action' => $validated['action'], 'note' => $validated['note'] ?? null, 'user' => $request->user()->name, 'at' => now()->format('d/m/Y H:i')];
        $request->session()->put(DemoWorkspace::SESSION_KEY, array_slice($changes, -20));

        return redirect()->to($validated['return_to'] ?? url()->previous())
            ->with('demo_status', 'Cambios guardados en la demostración.');
    }

    public function reset(Request $request): RedirectResponse
    {
        $request->session()->forget(DemoWorkspace::SESSION_KEY);

        return back()->with('demo_status', 'La demostración fue restablecida a sus datos originales.');
    }
}
