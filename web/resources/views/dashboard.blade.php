<x-layouts.app title='Inicio · GIATEP'>
    <section class='rounded-2xl bg-gradient-to-br from-blue-900 to-sky-700 p-6 text-white sm:p-10'>
        <p class='text-sm font-bold uppercase tracking-widest text-sky-200'>{{ $dashboardProfile['label'] }}</p>
        <h1 class='mt-3 text-3xl font-bold sm:text-4xl'>Hola, {{ $user->name }}</h1>
        <p class='mt-3 max-w-2xl text-sky-100'>{{ $dashboardProfile['message'] }}</p>
    </section>

    <section class='mt-6 grid gap-4 md:grid-cols-3'>
        <div class='card p-6'><p class='eyebrow'>Roles activos</p><p class='mt-3 text-lg font-bold'>{{ $user->roles->pluck('name')->join(', ') }}</p></div>
        <div class='card p-6'><p class='eyebrow'>Establecimientos</p><p class='mt-3 text-lg font-bold'>{{ $user->establishments->count() }}</p><p class='mt-1 text-sm text-slate-500'>Solo verás información dentro de este alcance.</p></div>
        <div class='card p-6'><p class='eyebrow'>Estado</p><p class='mt-3 inline-flex rounded-full bg-emerald-100 px-3 py-1 text-sm font-bold text-emerald-800'>Cuenta activa</p></div>
    </section>

    <section class='mt-6'>
        <div><p class='eyebrow'>Accesos rápidos</p><h2 class='mt-2 text-2xl font-bold'>Actividad autorizada</h2><p class='mt-2 text-slate-600'>Los accesos se generan desde los permisos de tus roles. No se muestran cifras clínicas ni estadísticas simuladas.</p></div>
        @if($availableActions->isEmpty())
            <div class='card mt-5 p-6 text-slate-600'>No tienes módulos operativos asignados. Puedes consultar tu perfil desde el menú principal.</div>
        @else
            <div class='mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-3'>
                @foreach($availableActions as $action)
                    <a href='{{ route($action['route']) }}' class='card group p-6 transition hover:-translate-y-0.5 hover:border-sky-300 hover:shadow-md'><div class='flex items-start justify-between gap-4'><div><h3 class='text-lg font-bold text-slate-950 group-hover:text-blue-800'>{{ $action['title'] }}</h3><p class='mt-2 text-sm leading-6 text-slate-600'>{{ $action['description'] }}</p></div><span class='text-xl text-sky-700' aria-hidden='true'>→</span></div></a>
                @endforeach
            </div>
        @endif
        <div class='mt-6 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900'><strong>Decisión provisional:</strong> las estadísticas globales y exportaciones están disponibles solo para Prevencionistas y Alta Dirección, pendiente de validación de la matriz RBAC.</div>
    </section>
</x-layouts.app>
