<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResolveRegistrationRequest;
use App\Models\Establishment;
use App\Models\RegistrationRequest;
use App\Models\Role;
use App\Models\User;
use App\Support\OriginalGiatepSchema;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RegistrationRequestController extends Controller
{
    public function index(Request $request): View
    {
        if (OriginalGiatepSchema::isActive()) {
            $allRequests = RegistrationRequest::query()->with(['establishment', 'resolver'])->orderByDesc('Fecha_Solicitud')->get();
            $filtered = $allRequests
                ->when($request->filled('search'), function ($items) use ($request) {
                    $search = mb_strtolower($request->string('search')->toString());

                    return $items->filter(fn (RegistrationRequest $item): bool => str_contains(mb_strtolower($item->name), $search)
                        || str_contains(mb_strtolower($item->rut), $search));
                })
                ->when($request->filled('status'), fn ($items) => $items->where('status', $request->string('status')->toString()))
                ->when($request->filled('establishment_id'), fn ($items) => $items->where('establishment_id', $request->integer('establishment_id')))
                ->when($request->filled('requested_role'), fn ($items) => $items->where('requested_role', $request->string('requested_role')->toString()))
                ->values();
            $page = LengthAwarePaginator::resolveCurrentPage();
            $requests = new LengthAwarePaginator(
                $filtered->forPage($page, 10)->values(), $filtered->count(), 10, $page,
                ['path' => $request->url(), 'query' => $request->query()],
            );
            $counts = $allRequests->countBy('status');
            $selected = $request->filled('review') ? $allRequests->firstWhere('id', $request->integer('review')) : null;
            $establishments = Establishment::orderBy('Nombre_Dependencia')->get();

            return view('admin.registration-requests.index', compact('requests', 'counts', 'selected', 'establishments'));
        }

        $query = RegistrationRequest::query()->with(['establishment', 'resolver'])
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = $request->string('search')->toString();
                $pattern = '%'.$search.'%';
                $query->where(fn ($query) => $query->where('name', 'like', $pattern)->orWhere('rut', 'like', $pattern));
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('establishment_id'), fn ($query) => $query->where('establishment_id', $request->integer('establishment_id')))
            ->when($request->filled('requested_role'), fn ($query) => $query->where('requested_role', $request->string('requested_role')));

        $requests = $query->orderByRaw('status = ? desc', ['pending'])->latest()->paginate(10)->withQueryString();
        $counts = RegistrationRequest::selectRaw('status, count(*) as aggregate')->groupBy('status')->pluck('aggregate', 'status');
        $selected = $request->filled('review')
            ? RegistrationRequest::with(['establishment', 'resolver'])->findOrFail($request->integer('review'))
            : null;
        $establishments = Establishment::orderBy('type')->orderBy('name')->get();

        return view('admin.registration-requests.index', compact('requests', 'counts', 'selected', 'establishments'));
    }

    public function update(ResolveRegistrationRequest $request, RegistrationRequest $registrationRequest): RedirectResponse
    {
        if (OriginalGiatepSchema::isActive()) {
            DB::transaction(function () use ($request, $registrationRequest): void {
                $locked = RegistrationRequest::query()->lockForUpdate()->findOrFail($registrationRequest->getKey());
                abort_if($locked->status !== 'pending', 409, 'Esta solicitud ya fue resuelta.');

                if ($request->string('action')->toString() === 'approve') {
                    $roleId = DB::table('rol')->where('Codigo_Rol', $locked->requested_role)->value('Id_Rol');
                    abort_unless($roleId, 422, 'El rol solicitado no existe en ROL.');

                    $user = new User;
                    $user->fill([
                        'rut' => $locked->rut, 'name' => $locked->name, 'email' => $locked->email,
                        'job_title' => $locked->job_title, 'phone' => $locked->phone,
                        'password' => $locked->password, 'status' => 'active',
                    ]);
                    $user->Id_Dependencia = $locked->establishment_id;
                    $user->save();
                    $user->roles()->syncWithoutDetaching([$roleId]);

                    DB::table('solicitud_acceso')->where('Id_Solicitud', $locked->getKey())->update([
                        'Rut_Usuario_Beneficiario' => $user->getKey(),
                    ]);
                    $locked->status = 'approved';
                } else {
                    $locked->status = 'rejected';
                    $locked->rejection_reason = $request->string('rejection_reason')->toString();
                }

                $locked->fill([
                    'pending_key' => null,
                    'resolved_by' => $request->user()->getKey(),
                    'resolved_at' => now(),
                ])->save();
            });

            $message = $request->string('action')->toString() === 'approve' ? 'Solicitud aprobada.' : 'Solicitud rechazada.';

            return redirect()->route('admin.registration-requests.index')->with('status', $message);
        }

        DB::transaction(function () use ($request, $registrationRequest): void {
            $registrationRequest = RegistrationRequest::query()->lockForUpdate()->findOrFail($registrationRequest->id);
            abort_if($registrationRequest->status !== 'pending', 409, 'Esta solicitud ya fue resuelta.');

            if ($request->string('action')->toString() === 'approve') {
                $this->approve($registrationRequest);
            } else {
                $registrationRequest->status = 'rejected';
                $registrationRequest->rejection_reason = $request->string('rejection_reason')->toString();
            }

            $registrationRequest->fill([
                'pending_key' => null,
                'resolved_by' => $request->user()->id,
                'resolved_at' => now(),
            ])->save();
        });

        $message = $request->string('action')->toString() === 'approve' ? 'Solicitud aprobada.' : 'Solicitud rechazada.';

        return redirect()->route('admin.registration-requests.index')->with('status', $message);
    }

    private function approve(RegistrationRequest $registrationRequest): void
    {
        $role = Role::where('slug', $registrationRequest->requested_role)->firstOrFail();
        $user = User::firstOrNew(['rut' => $registrationRequest->rut]);
        $user->fill([
            'name' => $registrationRequest->name,
            'email' => $registrationRequest->email,
            'job_title' => $registrationRequest->job_title,
            'phone' => $registrationRequest->phone,
            'password' => $registrationRequest->password,
            'status' => 'active',
        ])->save();
        $user->roles()->syncWithoutDetaching($role);
        $user->establishments()->syncWithoutDetaching($registrationRequest->establishment_id);
        $registrationRequest->status = 'approved';
    }
}
