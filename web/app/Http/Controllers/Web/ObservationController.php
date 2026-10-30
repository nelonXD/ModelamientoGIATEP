<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Investigation;
use App\Models\OccupationalCase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ObservationController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): View
    {
        return $this->renderModule($request, 'observations.read', 'modules.observations.index', 'Observaciones', 'Registro de observaciones y seguimiento asociado a investigaciones y medidas.');
    }

    public function store(Request $request, OccupationalCase $case): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user->hasRole('jefatura') || $user->hasRole('alta-direccion') || $user->hasRole('prevencion'), 403);
        $data = $request->validate(['body' => ['required', 'string', 'min:5', 'max:5000']]);
        $investigation = $case->investigation()->firstOrFail();
        abort_unless(in_array($investigation->status, ['En desarrollo', 'En validación'], true), 409);

        DB::table('investigation_observations')->insert([
            'investigation_folio' => $investigation->folio, 'author_rut' => $user->rut,
            'author_name' => $user->name, 'author_role' => $user->roles->pluck('name')->implode(', '),
            'body' => $data['body'], 'status' => 'pendiente', 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('investigation_events')->insert([
            'investigation_folio' => $investigation->folio, 'actor_rut' => $user->rut,
            'actor_name' => $user->name, 'actor_role' => $user->roles->pluck('name')->implode(', '),
            'event' => 'Observación registrada', 'created_at' => now(),
        ]);

        return to_route('modules.investigations.index', ['case' => $case->folio])->with('demo_status', 'Observación enviada al equipo investigador.');
    }

    public function reply(Request $request, int $observation): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user->hasRole('cphs') || $user->hasRole('delegado') || $user->hasRole('prevencion'), 403);
        $data = $request->validate(['body' => ['required', 'string', 'min:2', 'max:5000']]);
        $root = DB::table('investigation_observations')->where('id', $observation)->whereNull('parent_id')->first();
        abort_unless($root, 404);
        $investigation = Investigation::where('folio', $root->investigation_folio)->with('owner')->firstOrFail();
        abort_unless($investigation->owner?->rut === $user->rut, 403);
        abort_unless(in_array($root->status, ['pendiente', 'respondida'], true), 409);
        abort_if($investigation->status === 'Completada', 409, 'La investigación está completada y sus observaciones son de solo lectura.');

        DB::transaction(function () use ($user, $data, $root, $investigation): void {
            DB::table('investigation_observations')->insert([
                'investigation_folio' => $investigation->folio, 'parent_id' => $root->id, 'author_rut' => $user->rut,
                'author_name' => $user->name, 'author_role' => $user->roles->pluck('name')->implode(', '),
                'body' => $data['body'], 'status' => 'respondida', 'responded_at' => now(), 'created_at' => now(), 'updated_at' => now(),
            ]);
            DB::table('investigation_observations')->where('id', $root->id)->update(['status' => 'respondida', 'responded_at' => now(), 'updated_at' => now()]);
            DB::table('investigation_events')->insert([
                'investigation_folio' => $investigation->folio, 'actor_rut' => $user->rut, 'actor_name' => $user->name,
                'actor_role' => $user->roles->pluck('name')->implode(', '), 'event' => 'Observación respondida', 'created_at' => now(),
            ]);
        });

        return back()->with('demo_status', 'Respuesta agregada al hilo de la observación.');
    }
}
