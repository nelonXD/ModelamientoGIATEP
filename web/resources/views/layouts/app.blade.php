<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='utf-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <meta name='csrf-token' content='{{ csrf_token() }}'>
    <title>{{ $title ?? 'GIATEP · Dirección Comunal de Salud' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class='bg-slate-50 text-slate-900 antialiased'>
@php
$navSections = [
    'Operación y Casos' => [
        ['Inicio', 'dashboard.view', route('dashboard')],
        ['Inicio administrativo', 'admin.dashboard', route('dashboard')],
        ['Casos', 'cases.read', route('modules.cases.index')],
        ['Investigaciones', 'investigations.read', route('modules.investigations.index')],
        ['Revisiones y validaciones', 'reviews.read', route('modules.reviews.index')],
        ['Medidas y planes de acción', 'measures.read', route('modules.measures.index')],
        ['Observaciones', 'observations.read', route('modules.observations.index')],
    ],
    'Gestión e Indicadores' => [
        ['Dashboards y estadísticas', 'statistics.read', route('modules.statistics.index')],
        ['Reportes', 'reports.export', route('modules.reports.index')],
    ],
    'Administración' => [
        ['Solicitudes de registro', 'registration-requests.review', route('admin.registration-requests.index')],
        ['Usuarios', 'users.manage', route('admin.users.index')],
        ['Roles y permisos', 'roles.manage', route('admin.roles.index')],
        ['Establecimientos', 'establishments.manage', route('admin.establishments.index')],
        ['Parámetros institucionales', 'settings.manage', route('admin.settings.index')],
        ['Mi perfil', 'profile.read', route('modules.profile.index')],
    ],
];
@endphp
<div class='min-h-screen lg:grid lg:grid-cols-[18.5rem_1fr]'>
    <aside class='fixed inset-y-0 left-0 z-30 hidden w-72 flex-col bg-[#0b2545] text-white shadow-xl lg:static lg:flex lg:w-auto' data-sidebar>
        <div class='border-b border-white/10 px-6 py-5'>
            <div class='flex items-center justify-between'>
                <a href='{{ route('dashboard') }}' class='flex items-center gap-2.5 text-2xl font-black tracking-tight text-white'>
                    <span class='flex size-8 items-center justify-center rounded-lg bg-sky-500 text-sm font-black text-slate-950'>G</span>
                    <span>GIATEP</span>
                </a>
                <button type='button' class='text-2xl text-white/70 hover:text-white lg:hidden' data-menu-close aria-label='Cerrar menú'>×</button>
            </div>
            <p class='mt-1.5 text-[11px] font-bold tracking-wider text-sky-300 uppercase'>Dirección Comunal de Salud</p>
            <p class='text-[10px] text-slate-300'>Municipalidad de Los Ángeles · Ley 16.744</p>
        </div>
        <nav class='flex-1 overflow-y-auto px-4 py-5' aria-label='Navegación principal'>
            <div class='space-y-6'>
                @foreach($navSections as $sectionTitle => $items)
                    @php
                        $visibleItems = collect($items)->filter(fn($item) => auth()->user()->hasPermission($item[1]));
                    @endphp
                    @if($visibleItems->isNotEmpty())
                        <div>
                            <p class='px-3 text-[10px] font-extrabold tracking-wider text-sky-300/80 uppercase'>{{ $sectionTitle }}</p>
                            <ul class='mt-2 space-y-1'>
                                @foreach($visibleItems as [$label, $permission, $url])
                                    <li>
                                        <a href='{{ $url }}' class='flex items-center rounded-lg px-3 py-2 text-sm font-semibold transition {{ url()->current() === $url ? 'bg-sky-600 font-bold text-white shadow-sm' : 'text-slate-200 hover:bg-white/10 hover:text-white' }}'>
                                            {{ $label }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                @endforeach
            </div>
        </nav>
        <div class='border-t border-white/10 bg-[#081c36] p-4 text-xs'>
            <div class='rounded-lg bg-white/5 p-3'>
                <p class='text-[10px] font-bold tracking-wider text-sky-300 uppercase'>Establecimiento asignado</p>
                <p class='mt-1 font-semibold text-slate-200 truncate'>
                    {{ auth()->user()->establishments->pluck('name')->join(' · ') ?: 'Red Comunal de Salud' }}
                </p>
            </div>
            <form method='POST' action='{{ route('logout') }}' class='mt-3'>
                @csrf
                <button class='flex w-full items-center justify-between rounded-lg border border-white/15 px-3 py-2 text-xs font-bold text-slate-200 transition hover:bg-white/10 hover:text-white'>
                    <span>Cerrar sesión</span>
                    <span aria-hidden='true'>↳</span>
                </button>
            </form>
        </div>
    </aside>
    <div class='min-w-0 flex flex-col min-h-screen'>
        <header class='sticky top-0 z-20 flex h-16 items-center justify-between border-b border-slate-200 bg-white/95 px-4 backdrop-blur sm:px-6 lg:px-8'>
            <div class='flex items-center gap-3'>
                <button type='button' class='btn-secondary py-1.5 px-3 text-xs lg:hidden' data-menu-toggle aria-expanded='false'>Menú</button>
                <nav class='hidden sm:flex items-center gap-2 text-xs font-semibold text-slate-500' aria-label='Migas de pan'>
                    <a href='{{ route('dashboard') }}' class='hover:text-blue-900'>GIATEP</a>
                    <span>/</span>
                    <span class='text-slate-800 font-bold'>{{ $title ?? 'Gestión Institucional' }}</span>
                </nav>
            </div>
            <div class='flex items-center gap-4'>
                <div class='text-right'>
                    <p class='text-xs font-black text-slate-900 leading-tight'>{{ auth()->user()->name }}</p>
                    <span class='inline-block rounded bg-sky-100 px-2 py-0.5 text-[10px] font-extrabold text-sky-900'>
                        {{ auth()->user()->roles->pluck('name')->join(' · ') }}
                    </span>
                </div>
            </div>
        </header>
        <main class='flex-1 p-4 sm:p-6 lg:p-8 max-w-[1600px] w-full mx-auto'>
            @if(session('demo_status') || session('status'))
                <div class='mb-6 flex items-start justify-between gap-3 rounded-xl border border-sky-200 bg-sky-50 px-4 py-3.5 text-sm font-semibold text-sky-950 shadow-sm' role='status'>
                    <div class='flex items-center gap-2.5'>
                        <span class='flex size-6 shrink-0 items-center justify-center rounded-full bg-sky-600 text-xs font-bold text-white'>✓</span>
                        <span>{{ session('demo_status') ?? session('status') }}</span>
                    </div>
                    <button type='button' onclick='this.parentElement.remove()' class='text-slate-400 hover:text-slate-600 font-bold text-lg leading-none' aria-label='Cerrar aviso'>×</button>
                </div>
            @endif
            {{ $slot }}
        </main>
        <footer class='border-t border-slate-200 bg-white/70 px-4 py-4 text-xs text-slate-500 sm:px-6 lg:px-8'>
            <div class='max-w-[1600px] mx-auto flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between text-[11px] font-medium'>
                <p>Dirección Comunal de Salud de Los Ángeles · Unidad de Prevención de Riesgos de Salud Ocupacional (UPRSO)</p>
                <p class='text-slate-400'>Ley N° 16.744 · Ley N° 21.180 de Transformación Digital del Estado</p>
            </div>
        </footer>
    </div>
</div>
@stack('scripts')
</body>
</html>
