<x-layouts.app title="Panel Principal · GIATEP">
<div class="mx-auto max-w-[1600px] space-y-8">
    <!-- Indicadores Principales según Rol -->
    @if($user->hasPermission('cases.read') || $user->hasPermission('investigations.read'))
        @if($metrics['cases'] === 0)
            <section class="rounded-3xl border border-dashed border-slate-300 bg-white px-6 py-12 text-center shadow-xs">
                <div class="mx-auto grid size-14 place-items-center rounded-2xl bg-sky-100 text-2xl font-black text-sky-800">0</div>
                <h2 class="mt-5 text-2xl font-black text-slate-950">Todavía no hay expedientes registrados</h2>
                <p class="mx-auto mt-2 max-w-2xl text-sm leading-relaxed text-slate-600">Los indicadores, gráficos y la actividad reciente aparecerán cuando se registre el primer caso. No se muestran datos ficticios en esta pantalla.</p>
                @if($user->hasPermission('cases.create'))
                    <a href="{{ route('modules.cases.create') }}" class="btn-primary mt-6">Registrar el primer caso</a>
                @endif
            </section>
        @else
        <section class="space-y-4">
            <div class="flex items-end justify-between">
                <div>
                    <p class="eyebrow">Monitoreo en Tiempo Real</p>
                    <h2 class="text-2xl font-black text-slate-950">Resumen Operativo Institucional</h2>
                </div>
                <span class="text-xs font-semibold text-slate-500">Datos sincronizados con base de datos</span>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                @if($user->hasPermission('cases.read'))
                    <a class="metric metric-link group" href="{{ route('modules.cases.index') }}">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Casos Registrados</span>
                        <div class="mt-2 flex items-baseline justify-between">
                            <strong class="text-3xl font-black text-slate-950">{{ $metrics['cases'] }}</strong>
                            <span class="rounded-full bg-blue-50 px-2 py-0.5 text-xs font-bold text-blue-700">APS</span>
                        </div>
                        <small class="mt-auto pt-3 text-xs font-bold text-sky-700 group-hover:underline">Ver listado de casos →</small>
                    </a>
                @endif

                @if($user->hasPermission('investigations.read'))
                    <a class="metric metric-link group" href="{{ route('modules.investigations.index') }}">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Investigaciones Activas</span>
                        <div class="mt-2 flex items-baseline justify-between">
                            <strong class="text-3xl font-black text-amber-600">{{ $metrics['investigations_pending'] }}</strong>
                            <span class="rounded-full bg-amber-50 px-2 py-0.5 text-xs font-bold text-amber-700">En curso</span>
                        </div>
                        <small class="mt-auto pt-3 text-xs font-bold text-amber-700 group-hover:underline">Revisar árbol causal →</small>
                    </a>
                @endif

                @if($user->hasPermission('observations.read'))
                    <a class="metric metric-link group" href="{{ route('modules.observations.index') }}">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Con Observaciones</span>
                        <div class="mt-2 flex items-baseline justify-between">
                            <strong class="text-3xl font-black text-violet-600">{{ $metrics['with_observations'] }}</strong>
                            <span class="rounded-full bg-violet-50 px-2 py-0.5 text-xs font-bold text-violet-700">Jefatura / Dirección</span>
                        </div>
                        <small class="mt-auto pt-3 text-xs font-bold text-violet-700 group-hover:underline">Ver observaciones →</small>
                    </a>
                @endif

                @if($user->hasPermission('measures.read'))
                    <a class="metric metric-link group" href="{{ route('modules.measures.index') }}">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Medidas en Ejecución</span>
                        <div class="mt-2 flex items-baseline justify-between">
                            <strong class="text-3xl font-black text-emerald-600">{{ $metrics['measures_due_soon'] }}</strong>
                            <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-bold text-emerald-700">Planes activos</span>
                        </div>
                        <small class="mt-auto pt-3 text-xs font-bold text-emerald-700 group-hover:underline">Plan de acción →</small>
                    </a>

                    <a class="metric metric-danger metric-link group" href="{{ route('modules.measures.index') }}">
                        <span class="text-xs font-bold uppercase tracking-wider text-red-700">Medidas Vencidas</span>
                        <div class="mt-2 flex items-baseline justify-between">
                            <strong class="text-3xl font-black text-red-800">{{ $metrics['measures_overdue'] }}</strong>
                            <span class="rounded-full bg-red-100 px-2 py-0.5 text-xs font-bold text-red-800">Urgente</span>
                        </div>
                        <small class="mt-auto pt-3 text-xs font-bold text-red-800 group-hover:underline">Ver medidas vencidas →</small>
                    </a>
                @endif
            </div>
        </section>

        <!-- Bloque Analítico y Actividad Reciente -->
        @php
            $totalCases = $metrics['cases'];
            $workAccidents = $demo['cases']->filter(fn($c) => in_array($c['type'], ['Accidente de trabajo', 'Accidente del trabajo', 'Accidente grave', 'Accidente fatal']))->count();
            $commuteAccidents = $demo['cases']->filter(fn($c) => $c['type'] === 'Accidente de trayecto')->count();
            $diseases = $demo['cases']->filter(fn($c) => $c['type'] === 'Enfermedad profesional')->count();
            
            $workPct = $totalCases > 0 ? round(($workAccidents / $totalCases) * 100) : 0;
            $commutePct = $totalCases > 0 ? round(($commuteAccidents / $totalCases) * 100) : 0;
            $diseasePct = $totalCases > 0 ? round(($diseases / $totalCases) * 100) : 0;

            $casesByEst = $demo['cases']->groupBy('establishment')->map->count()->sortDesc()->take(5);
        @endphp
        <section class="grid gap-6 xl:grid-cols-[1.4fr_1fr]">
            <div class="card p-6">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div>
                        <p class="eyebrow">Distribución de Accidentes y Enfermedades</p>
                        <h3 class="mt-1 text-lg font-black text-slate-950">Estadísticas de Casos en Los Ángeles</h3>
                    </div>
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">Período {{ date('Y') }}</span>
                </div>
                <div class="mt-6 grid gap-6 sm:grid-cols-2 items-center">
                    <div class="space-y-4">
                        <div>
                            <div class="flex justify-between text-xs font-bold">
                                <span class="text-slate-700">Accidentes del Trabajo (DIAT)</span>
                                <span class="text-sky-800">{{ $workAccidents }} {{ $workAccidents === 1 ? 'caso' : 'casos' }} ({{ $workPct }}%)</span>
                            </div>
                            <div class="mt-1.5 h-3 rounded-full bg-slate-100 overflow-hidden">
                                <div class="h-full bg-sky-600 rounded-full" style="width: {{ $workPct }}%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-xs font-bold">
                                <span class="text-slate-700">Accidentes de Trayecto (DIAT)</span>
                                <span class="text-indigo-800">{{ $commuteAccidents }} {{ $commuteAccidents === 1 ? 'caso' : 'casos' }} ({{ $commutePct }}%)</span>
                            </div>
                            <div class="mt-1.5 h-3 rounded-full bg-slate-100 overflow-hidden">
                                <div class="h-full bg-indigo-500 rounded-full" style="width: {{ $commutePct }}%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-xs font-bold">
                                <span class="text-slate-700">Enfermedades Profesionales (DIEP)</span>
                                <span class="text-amber-800">{{ $diseases }} {{ $diseases === 1 ? 'caso' : 'casos' }} ({{ $diseasePct }}%)</span>
                            </div>
                            <div class="mt-1.5 h-3 rounded-full bg-slate-100 overflow-hidden">
                                <div class="h-full bg-amber-500 rounded-full" style="width: {{ $diseasePct }}%"></div>
                            </div>
                        </div>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Centros con Mayor Actividad</p>
                        <ul class="mt-3 space-y-2.5 text-xs font-semibold">
                            @forelse($casesByEst as $estName => $estCount)
                                <li class="flex items-center justify-between border-b border-slate-200/60 pb-2 last:border-b-0 last:pb-0">
                                    <span class="text-slate-800">{{ $estName }}</span>
                                    <span class="rounded bg-sky-100 px-2 py-0.5 font-bold text-sky-800">{{ $estCount }} {{ $estCount === 1 ? 'caso' : 'casos' }}</span>
                                </li>
                            @empty
                                <li class="text-xs text-slate-500 italic">No hay casos registrados por establecimiento.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>

            <div class="card p-6">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div>
                        <p class="eyebrow">Trazabilidad de la Red</p>
                        <h3 class="mt-1 text-lg font-black text-slate-950">Bitácora de Actividad Reciente</h3>
                    </div>
                    <span class="size-2 rounded-full bg-emerald-500"></span>
                </div>
                <ul class="timeline mt-6">
                    @forelse($demo['activity'] as $item)
                        <li>
                            <strong class="text-slate-900 leading-snug">{{ $item['text'] }}</strong>
                            <span class="text-xs font-semibold text-slate-400">{{ $item['time'] }}</span>
                        </li>
                    @empty
                        <li class="text-xs text-slate-500 italic">No hay actividad reciente registrada en la plataforma.</li>
                    @endforelse
                </ul>
            </div>
        </section>
        @endif
    @else
        <!-- Panel para Administrador -->
        <section class="grid gap-4 md:grid-cols-4">
            <a href="{{ route('admin.registration-requests.index') }}" class="metric metric-link group">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Solicitudes de Acceso</span>
                <strong class="mt-2 text-3xl font-black text-amber-600">{{ $adminMetrics['pending_requests'] }} pendientes</strong>
                <small class="mt-auto pt-3 text-xs font-bold text-sky-700 group-hover:underline">Aprobar Delegados y CPHS →</small>
            </a>
            <a href="{{ route('admin.users.index') }}" class="metric metric-link group">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Usuarios Registrados</span>
                <strong class="mt-2 text-3xl font-black text-slate-950">{{ $adminMetrics['active_users'] }} cuentas activas</strong>
                <small class="mt-auto pt-3 text-xs font-bold text-sky-700 group-hover:underline">Gestionar usuarios →</small>
            </a>
            <a href="{{ route('admin.roles.index') }}" class="metric metric-link group">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Roles y Permisos RBAC</span>
                <strong class="mt-2 text-3xl font-black text-slate-950">{{ $adminMetrics['roles'] }} roles</strong>
                <small class="mt-auto pt-3 text-xs font-bold text-sky-700 group-hover:underline">Ver matriz de permisos →</small>
            </a>
            <a href="{{ route('admin.establishments.index') }}" class="metric metric-link group">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Establecimientos APS</span>
                <strong class="mt-2 text-3xl font-black text-slate-950">{{ $adminMetrics['active_establishments'] }} centros activos</strong>
                <small class="mt-auto pt-3 text-xs font-bold text-sky-700 group-hover:underline">Ver catálogo →</small>
            </a>
        </section>
    @endif

    <!-- Accesos Rápidos Autorizados -->
    <section class="space-y-4">
        <div>
            <p class="eyebrow">Módulos Disponibles</p>
            <h2 class="text-2xl font-black text-slate-950">Actividades Autorizadas según tu Perfil</h2>
            <p class="mt-1 text-sm text-slate-600">
                Los módulos mostrados se adaptan automáticamente a tus permisos de <strong>{{ $user->roles->pluck('name')->join(', ') }}</strong>.
            </p>
        </div>

        @if($availableActions->isEmpty())
            <div class="empty-state">
                No tienes módulos operativos asignados directamente. Puedes utilizar las funciones de administración o consultar tu perfil en el menú lateral.
            </div>
        @else
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach($availableActions as $action)
                    <a href="{{ route($action['route']) }}" class="card group p-6 transition duration-150 hover:-translate-y-1 hover:border-sky-300 hover:shadow-lg">
                        <div class="flex items-start justify-between">
                            <span class="module-icon group-hover:bg-sky-600 group-hover:text-white transition">
                                {{ mb_substr($action['title'], 0, 1) }}
                            </span>
                            <span class="text-sky-600 transition group-hover:translate-x-1" aria-hidden="true">→</span>
                        </div>
                        <h3 class="mt-4 text-lg font-black text-slate-950 group-hover:text-blue-800 transition">
                            {{ $action['title'] }}
                        </h3>
                        <p class="mt-2 text-sm leading-6 text-slate-600">
                            {{ $action['description'] }}
                        </p>
                        <span class="mt-4 inline-block text-xs font-bold text-sky-700 group-hover:text-blue-900">
                            Ingresar al módulo →
                        </span>
                    </a>
                @endforeach
            </div>
        @endif
    </section>

    <!-- Nota de Alcance Institucional -->
    <div class="flex items-start gap-3 rounded-2xl border border-sky-200 bg-sky-50/80 p-5 text-sm text-sky-950">
        <span class="text-xl">🏛</span>
        <div class="space-y-1">
            <strong class="font-black text-blue-950">Dirección Comunal de Salud de Los Ángeles - GIATEP</strong>
            <p class="text-xs text-sky-900 leading-relaxed">
                Plataforma oficial para la gestión e investigación de accidentes del trabajo y enfermedades profesionales en la red de establecimientos APS (CESFAM, SAR, CECOSF y postas rurales) en convenio con la Asociación Chilena de Seguridad (ACHS).
            </p>
        </div>
    </div>
</div>
</x-layouts.app>
