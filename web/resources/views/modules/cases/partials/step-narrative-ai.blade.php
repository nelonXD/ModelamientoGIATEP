<section class='card hidden p-6 sm:p-8' data-step-panel='3'>
    <div class='flex items-start gap-4'>
        <span class='flex size-10 shrink-0 items-center justify-center rounded-xl bg-[#0b2545] font-black text-white text-sm'>3</span>
        <div>
            <p class='text-xs font-bold uppercase tracking-wider text-sky-700'>Circunstancias del Evento</p>
            <h2 class='mt-0.5 text-xl sm:text-2xl font-black text-slate-950'>Antecedentes del caso</h2>
            <p class='mt-1 text-xs sm:text-sm text-slate-600'>Registro de los hechos ocurridos, clasificación oficial y evidencias adjuntas al momento de la notificación.</p>
        </div>
    </div>
    <div class='mt-6 space-y-6'>
        <fieldset class='rounded-xl border border-slate-200 bg-slate-50/50 p-5'>
            <legend class='text-xs font-bold uppercase tracking-wider text-slate-500 mb-2'>Tipo de caso (Clasificación oficial) <span class='text-red-700'>*</span></legend>
            <div class='grid gap-2.5 sm:grid-cols-2 lg:grid-cols-4'>
                @foreach($catalogs->get('case_type', collect()) as $type)
                    <label class='flex cursor-pointer items-center gap-2.5 rounded-lg border border-slate-300 bg-white p-3 text-xs font-bold text-slate-700 transition hover:border-slate-400 has-checked:border-blue-800 has-checked:bg-blue-50 has-checked:text-blue-950'>
                        <input type='radio' name='case_type' value='{{ $type->value }}' class='accent-blue-800' data-case-classification required @checked(old('case_type') === $type->value)>
                        <span>{{ $type->label }}</span>
                    </label>
                @endforeach
            </div>
            <p class='mt-3 hidden text-xs font-semibold text-red-700' data-case-classification-error role='alert'>Seleccione el tipo de caso antes de continuar.</p>
        </fieldset>

        <section class='hidden rounded-xl border border-slate-200 bg-slate-50/50 p-5' data-accident-report aria-hidden='true'>
            <h3 class='text-xs font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200 pb-2 mb-4'>Datos del accidente o incidente</h3>
            <div class='grid gap-4 md:grid-cols-2'>
                <label>
                    <span class='label text-xs'>Fecha del accidente <span class='text-red-700'>*</span></span>
                    <input class='input text-sm' type='date' name='event_date' data-summary='event_date' disabled>
                </label>
                <label>
                    <span class='label text-xs'>Hora del accidente <span class='text-red-700'>*</span></span>
                    <input class='input text-sm' type='time' name='event_time' disabled>
                </label>
                <label>
                    <span class='label text-xs'>Hora de ingreso al trabajo</span>
                    <input class='input text-sm' type='time' name='work_start_time' disabled>
                </label>
                <label>
                    <span class='label text-xs'>Hora de salida del trabajo</span>
                    <input class='input text-sm' type='time' name='work_end_time' disabled>
                </label>
                <label class='md:col-span-2'>
                    <span class='label text-xs'>Lugar específico del accidente <span class='text-red-700'>*</span></span>
                    <input class='input text-sm' name='event_location' placeholder='Box de atención, pasillo, calle o trayecto específico' data-summary='event_location' disabled>
                </label>
                <label>
                    <span class='label text-xs'>¿Qué hacía la persona justo antes del accidente?</span>
                    <textarea class='input text-sm' name='activity_before_event' rows='3' disabled></textarea>
                </label>
                <label>
                    <span class='label text-xs'>Trabajo habitual</span>
                    <textarea class='input text-sm' name='usual_job_description' rows='3' disabled></textarea>
                </label>
                <label class='md:col-span-2'>
                    <span class='label text-xs'>Describa cómo ocurrió el accidente <span class='text-red-700'>*</span></span>
                    <textarea class='input text-sm min-h-36 resize-y' name='narrative' placeholder='Relato detallado de la secuencia de hechos acontecidos...' data-summary='narrative' disabled></textarea>
                </label>
                <fieldset>
                    <legend class='label text-xs'>¿Desarrollaba su trabajo habitual?</legend>
                    <div class='flex gap-2.5'>
                        @foreach($catalogs->get('yes_no', collect()) as $answer)
                            <label class='btn-secondary text-xs py-1.5 px-3 gap-2'><input type='radio' name='doing_usual_job' value='{{ $answer->value }}' disabled> {{ $answer->label }}</label>
                        @endforeach
                    </div>
                </fieldset>
                <fieldset class='hidden md:col-span-2' data-route-type>
                    <legend class='label text-xs'>Tipo de accidente de trayecto <span class='text-red-700'>*</span></legend>
                    <div class='flex flex-wrap gap-3'>
                        @foreach($catalogs->get('route_type', collect()) as $route)
                            <label class='btn-secondary text-xs py-1.5 px-3 gap-2'><input type='radio' name='route_type' value='{{ $route->value }}' disabled> {{ $route->label }}</label>
                        @endforeach
                    </div>
                </fieldset>
            </div>
        </section>

        <section class='hidden rounded-xl border border-slate-200 bg-slate-50/50 p-5' data-disease-report aria-hidden='true'>
            <h3 class='text-xs font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200 pb-2 mb-4'>Datos de la enfermedad profesional</h3>
            <div class='grid gap-4 md:grid-cols-2'>
                <label class='md:col-span-2'>
                    <span class='label text-xs'>Molestias o síntomas actuales <span class='text-red-700'>*</span></span>
                    <textarea class='input text-sm min-h-28 resize-y' name='narrative' placeholder='Describa detalladamente los síntomas o molestias informadas...' data-summary='narrative' disabled></textarea>
                </label>
                <label>
                    <span class='label text-xs'>Fecha de detección <span class='text-red-700'>*</span></span>
                    <input class='input text-sm' type='date' name='event_date' data-summary='event_date' disabled>
                </label>
                <label>
                    <span class='label text-xs'>Parte del cuerpo afectada</span>
                    <input class='input text-sm' name='affected_body_part' placeholder='Ej.: Muñeca, hombro, zona lumbar' disabled>
                </label>
                <label>
                    <span class='label text-xs'>¿Hace cuánto presenta los síntomas?</span>
                    <input class='input text-sm' name='symptom_duration' placeholder='Días, meses o años' disabled>
                </label>
                <fieldset>
                    <legend class='label text-xs'>¿Había tenido estas molestias anteriormente?</legend>
                    <div class='flex gap-2.5'>
                        @foreach($catalogs->get('yes_no', collect()) as $answer)
                            <label class='btn-secondary text-xs py-1.5 px-3 gap-2'><input type='radio' name='previous_symptoms' value='{{ $answer->value }}' disabled> {{ $answer->label }}</label>
                        @endforeach
                    </div>
                </fieldset>
                <label class='md:col-span-2'>
                    <span class='label text-xs'>Trabajo o actividad que realizaba cuando comenzaron las molestias <span class='text-red-700'>*</span></span>
                    <textarea class='input text-sm' name='activity_when_symptoms_began' rows='3' disabled></textarea>
                </label>
                <label>
                    <span class='label text-xs'>Puesto de trabajo donde comenzaron las molestias</span>
                    <input class='input text-sm' name='symptom_job_position' disabled>
                </label>
                <fieldset>
                    <legend class='label text-xs'>¿Existen compañeros con los mismos síntomas?</legend>
                    <div class='flex gap-2.5'>
                        @foreach($catalogs->get('yes_no', collect()) as $answer)
                            <label class='btn-secondary text-xs py-1.5 px-3 gap-2'><input type='radio' name='coworkers_same_symptoms' value='{{ $answer->value }}' disabled> {{ $answer->label }}</label>
                        @endforeach
                    </div>
                </fieldset>
                <label class='md:col-span-2'>
                    <span class='label text-xs'>Agentes, sustancias o condiciones laborales que podrían causar las molestias <span class='text-red-700'>*</span></span>
                    <textarea class='input text-sm' name='suspected_work_agents' rows='3' placeholder='Factores ergonómicos, biológicos, químicos o psicosociales...' disabled></textarea>
                </label>
                <label>
                    <span class='label text-xs'>Tiempo de exposición</span>
                    <input class='input text-sm' name='exposure_duration' placeholder='Ej.: 6 meses' disabled>
                </label>
                <label>
                    <span class='label text-xs'>Lugar o establecimiento relacionado</span>
                    <input class='input text-sm' name='event_location' data-summary='event_location' disabled>
                </label>
            </div>
        </section>

        <label class='block'>
            <span class='label text-xs'>Testigos o antecedentes conocidos</span>
            <textarea class='input text-sm min-h-20 resize-y' name='witnesses' placeholder='Nombres y cargos de testigos u otros antecedentes relevantes conocidos.'></textarea>
        </label>

        <section class='overflow-hidden rounded-xl border border-slate-200 bg-white'>
            <div class='border-b border-slate-200 bg-slate-50/70 p-4'>
                <div class='flex items-center gap-3'>
                    <span class='flex size-8 shrink-0 items-center justify-center rounded-lg bg-sky-100 text-sky-800 text-sm font-bold'>📎</span>
                    <div>
                        <h3 class='text-sm font-bold text-slate-900'>Evidencias del caso</h3>
                        <p class='text-xs text-slate-500'>Medios de prueba acreditados, documentos médicos, fotografías del lugar, declaraciones o partes policiales.</p>
                    </div>
                </div>
            </div>
            <div class='p-5 space-y-5'>
                <fieldset>
                    <legend class='label text-xs mb-2'>Medios de prueba disponibles</legend>
                    <div class='flex flex-wrap gap-2.5 mb-2'>
                        @foreach($catalogs->get('proof_type', collect()) as $proof)
                            <label class='flex items-center gap-2 text-xs font-medium text-slate-700 bg-slate-50 hover:bg-slate-100 border border-slate-200 px-3 py-1.5 rounded-lg cursor-pointer transition'>
                                <input type='checkbox' name='proof_types[]' value='{{ $proof->value }}' class='accent-blue-800'>
                                <span>{{ $proof->label }}</span>
                            </label>
                        @endforeach
                    </div>
                    <textarea class='input text-xs' name='proof_detail' rows='2' placeholder='Detalle o comentarios de los medios de prueba acompañados o pendientes...'></textarea>
                </fieldset>

                <div>
                    <label class='label text-xs mb-2 block'>Archivos adjuntos</label>
                    <label class='group flex min-h-32 cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-slate-300 bg-slate-50/50 p-6 text-center transition hover:border-sky-500 hover:bg-sky-50/50' data-evidence-dropzone>
                        <span class='flex size-10 items-center justify-center rounded-full bg-sky-100 text-sky-800 text-lg font-light'>+</span>
                        <span class='mt-2.5 text-xs font-bold text-slate-900'>Adjuntar documentos o evidencias</span>
                        <span class='mt-1 text-[11px] text-slate-500'>PDF, Word, Excel, imágenes, audios o videos</span>
                        <input class='sr-only' type='file' name='evidence_files[]' multiple accept='.pdf,.doc,.docx,.xls,.xlsx,image/*,audio/*,video/*' data-evidence-files>
                    </label>
                    <div class='mt-4 hidden' data-evidence-panel>
                        <div class='flex items-center justify-between gap-3 border-b border-slate-100 pb-2'>
                            <h4 class='text-xs font-bold text-slate-900 uppercase tracking-wider'>Archivos seleccionados</h4>
                            <span class='rounded-full bg-sky-100 px-2 py-0.5 text-[10px] font-bold text-sky-900' data-evidence-count></span>
                        </div>
                        <div class='mt-3 grid gap-2 sm:grid-cols-2 xl:grid-cols-3' data-evidence-list></div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <div class='mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-between border-t border-slate-100 pt-5'>
        <button type='button' class='btn-secondary text-xs py-2.5 px-4' data-previous-step>← Volver</button>
        <button type='button' class='btn-primary inline-flex text-xs py-2.5 px-5' data-next-step>Continuar a revisión final →</button>
    </div>
</section>
