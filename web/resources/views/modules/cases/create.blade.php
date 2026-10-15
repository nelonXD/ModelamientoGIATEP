<x-layouts.app title='Registrar caso · GIATEP'>
    <div class='mx-auto max-w-6xl space-y-6' data-case-wizard data-list-url='{{ route('modules.cases.index') }}'>
        <div class='flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between border-b border-slate-200 pb-5'>
            <div>
                <a href='{{ route('modules.cases.index') }}' class='inline-flex items-center gap-1.5 text-xs font-bold text-sky-700 hover:text-sky-900'>
                    <span aria-hidden='true'>←</span> Volver a casos
                </a>
                <p class='text-xs font-bold uppercase tracking-wider text-sky-700 mt-3'>Notificación y apertura · Ley 16.744</p>
                <h1 class='mt-1 text-2xl sm:text-3xl font-black tracking-tight text-slate-950'>Registrar un caso</h1>
                <p class='mt-1 text-sm text-slate-600'>Formulario oficial de notificación inicial y captura de antecedentes para accidentes del trabajo y enfermedades profesionales.</p>
            </div>
            <span class='inline-flex rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-900 border border-blue-200'>
                Apertura oficial (DIAT / DIEP)
            </span>
        </div>

        <nav class='card p-3 shadow-sm' aria-label='Progreso del registro'>
            <ol class='grid gap-2 sm:grid-cols-4'>
                @foreach([
                    1 => ['Datos del empleador', 'Identificación institucional'],
                    2 => ['Datos de la persona accidentada', 'Identificación y cargo'],
                    3 => ['Antecedentes del caso', 'Circunstancias y evidencias'],
                    4 => ['Recopilación del caso', 'Revisión final']
                ] as $number => [$name, $detail])
                    <li>
                        <button type='button' class='flex w-full items-center gap-3 rounded-xl border border-transparent p-2.5 text-left transition hover:bg-slate-50' data-step-trigger='{{ $number }}'>
                            <span class='flex size-8 shrink-0 items-center justify-center rounded-lg bg-slate-200 text-xs font-black text-slate-700' data-step-number>{{ $number }}</span>
                            <span class='min-w-0'>
                                <strong class='block text-xs font-bold text-slate-900 truncate'>{{ $name }}</strong>
                                <small class='block text-[11px] text-slate-500 truncate'>{{ $detail }}</small>
                            </span>
                        </button>
                    </li>
                @endforeach
            </ol>
        </nav>

        <form class='space-y-6' method='POST' action='{{ route('modules.cases.store') }}' enctype='multipart/form-data' data-case-form novalidate>
            @csrf
            @include('modules.cases.partials.step-employer')
            @include('modules.cases.partials.step-injured-worker')
            @include('modules.cases.partials.step-narrative-ai')
            @include('modules.cases.partials.step-review')
        </form>
    </div>
    @push('scripts')
        @include('modules.cases.partials.wizard-script')
    @endpush
</x-layouts.app>
