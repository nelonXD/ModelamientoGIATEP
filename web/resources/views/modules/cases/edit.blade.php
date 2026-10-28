<x-layouts.app title='Editar caso · GIATEP'>
    <div class='mx-auto max-w-6xl space-y-6'>
        <header class='border-b border-slate-200 pb-5'>
            <a href='{{ route('modules.cases.index') }}' class='inline-flex items-center gap-1.5 text-xs font-bold text-sky-700 hover:text-sky-900'>← Volver a casos</a>
            <p class='mt-3 text-xs font-bold uppercase tracking-wider text-sky-700'>Actualización de antecedentes · Ley 16.744</p>
            <h1 class='mt-1 text-2xl font-black tracking-tight text-slate-950 sm:text-3xl'>Editar caso {{ $case->folio }}</h1>
            <p class='mt-1 text-sm text-slate-600'>Modifique los antecedentes registrados y guarde los cambios del expediente.</p>
        </header>

        @if($errors->any())
            <div class='rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800' role='alert'>
                <strong>No se pudo actualizar el caso.</strong>
                <ul class='mt-2 list-inside list-disc text-xs'>
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form method='POST' action='{{ route('modules.cases.update', ['case' => $case->folio]) }}' class='space-y-6'>
            @csrf
            @method('PATCH')

            <section class='card p-6'>
                <h2 class='text-lg font-black text-slate-950'>Datos del empleador</h2>
                <div class='mt-5 grid gap-4 md:grid-cols-2'>
                    <label><span class='label'>Nombre o razón social *</span><input class='input' name='employer_name' value='{{ old('employer_name', $case->employer_name) }}' required></label>
                    <label><span class='label'>RUT de la empresa *</span><input class='input font-mono' name='employer_rut' value='{{ old('employer_rut', $case->employer_rut) }}' required></label>
                    <label class='md:col-span-2'><span class='label'>Dirección *</span><input class='input' name='employer_address' value='{{ old('employer_address', $case->employer_address) }}' required></label>
                    <label><span class='label'>Comuna *</span><select class='input' name='employer_commune' required><option value=''>Seleccionar</option>@foreach($catalogs->get('commune', collect()) as $option)<option value='{{ $option->value }}' @selected(old('employer_commune', $case->employer_commune) === $option->value)>{{ $option->label }}</option>@endforeach</select></label>
                    <label><span class='label'>Teléfono</span><input class='input' name='employer_phone' value='{{ old('employer_phone', $case->employer_phone) }}'></label>
                    <label><span class='label'>Actividad económica *</span><input class='input' name='economic_activity' value='{{ old('economic_activity', $case->economic_activity) }}' required></label>
                    <label><span class='label'>Tipo de empresa *</span><select class='input' name='company_type' required><option value=''>Seleccionar</option>@foreach($catalogs->get('company_type', collect()) as $option)<option value='{{ $option->value }}' @selected(old('company_type', $case->company_type) === $option->value)>{{ $option->label }}</option>@endforeach</select></label>
                    <label><span class='label'>Organismo administrador *</span><select class='input' name='administrator' required><option value=''>Seleccionar</option>@foreach($catalogs->get('administrator', collect()) as $option)<option value='{{ $option->value }}' @selected(old('administrator', $case->administrator) === $option->value)>{{ $option->label }}</option>@endforeach</select></label>
                    <label><span class='label'>Establecimiento *</span><select class='input' name='establishment_id' required><option value=''>Seleccionar</option>@foreach($establishments as $establishment)<option value='{{ $establishment->id }}' @selected((string) old('establishment_id', $case->establishment_id) === (string) $establishment->id)>{{ $establishment->name }}</option>@endforeach</select></label>
                </div>
            </section>

            <section class='card p-6'>
                <h2 class='text-lg font-black text-slate-950'>Datos de la persona afectada</h2>
                <div class='mt-5 grid gap-4 md:grid-cols-2'>
                    <label><span class='label'>Nombre completo *</span><input class='input' name='worker_name' value='{{ old('worker_name', $case->worker_name) }}' required></label>
                    <label><span class='label'>RUT o RUN *</span><input class='input font-mono' name='worker_rut' value='{{ old('worker_rut', $case->worker_rut) }}' required></label>
                    <label class='md:col-span-2'><span class='label'>Dirección particular</span><input class='input' name='worker_address' value='{{ old('worker_address', $case->worker_address) }}'></label>
                    <label><span class='label'>Comuna</span><select class='input' name='worker_commune'><option value=''>Seleccionar</option>@foreach($catalogs->get('commune', collect()) as $option)<option value='{{ $option->value }}' @selected(old('worker_commune', $case->worker_commune) === $option->value)>{{ $option->label }}</option>@endforeach</select></label>
                    <label><span class='label'>Teléfono</span><input class='input' name='phone' value='{{ old('phone', $case->worker_phone) }}'></label>
                    <label><span class='label'>Sexo</span><select class='input' name='worker_sex'><option value=''>Seleccionar</option>@foreach($catalogs->get('worker_sex', collect()) as $option)<option value='{{ $option->value }}' @selected(old('worker_sex', $case->worker_sex) === $option->value)>{{ $option->label }}</option>@endforeach</select></label>
                    <label><span class='label'>Fecha de nacimiento</span><input class='input' type='date' name='birth_date' value='{{ old('birth_date', $case->birth_date?->format('Y-m-d')) }}'></label>
                    <label><span class='label'>Nacionalidad</span><select class='input' name='nationality'><option value=''>Seleccionar</option>@foreach($catalogs->get('nationality', collect()) as $option)<option value='{{ $option->value }}' @selected(old('nationality', $case->nationality) === $option->value)>{{ $option->label }}</option>@endforeach</select></label>
                    <label><span class='label'>Profesión u oficio *</span><input class='input' name='job_title' value='{{ old('job_title', $case->job_title) }}' required></label>
                    <label><span class='label'>Unidad o centro de desempeño *</span><input class='input' name='workplace' value='{{ old('workplace', $case->workplace) }}' required></label>
                    <label><span class='label'>Tipo de contrato</span><select class='input' name='contract_type'><option value=''>Seleccionar</option>@foreach($catalogs->get('contract_type', collect()) as $option)<option value='{{ $option->value }}' @selected(old('contract_type', $case->contract_type) === $option->value)>{{ $option->label }}</option>@endforeach</select></label>
                    <label><span class='label'>Categoría ocupacional</span><select class='input' name='occupational_category'><option value=''>Seleccionar</option>@foreach($catalogs->get('occupational_category', collect()) as $option)<option value='{{ $option->value }}' @selected(old('occupational_category', $case->occupational_category) === $option->value)>{{ $option->label }}</option>@endforeach</select></label>
                    <label><span class='label'>Fecha de ingreso</span><input class='input' type='date' name='hire_date' value='{{ old('hire_date', $case->hire_date?->format('Y-m-d')) }}'></label>
                </div>
            </section>

            <section class='card p-6'>
                <h2 class='text-lg font-black text-slate-950'>Antecedentes del caso</h2>
                <div class='mt-5 grid gap-4 md:grid-cols-2'>
                    <label><span class='label'>Tipo de caso *</span><select class='input' name='case_type' required>@unless($catalogs->get('case_type', collect())->contains('value', $case->case_type))<option value='{{ $case->case_type }}' selected>{{ $case->case_type }}</option>@endunless @foreach($catalogs->get('case_type', collect()) as $option)<option value='{{ $option->value }}' @selected(old('case_type', $case->case_type) === $option->value)>{{ $option->label }}</option>@endforeach</select></label>
                    <label><span class='label'>Fecha del evento *</span><input class='input' type='date' name='event_date' value='{{ old('event_date', $case->event_date?->format('Y-m-d')) }}' required></label>
                    <label><span class='label'>Hora</span><input class='input' type='time' name='event_time' value='{{ old('event_time', $case->event_time ? substr((string) $case->event_time, 0, 5) : '') }}'></label>
                    <label><span class='label'>Lugar</span><input class='input' name='event_location' value='{{ old('event_location', $case->event_location) }}'></label>
                    <label class='md:col-span-2'><span class='label'>Relato *</span><textarea class='input min-h-36' name='narrative' required>{{ old('narrative', $case->narrative) }}</textarea></label>
                    <label class='md:col-span-2'><span class='label'>Testigos o antecedentes conocidos</span><textarea class='input min-h-24' name='witnesses'>{{ old('witnesses', $case->witnesses) }}</textarea></label>
                </div>
            </section>

            <div class='flex justify-end gap-3'>
                <a href='{{ route('modules.cases.index') }}' class='btn-secondary'>Cancelar</a>
                <button class='btn-primary' type='submit'>Guardar cambios</button>
            </div>
        </form>
    </div>
@push('scripts')
    <script>
        if (window.location.hash === '#evidencias') {
            window.location.replace(@js(route('modules.cases.evidence.index', ['case' => $case->folio])));
        }
    </script>
@endpush
</x-layouts.app>
