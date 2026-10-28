<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'GIATEP · DCS Los Ángeles' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-900 antialiased selection:bg-sky-500 selection:text-white">
@php
$groups = [
    'Trabajo Operativo' => [
        ['Inicio', 'dashboard.view', 'dashboard'],
        ['Inicio administrativo', 'admin.dashboard', 'dashboard'],
        ['Casos', 'cases.read', 'modules.cases.index'],
        ['Investigación', 'investigations.read', 'modules.investigations.index'],
        ['Revisiones y validaciones', 'reviews.read', 'modules.reviews.index'],
        ['Observaciones', 'observations.read', 'modules.observations.index'],
        ['Medidas y planes de acción', 'measures.read', 'modules.measures.index'],
    ],
    'Análisis Institucional' => [
        ['Dashboards y estadísticas', 'statistics.read', 'modules.statistics.index'],
        ['Reportes e informes', 'reports.export', 'modules.reports.index'],
    ],
    'Administración' => [
        ['Solicitudes de registro', 'registration-requests.review', 'admin.registration-requests.index'],
        ['Usuarios', 'users.manage', 'admin.users.index'],
        ['Roles y permisos', 'roles.manage', 'admin.roles.index'],
        ['Establecimientos', 'establishments.manage', 'admin.establishments.index'],
        ['Parámetros institucionales', 'settings.manage', 'admin.settings.index'],
    ],
    'Cuenta' => [
        ['Mi perfil', 'profile.read', 'modules.profile.index'],
    ],
];

@endphp

<div class="app-shell min-h-screen">
    <!-- Barra Lateral de Navegación -->
    <aside class="fixed inset-y-0 left-0 z-40 hidden w-56 flex-col bg-[#0b2545] text-white shadow-xl lg:static lg:flex lg:w-auto" data-sidebar>
        <div class="border-b border-white/10 px-5 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                        <span class="grid size-8 place-items-center rounded-lg bg-sky-500 text-sm font-black text-slate-950">G</span>
                        <span class="text-2xl font-black tracking-tight text-white">GIATEP</span>
                    </a>
                </div>
                <button type="button" class="text-2xl text-slate-400 hover:text-white lg:hidden" data-menu-close aria-label="Cerrar menú">×</button>
            </div>
        </div>

        <nav class="min-h-0 flex-1 overflow-y-auto p-4" aria-label="Navegación principal">
            @foreach($groups as $group => $items)
                @php
                    $visible = collect($items)->filter(fn($item) => auth()->user()->hasPermission($item[1]));
                @endphp
                @if($visible->isNotEmpty())
                    <section class="mb-5">
                        <p class="mb-2 px-3 text-[10px] font-black uppercase tracking-[.18em] text-sky-400">{{ $group }}</p>
                        <ul class="grid gap-1">
                            @foreach($visible as [$label, $permission, $routeName])
                                @php
                                    $icon = match ($label) {
                                        'Inicio' => '⌂', 'Casos' => '▣', 'Investigación' => '◉',
                                        'Revisiones y validaciones' => '✓', 'Observaciones' => '◌',
                                        'Medidas y planes de acción' => '▤', 'Dashboards y estadísticas' => '▥',
                                        'Reportes e informes' => '▧', 'Mi perfil' => '●', default => '•',
                                    };
                                @endphp
                                <li>
                                    <a href="{{ route($routeName) }}" class="nav-link flex items-center justify-between {{ request()->routeIs($routeName) ? 'nav-link-active' : '' }}">
                                        <span class="flex items-center gap-2.5"><span class="w-4 text-center text-sm text-sky-200" aria-hidden="true">{{ $icon }}</span>{{ $label }}</span>
                                        @if(request()->routeIs($routeName))
                                            <span class="size-1.5 rounded-full bg-white"></span>
                                        @endif
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif
            @endforeach
        </nav>

        <div class="border-t border-white/10 bg-slate-950/60 p-4">
            <div class="rounded-lg border border-white/10 bg-white/5 p-2.5">
                <p class="text-[10px] font-bold uppercase tracking-wider text-sky-300">Establecimientos autorizados</p>
                <p class="mt-1 text-xs font-semibold leading-relaxed text-slate-200">
                    {{ auth()->user()->establishments->map(fn($item) => $item->type . ' ' . $item->name)->join(' · ') ?: 'Red Comunal Completa (APS Los Ángeles)' }}
                </p>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="mt-3">
                @csrf
                <button class="w-full rounded-xl border border-white/15 px-4 py-2.5 text-center text-xs font-bold text-slate-300 transition hover:border-red-400 hover:bg-red-500/20 hover:text-white">
                    Cerrar sesión
                </button>
            </form>
        </div>
    </aside>

    <!-- Área de Contenido Principal -->
    <div class="flex min-w-0 flex-col">
        <header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-slate-200 bg-white/90 px-4 backdrop-blur lg:px-8">
            <div class="flex items-center gap-3">
                <button type="button" class="btn-secondary lg:hidden" data-menu-toggle aria-expanded="false">
                    ☰ Menú
                </button>
            </div>
            <div class="ml-auto flex items-center gap-3">
                <div class="text-right">
                    <p class="text-sm font-black text-slate-900">{{ auth()->user()->name }}</p>
                    <p class="text-xs font-bold text-sky-700">
                        {{ auth()->user()->roles->pluck('name')->join(' · ') }}
                        @if(auth()->user()->job_title)
                            <span class="font-normal text-slate-500">({{ auth()->user()->job_title }})</span>
                        @endif
                    </p>
                </div>
                <div class="grid size-10 place-items-center rounded-xl bg-gradient-to-br from-blue-700 to-sky-600 font-bold text-white shadow-sm">
                    {{ mb_substr(auth()->user()->name, 0, 1) }}
                </div>
            </div>
        </header>

        <main class="flex-1 p-4 sm:p-6 lg:p-8">
            @if(session('demo_status'))
                <div class="mb-6 flex items-start justify-between gap-3 rounded-2xl border border-sky-200 bg-sky-50 p-4 text-sm font-semibold text-sky-950 shadow-sm" role="status">
                    <div class="flex items-center gap-2.5">
                        <span class="text-lg text-sky-600">ℹ</span>
                        <span>{{ session('demo_status') }}</span>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" class="text-slate-400 hover:text-slate-600">×</button>
                </div>
            @endif

            {{ $slot }}
        </main>
    </div>
</div>

@stack('scripts')
</body>
</html>
