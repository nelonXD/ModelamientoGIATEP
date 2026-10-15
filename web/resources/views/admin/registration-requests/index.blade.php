<x-layouts.app title='Solicitudes de registro · GIATEP'>
<div class='space-y-6'>
    <!-- Encabezado de Administración -->
    <div class='flex flex-wrap items-end justify-between gap-4'>
        <div>
            <p class='eyebrow'>Gestión y Control de Acceso</p>
            <h1 class='mt-1 text-3xl font-black tracking-tight text-slate-950 sm:text-4xl'>Solicitudes de Registro</h1>
            <p class='mt-1 text-sm text-slate-600'>Evaluación de requerimientos de acceso para integrantes del Comité Paritario (CPHS) y Delegados de Seguridad.</p>
        </div>
    </div>

    @if(session('status'))
        <div class='flex items-center gap-2.5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3.5 text-sm font-semibold text-emerald-950 shadow-xs' role='status'>
            <span class='flex size-6 shrink-0 items-center justify-center rounded-full bg-emerald-600 text-xs font-bold text-white'>✓</span>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    <!-- Métricas de Solicitudes -->
    <div class='grid gap-4 sm:grid-cols-3'>
        <div class='stat-card'>
            <div>
                <span class='text-xs font-bold uppercase tracking-wider text-slate-500'>Pendientes de Revisión</span>
                <strong class='mt-2 block text-3xl font-black text-amber-600'>{{ $counts['pending'] ?? 0 }}</strong>
            </div>
            <span class='flex size-10 items-center justify-center rounded-xl bg-amber-50 text-amber-700 text-lg font-bold'>⏳</span>
        </div>
        <div class='stat-card'>
            <div>
                <span class='text-xs font-bold uppercase tracking-wider text-slate-500'>Solicitudes Aprobadas</span>
                <strong class='mt-2 block text-3xl font-black text-emerald-700'>{{ $counts['approved'] ?? 0 }}</strong>
            </div>
            <span class='flex size-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700 text-lg font-bold'>✓</span>
        </div>
        <div class='stat-card'>
            <div>
                <span class='text-xs font-bold uppercase tracking-wider text-slate-500'>Solicitudes Rechazadas</span>
                <strong class='mt-2 block text-3xl font-black text-slate-700'>{{ $counts['rejected'] ?? 0 }}</strong>
            </div>
            <span class='flex size-10 items-center justify-center rounded-xl bg-slate-100 text-slate-600 text-lg font-bold'>✕</span>
        </div>
    </div>

    <!-- Barra de Filtros -->
    <form method='GET' class='filter-bar'>
        <div class='xl:col-span-2'>
            <label for='search' class='label'>Buscar por nombre o RUT</label>
            <input id='search' name='search' value='{{ request('search') }}' class='input' placeholder='Nombre del funcionario o RUT...'>
        </div>
        <div>
            <label for='status' class='label'>Estado</label>
            <select id='status' name='status' class='input'>
                <option value=''>Todos los estados</option>
                @foreach(['pending' => 'Pendiente', 'approved' => 'Aprobada', 'rejected' => 'Rechazada'] as $value => $label)
                    <option value='{{ $value }}' @selected(request('status') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for='establishment_id' class='label'>Establecimiento</label>
            <select id='establishment_id' name='establishment_id' class='input'>
                <option value=''>Todos los centros</option>
                @foreach($establishments as $item)
                    <option value='{{ $item->id }}' @selected((string) request('establishment_id') === (string) $item->id)>
                        {{ $item->type }} · {{ $item->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label for='requested_role' class='label'>Rol Solicitado</label>
            <select id='requested_role' name='requested_role' class='input'>
                <option value=''>Todos los roles</option>
                <option value='delegado' @selected(request('requested_role') === 'delegado')>Delegado de Seguridad</option>
                <option value='cphs' @selected(request('requested_role') === 'cphs')>Comité Paritario</option>
            </select>
        </div>
        <div class='flex items-center gap-2 xl:col-span-5 pt-1'>
            <button class='btn-primary' type='submit'>Aplicar Filtros</button>
            <a class='btn-secondary' href='{{ route('admin.registration-requests.index') }}'>Limpiar</a>
        </div>
    </form>

    <!-- Tabla de Solicitudes -->
    <div class='card table-card shadow-sm'>
        <table class='data-table'>
            <thead>
                <tr>
                    <th>Nombre del Funcionario</th>
                    <th>RUT</th>
                    <th>Cargo Laboral</th>
                    <th>Establecimiento</th>
                    <th>Rol Solicitado</th>
                    <th>Fecha Ingreso</th>
                    <th>Estado</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $item)
                    <tr>
                        <td class='font-bold text-slate-900'>{{ $item->name }}</td>
                        <td class='font-mono text-xs text-slate-600'>{{ $item->rut }}</td>
                        <td>{{ $item->job_title }}</td>
                        <td>{{ $item->establishment->type }} · {{ $item->establishment->name }}</td>
                        <td>
                            <span class='rounded bg-sky-50 px-2.5 py-0.5 text-xs font-bold text-sky-900'>
                                {{ $item->requested_role === 'delegado' ? 'Delegado de Seguridad' : 'Comité Paritario' }}
                            </span>
                        </td>
                        <td class='text-xs text-slate-500'>{{ $item->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <span class='status status-{{ $item->status }}'>
                                {{ ['pending' => 'Pendiente', 'approved' => 'Aprobada', 'rejected' => 'Rechazada'][$item->status] }}
                            </span>
                        </td>
                        <td>
                            <a class='text-link text-xs' href='{{ route('admin.registration-requests.index', array_merge(request()->query(), ['review' => $item->id])) }}'>
                                Revisar Expediente →
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan='8' class='py-12 text-center text-xs text-slate-500'>
                            No hay solicitudes registradas que coincidan con los criterios de búsqueda.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if($requests->hasPages())
            <div class='border-t border-slate-200 bg-slate-50/50 p-4'>
                {{ $requests->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Lateral de Evaluación de Solicitud -->
    @if($selected)
        <div class='fixed inset-0 z-40 flex justify-end bg-slate-950/50 backdrop-blur-xs' role='dialog' aria-modal='true' aria-labelledby='detail-title'>
            <section class='h-full w-full max-w-xl overflow-y-auto bg-white p-6 shadow-2xl sm:p-8 flex flex-col justify-between'>
                <div>
                    <div class='flex items-start justify-between gap-4 border-b border-slate-100 pb-5'>
                        <div>
                            <p class='eyebrow'>Expediente de Registro</p>
                            <h2 id='detail-title' class='mt-1 text-2xl font-black text-slate-950'>{{ $selected->name }}</h2>
                            <p class='text-xs text-slate-500'>RUT: {{ $selected->rut }}</p>
                        </div>
                        <a href='{{ route('admin.registration-requests.index', request()->except('review')) }}' class='flex size-8 items-center justify-center rounded-xl bg-slate-100 text-lg font-bold text-slate-600 hover:bg-slate-200 transition' aria-label='Cerrar'>×</a>
                    </div>

                    <dl class='mt-6 grid gap-4 sm:grid-cols-2 text-xs'>
                        <div><dt>RUT:</dt><dd class='text-sm font-bold text-slate-900'>{{ $selected->rut }}</dd></div>
                        <div><dt>Fecha de Envío:</dt><dd class='text-sm font-bold text-slate-900'>{{ $selected->created_at->format('d/m/Y H:i') }}</dd></div>
                        <div><dt>Cargo Laboral:</dt><dd class='text-sm font-bold text-slate-900'>{{ $selected->job_title }}</dd></div>
                        <div><dt>Rol Solicitado:</dt><dd class='text-sm font-bold text-sky-800'>{{ $selected->requested_role === 'delegado' ? 'Delegado de Seguridad' : 'Comité Paritario' }}</dd></div>
                        <div class='sm:col-span-2'><dt>Centro de Salud:</dt><dd class='text-sm font-bold text-slate-900'>{{ $selected->establishment->type }} · {{ $selected->establishment->name }}</dd></div>
                        <div><dt>Correo Institucional:</dt><dd class='text-sm font-bold text-slate-900'>{{ $selected->email }}</dd></div>
                        <div><dt>Teléfono:</dt><dd class='text-sm font-bold text-slate-900'>{{ $selected->phone }}</dd></div>
                    </dl>

                    @if($selected->status === 'pending')
                        <div class='mt-8 space-y-5 border-t border-slate-200 pt-6'>
                            <form method='POST' action='{{ route('admin.registration-requests.update', $selected) }}' data-confirm='¿Confirmas la aprobación de {{ $selected->name }} como {{ $selected->requested_role === 'delegado' ? 'Delegado de Seguridad' : 'Comité Paritario' }} en {{ $selected->establishment->name }}?'>
                                @csrf 
                                @method('PATCH')
                                <input type='hidden' name='action' value='approve'>
                                <button class='btn-primary w-full py-3 bg-emerald-700 hover:bg-emerald-800 text-sm'>
                                    ✓ Aprobar Solicitud y Asignar Rol
                                </button>
                            </form>

                            <form method='POST' action='{{ route('admin.registration-requests.update', $selected) }}' class='space-y-3' data-confirm='¿Confirmas el rechazo de esta solicitud?'>
                                @csrf 
                                @method('PATCH')
                                <input type='hidden' name='action' value='reject'>
                                <label for='rejection_reason' class='label'>Motivo de Rechazo Formal</label>
                                <textarea id='rejection_reason' name='rejection_reason' rows='3' class='input text-xs' placeholder='Explique la causa del rechazo (ej. funcionario no vigente, rol duplicado)...' required>{{ old('rejection_reason') }}</textarea>
                                @error('rejection_reason')<p class='error'>{{ $message }}</p>@enderror
                                <button class='btn-danger w-full py-3 text-sm'>
                                    ✕ Rechazar Solicitud
                                </button>
                            </form>
                        </div>
                    @else
                        <div class='mt-8 rounded-2xl border {{ $selected->status === 'approved' ? 'border-emerald-200 bg-emerald-50/60' : 'border-slate-200 bg-slate-50' }} p-5 text-xs'>
                            <p class='font-black text-sm {{ $selected->status === 'approved' ? 'text-emerald-950' : 'text-slate-950' }}'>
                                Solicitud {{ $selected->status === 'approved' ? 'Aprobada' : 'Rechazada' }}
                            </p>
                            <p class='mt-1 text-slate-600'>
                                Resuelta por <strong>{{ $selected->resolver?->name ?? 'Administrador del Sistema' }}</strong> el {{ $selected->resolved_at?->format('d/m/Y H:i') }}.
                            </p>
                            @if($selected->rejection_reason)
                                <p class='mt-3 rounded-lg bg-white p-3 border border-slate-200 text-slate-700'>
                                    <strong>Motivo registrado:</strong> {{ $selected->rejection_reason }}
                                </p>
                            @endif
                        </div>
                    @endif
                </div>

                <div class='pt-6 border-t border-slate-100 mt-6'>
                    <a href='{{ route('admin.registration-requests.index', request()->except('review')) }}' class='btn-secondary w-full text-center'>
                        Cerrar Detalle
                    </a>
                </div>
            </section>
        </div>
    @endif
</div>
</x-layouts.app>
