<!DOCTYPE html><html lang='es'><head><meta charset='utf-8'><meta name='viewport' content='width=device-width, initial-scale=1'><title>{{ $title ?? 'GIATEP' }}</title>@vite(['resources/css/app.css', 'resources/js/app.js'])</head>
<body class='bg-slate-50 text-slate-900 antialiased'>
@php
$items = [
['Inicio', 'dashboard.view', route('dashboard')], ['Inicio administrativo', 'admin.dashboard', route('dashboard')],
['Casos', 'cases.read', route('modules.show', 'casos')], ['Investigaciones', 'investigations.read', route('modules.show', 'investigaciones')],
['Revisiones y validaciones', 'reviews.read', route('modules.show', 'revisiones')], ['Medidas y planes de acción', 'measures.read', route('modules.show', 'medidas')],
['Observaciones', 'observations.read', route('modules.show', 'observaciones')], ['Dashboards y estadísticas', 'statistics.read', route('modules.show', 'estadisticas')],
['Reportes', 'reports.export', route('modules.show', 'reportes')], ['Solicitudes de registro', 'registration-requests.review', route('admin.registration-requests.index')],
['Usuarios', 'users.manage', route('modules.show', 'usuarios')], ['Roles y permisos', 'roles.manage', route('modules.show', 'roles')],
['Establecimientos', 'establishments.manage', route('modules.show', 'establecimientos')], ['Parámetros institucionales', 'settings.manage', route('modules.show', 'parametros')],
['Mi perfil', 'profile.read', route('modules.show', 'perfil')],
];
@endphp
<div class='min-h-screen lg:grid lg:grid-cols-[18rem_1fr]'>
    <aside class='fixed inset-y-0 left-0 z-30 hidden w-72 flex-col bg-blue-950 text-white lg:static lg:flex lg:w-auto' data-sidebar>
        <div class='flex h-20 items-center justify-between border-b border-white/10 px-6'><a href='{{ route('dashboard') }}' class='text-2xl font-black'>GIATEP</a><button type='button' class='text-2xl lg:hidden' data-menu-close aria-label='Cerrar menú'>×</button></div>
        <nav class='flex-1 overflow-y-auto p-4' aria-label='Navegación principal'><ul class='grid gap-1'>@foreach($items as [$label, $permission, $url])@if(auth()->user()->hasPermission($permission))<li><a href='{{ $url }}' class='nav-link {{ url()->current() === $url ? 'nav-link-active' : '' }}'>{{ $label }}</a></li>@endif @endforeach</ul></nav>
        <div class='border-t border-white/10 p-4'><p class='px-3 text-xs font-bold uppercase tracking-wider text-sky-200'>Establecimientos autorizados</p><p class='mt-2 px-3 text-sm text-white/80'>{{ auth()->user()->establishments->map(fn($item) => $item->type.' '.$item->name)->join(' · ') ?: 'Sin establecimiento asignado' }}</p><form method='POST' action='{{ route('logout') }}' class='mt-4'>@csrf<button class='w-full rounded-lg border border-white/20 px-4 py-2 text-left font-bold hover:bg-white/10'>Cerrar sesión</button></form></div>
    </aside>
    <div class='min-w-0'><header class='sticky top-0 z-20 flex h-16 items-center justify-between border-b border-slate-200 bg-white px-4 lg:px-8'><button type='button' class='btn-secondary lg:hidden' data-menu-toggle aria-expanded='false'>Menú</button><div><p class='font-bold'>{{ auth()->user()->name }}</p><p class='text-xs text-slate-500'>{{ auth()->user()->roles->pluck('name')->join(' · ') }}</p></div></header><main class='p-4 sm:p-6 lg:p-8'>{{ $slot }}</main></div>
</div>
</body></html>
