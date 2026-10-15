<x-layouts.app title='Inicio · GIATEP'>
    <div class='space-y-6'>
        <section class='rounded-2xl bg-gradient-to-r from-[#0b2545] to-[#133e7c] p-6 text-white sm:p-8 shadow-sm'>
            <div class='flex flex-wrap items-center justify-between gap-4'>
                <div>
                    <span class='inline-block rounded bg-sky-500/25 px-2.5 py-0.5 text-xs font-bold uppercase tracking-wider text-sky-200 border border-sky-400/30'>
                        {{ $dashboardProfile['label'] }}
                    </span>
                    <h1 class='mt-2 text-2xl sm:text-3xl font-black tracking-tight'>Bienvenido(a), {{ $user->name }}</h1>
                    <p class='mt-1 text-sm text-sky-100/90 max-w-2xl'>{{ $dashboardProfile['message'] }}</p>
                </div>
                <div class='text-right hidden sm:block'>
                    <span class='text-xs text-sky-200 block font-semibold'>Dirección Comunal de Salud</span>
                    <span class='text-[11px] text-white/60 block'>Municipalidad de Los Ángeles</span>
                </div>
            </div>
        </section>

        <section class='grid gap-4 md:grid-cols-3'>
            <div class='card p-5'>
                <p class='text-xs font-bold uppercase tracking-wider text-slate-500'>Rol institucional</p>
                <p class='mt-2 text-base font-bold text-slate-900'>{{ $user->roles->pluck('name')->join(', ') }}</p>
            </div>
            <div class='card p-5'>
                <p class='text-xs font-bold uppercase tracking-wider text-slate-500'>Establecimientos con cobertura</p>
                <p class='mt-2 text-base font-bold text-slate-900'>{{ $user->establishments->count() }} {{ $user->establishments->count() === 1 ? 'centro asignado' : 'centros asignados' }}</p>
                <p class='mt-1 text-xs text-slate-500 truncate'>{{ $user->establishments->pluck('name')->join(' · ') ?: 'Red Comunal de Salud' }}</p>
            </div>
            <div class='card p-5'>
                <p class='text-xs font-bold uppercase tracking-wider text-slate-500'>Estado de habilitación</p>
                <p class='mt-2'>
                    <span class='inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-800 border border-emerald-200'>
                        <span class='size-1.5 rounded-full bg-emerald-600'></span>
                        Sesión autorizada
                    </span>
                </p>
            </div>
        </section>

        <section class='space-y-4'>
            <div>
                <h2 class='text-xl font-black text-slate-950'>Módulos y funciones autorizadas</h2>
                <p class='text-xs text-slate-600 mt-0.5'>Seleccione un módulo operativo para gestionar expedientes y registros institucionales.</p>
            </div>
            @if($availableActions->isEmpty())
                <div class='card p-6 text-sm text-slate-600 text-center'>
                    No cuenta con módulos operativos asignados actualmente. Puede consultar su perfil desde la barra lateral.
                </div>
            @else
                <div class='grid gap-4 md:grid-cols-2 xl:grid-cols-3'>
                    @foreach($availableActions as $action)
                        <a href='{{ route($action['route']) }}' class='card group p-5 transition hover:-translate-y-0.5 hover:border-sky-300 hover:shadow-md'>
                            <div class='flex items-start justify-between gap-3'>
                                <div>
                                    <h3 class='text-base font-bold text-slate-950 group-hover:text-blue-900'>{{ $action['title'] }}</h3>
                                    <p class='mt-1 text-xs leading-relaxed text-slate-600'>{{ $action['description'] }}</p>
                                </div>
                                <span class='text-slate-400 group-hover:text-blue-800 text-base font-bold' aria-hidden='true'>→</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
</x-layouts.app>
