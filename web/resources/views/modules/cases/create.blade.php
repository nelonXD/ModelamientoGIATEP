<x-layouts.app title='Registrar caso · GIATEP'>
    <div class='mx-auto max-w-6xl' data-case-wizard data-list-url='{{ route('modules.cases.index') }}'>
        <div class='flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between'>
            <div><a href='{{ route('modules.cases.index') }}' class='text-sm font-bold text-blue-800 hover:text-blue-950'>← Volver a casos</a><p class='eyebrow mt-5'>Nuevo expediente</p><h1 class='mt-2 text-3xl font-black tracking-tight text-slate-950'>Registrar un caso</h1><p class='mt-2 max-w-2xl text-sm leading-6 text-slate-600'>Completa la información en cuatro pasos. Los casos ficticios se guardarán solo en este navegador.</p></div>
            <span class='status bg-sky-100 text-sky-800'>Borrador de diseño</span>
        </div>
        <nav class='card mt-6 p-4' aria-label='Progreso del registro'>
            <ol class='grid gap-3 sm:grid-cols-4'>
                @foreach([1 => ['Empleador', 'Datos institucionales'], 2 => ['Persona accidentada', 'Identificación'], 3 => ['Relato y análisis', 'Circunstancias e IA'], 4 => ['Recopilación', 'Revisión final']] as $number => [$name, $detail])
                    <li><button type='button' class='flex w-full items-center gap-3 rounded-xl border border-transparent p-2 text-left transition' data-step-trigger='{{ $number }}'><span class='flex size-9 shrink-0 items-center justify-center rounded-full bg-slate-200 text-sm font-black text-slate-600' data-step-number>{{ $number }}</span><span><strong class='block text-sm text-slate-900'>{{ $name }}</strong><small class='text-xs text-slate-500'>{{ $detail }}</small></span></button></li>
                @endforeach
            </ol>
        </nav>
        <form class='mt-6' data-case-form novalidate>
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
