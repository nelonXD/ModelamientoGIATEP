<section class='card p-6 sm:p-8' data-step-panel='1'>
    <div class='flex items-start gap-4'>
        <span class='flex size-10 shrink-0 items-center justify-center rounded-xl bg-[#0b2545] font-black text-white text-sm'>1</span>
        <div>
            <p class='text-xs font-bold uppercase tracking-wider text-sky-700'>Identificación de la Entidad Empleadora</p>
            <h2 class='mt-0.5 text-xl sm:text-2xl font-black text-slate-950'>Datos del empleador</h2>
            <p class='mt-1 text-xs sm:text-sm text-slate-600'>Identificación institucional de la entidad empleadora y centro de trabajo de ocurrencia.</p>
        </div>
    </div>
    <div class='mt-6 space-y-6'>
        <div class='rounded-xl border border-slate-200 bg-slate-50/50 p-5'>
            <h3 class='text-xs font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200 pb-2 mb-4'>Identificación institucional</h3>
            <div class='grid gap-4 md:grid-cols-2'>
                <label>
                    <span class='label text-xs'>Nombre o razón social <span class='text-red-700'>*</span></span>
                    <input class='input text-sm' name='employer_name' value='{{ old('employer_name') }}' data-summary='employer_name' required>
                </label>
                <label>
                    <span class='label text-xs'>RUT de la empresa <span class='text-red-700'>*</span></span>
                    <input class='input text-sm font-mono' name='employer_rut' value='{{ old('employer_rut') }}' placeholder='RUT del empleador' data-summary='employer_rut' required>
                </label>
                <label class='md:col-span-2'>
                    <span class='label text-xs'>Dirección de la empresa <span class='text-red-700'>*</span></span>
                    <input class='input text-sm' name='employer_address' value='{{ old('employer_address') }}' placeholder='Calle, número, oficina' data-summary='employer_address' required>
                </label>
                <label>
                    <span class='label text-xs'>Comuna <span class='text-red-700'>*</span></span>
                    <select class='input text-sm' name='employer_commune' required>
                        <option value=''>Seleccionar comuna</option>
                        @foreach($catalogs->get('commune', collect()) as $commune)
                            <option value='{{ $commune->value }}' @selected(old('employer_commune') === $commune->value)>{{ $commune->label }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    <span class='label text-xs'>Número de teléfono</span>
                    <input class='input text-sm' type='tel' name='employer_phone' value='{{ old('employer_phone') }}' placeholder='Teléfono del empleador'>
                </label>
                <label>
                    <span class='label text-xs'>Actividad económica <span class='text-red-700'>*</span></span>
                    <input class='input text-sm' name='economic_activity' value='{{ old('economic_activity') }}' placeholder='Actividad económica' required>
                </label>
                <label>
                    <span class='label text-xs'>Tipo de empresa <span class='text-red-700'>*</span></span>
                    <select class='input text-sm' name='company_type' data-company-type required>
                        <option value=''>Seleccionar</option>
                        @foreach($catalogs->get('company_type', collect()) as $option)
                            <option value='{{ $option->value }}' @selected(old('company_type') === $option->value)>{{ $option->label }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    <span class='label text-xs'>Organismo administrador <span class='text-red-700'>*</span></span>
                    <select class='input text-sm' name='administrator' data-summary='administrator' required>
                        <option value=''>Seleccionar</option>
                        @foreach($catalogs->get('administrator', collect()) as $option)
                            <option value='{{ $option->value }}' @selected(old('administrator') === $option->value)>{{ $option->label }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    <span class='label text-xs'>Establecimiento o centro de trabajo <span class='text-red-700'>*</span></span>
                    <select class='input text-sm' name='establishment_id' data-summary='establishment_id' required>
                        <option value=''>Seleccionar establecimiento</option>
                        @foreach($establishments as $est)
                            <option value='{{ $est->id }}' @selected((string) old('establishment_id') === (string) $est->id)>{{ $est->name }} ({{ $est->type }})</option>
                        @endforeach
                    </select>
                    @if($establishments->isEmpty())
                        <span class='mt-1 block text-xs text-amber-800'>No hay establecimientos activos en la base de datos. Solicita que registren uno antes de crear el caso.</span>
                    @endif
                </label>
            </div>
        </div>
    </div>
    <div class='mt-6 flex justify-end border-t border-slate-100 pt-5'>
        <button type='button' class='btn-primary inline-flex text-xs py-2.5 px-5' data-next-step>Continuar a persona accidentada →</button>
    </div>
</section>
