<section class='card hidden p-6 sm:p-8' data-step-panel='2'>
    <div class='flex items-start gap-4'>
        <span class='flex size-10 shrink-0 items-center justify-center rounded-xl bg-[#0b2545] font-black text-white text-sm'>2</span>
        <div>
            <p class='text-xs font-bold uppercase tracking-wider text-sky-700'>Identificación de la Persona Afectada</p>
            <h2 class='mt-0.5 text-xl sm:text-2xl font-black text-slate-950'>Datos de la persona accidentada</h2>
            <p class='mt-1 text-xs sm:text-sm text-slate-600'>Antecedentes personales, contractuales y laborales del trabajador o trabajadora.</p>
        </div>
    </div>
    <div class='mt-6 space-y-6'>
        <div class='rounded-xl border border-slate-200 bg-slate-50/50 p-5'>
            <h3 class='text-xs font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200 pb-2 mb-4'>Identificación y contacto</h3>
            <div class='grid gap-4 md:grid-cols-2'>
                <label>
                    <span class='label text-xs'>Nombre completo <span class='text-red-700'>*</span></span>
                    <input class='input text-sm' name='worker_name' placeholder='Nombres y apellidos completos' data-summary='worker_name' required>
                </label>
                <label>
                    <span class='label text-xs'>RUT o RUN <span class='text-red-700'>*</span></span>
                    <input class='input text-sm font-mono' name='worker_rut' placeholder='12.345.678-5' data-summary='worker_rut' required>
                </label>
                <label class='md:col-span-2'>
                    <span class='label text-xs'>Dirección particular</span>
                    <input class='input text-sm' name='worker_address' placeholder='Calle, número, departamento, población o villa'>
                </label>
                <label>
                    <span class='label text-xs'>Comuna</span>
                    <select class='input text-sm' name='worker_commune'>
                        <option value=''>Seleccionar comuna</option>
                        @foreach($catalogs->get('commune', collect()) as $commune)
                            <option value='{{ $commune->value }}' @selected(old('worker_commune') === $commune->value)>{{ $commune->label }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    <span class='label text-xs'>Teléfono de contacto</span>
                    <input class='input text-sm' type='tel' name='phone' placeholder='+56 9 1234 5678'>
                </label>
                <label>
                    <span class='label text-xs'>Sexo</span>
                    <select class='input text-sm' name='worker_sex'>
                        <option value=''>Seleccionar</option>
                        @foreach($catalogs->get('worker_sex', collect()) as $option)
                            <option value='{{ $option->value }}' @selected(old('worker_sex') === $option->value)>{{ $option->label }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    <span class='label text-xs'>Fecha de nacimiento</span>
                    <input class='input text-sm' type='date' name='birth_date' data-worker-birth-date>
                </label>
                <label>
                    <span class='label text-xs'>Edad</span>
                    <input class='input text-sm' type='number' name='worker_age' min='0' readonly data-worker-age>
                </label>
                <label>
                    <span class='label text-xs'>Nacionalidad</span>
                    <select class='input text-sm' name='nationality'>
                        <option value=''>Seleccionar nacionalidad</option>
                        @foreach($catalogs->get('nationality', collect()) as $nationality)
                            <option value='{{ $nationality->value }}' @selected(old('nationality') === $nationality->value)>{{ $nationality->label }}</option>
                        @endforeach
                    </select>
                </label>
            </div>
        </div>

        <div class='rounded-xl border border-slate-200 bg-slate-50/50 p-5'>
            <h3 class='text-xs font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200 pb-2 mb-4'>Antecedentes laborales y contractuales</h3>
            <div class='grid gap-4 md:grid-cols-2'>
                <label class='md:col-span-2'>
                    <span class='label text-xs'>Pertenencia a pueblo originario</span>
                    <select class='input text-sm' name='indigenous_people'>
                        <option value=''>Seleccionar</option>
                        @foreach($catalogs->get('indigenous_people', collect()) as $people)
                            <option value='{{ $people->value }}' @selected(old('indigenous_people') === $people->value)>{{ $people->label }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    <span class='label text-xs'>Profesión u oficio <span class='text-red-700'>*</span></span>
                    <input class='input text-sm' name='job_title' placeholder='Cargo, función o estamento' data-summary='job_title' required>
                </label>
                <label>
                    <span class='label text-xs'>Unidad o centro de desempeño <span class='text-red-700'>*</span></span>
                    <input class='input text-sm' name='workplace' placeholder='Ej.: Box Dental, Vacunatorio, Urgencia' data-summary='workplace' required>
                </label>
                <label>
                    <span class='label text-xs'>Fecha de ingreso al centro</span>
                    <input class='input text-sm' type='date' name='hire_date'>
                </label>
                <label>
                    <span class='label text-xs'>Antigüedad</span>
                    <input class='input text-sm' name='company_tenure' placeholder='Ej.: 2 años y 4 meses'>
                </label>
                <label>
                    <span class='label text-xs'>Tipo de contrato</span>
                    <select class='input text-sm' name='contract_type'>
                        <option value=''>Seleccionar</option>
                        @foreach($catalogs->get('contract_type', collect()) as $contract)
                            <option value='{{ $contract->value }}' @selected(old('contract_type') === $contract->value)>{{ $contract->label }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    <span class='label text-xs'>Tipo de ingreso</span>
                    <select class='input text-sm' name='income_type'>
                        <option value=''>Seleccionar</option>
                        @foreach($catalogs->get('income_type', collect()) as $income)
                            <option value='{{ $income->value }}' @selected(old('income_type') === $income->value)>{{ $income->label }}</option>
                        @endforeach
                    </select>
                </label>
                <label class='md:col-span-2'>
                    <span class='label text-xs'>Categoría ocupacional</span>
                    <select class='input text-sm' name='occupational_category'>
                        <option value=''>Seleccionar</option>
                        @foreach($catalogs->get('occupational_category', collect()) as $category)
                            <option value='{{ $category->value }}' @selected(old('occupational_category') === $category->value)>{{ $category->label }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    <span class='label text-xs'>Jornada</span>
                    <select class='input text-sm' name='work_schedule'>
                        @foreach($catalogs->get('work_schedule', collect()) as $schedule)
                            <option value='{{ $schedule->value }}' @selected(old('work_schedule') === $schedule->value)>{{ $schedule->label }}</option>
                        @endforeach
                    </select>
                </label>
            </div>
        </div>
    </div>
    <div class='mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-between border-t border-slate-100 pt-5'>
        <button type='button' class='btn-secondary text-xs py-2.5 px-4' data-previous-step>← Volver</button>
        <button type='button' class='btn-primary inline-flex text-xs py-2.5 px-5' data-next-step>Continuar a antecedentes del caso →</button>
    </div>
</section>
