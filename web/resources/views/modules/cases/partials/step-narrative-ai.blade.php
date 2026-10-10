<section class='card hidden p-6 sm:p-8' data-step-panel='3'>
    <div class='flex items-start gap-4'><span class='flex size-11 shrink-0 items-center justify-center rounded-xl bg-blue-950 font-black text-white'>3</span><div><p class='eyebrow'>Circunstancias del caso</p><h2 class='mt-1 text-2xl font-black text-slate-950'>Antecedentes del caso</h2><p class='mt-1 text-sm text-slate-600'>Registra los antecedentes disponibles al crear el caso. El análisis y las medidas se desarrollarán posteriormente en la investigación.</p></div></div>
    <div class='mt-7'>
        <fieldset><legend class='label'>Seleccione el tipo de caso <span class='text-red-700'>*</span></legend><div class='grid gap-3 sm:grid-cols-2 lg:grid-cols-4'>@foreach(['Accidente de trabajo', 'Accidente de trayecto', 'Enfermedad profesional', 'Accidente grave', 'Accidente fatal', 'Accidente de trabajo sin días perdidos', 'Incidente o suceso peligroso', 'Accidente o enfermedad común'] as $type)<label class='flex cursor-pointer items-center gap-3 rounded-xl border border-slate-300 bg-white p-4 font-bold text-slate-700 has-checked:border-blue-800 has-checked:bg-blue-50 has-checked:text-blue-950'><input type='radio' name='case_type' value='{{ $type }}' class='accent-blue-800' data-case-classification required>{{ $type }}</label>@endforeach</div><p class='hint'>Las preguntas siguientes se adaptarán a la clasificación seleccionada.</p><p class='mt-3 hidden text-sm font-semibold text-red-700' data-case-classification-error role='alert'>Seleccione el tipo de caso y complete los campos obligatorios antes de continuar.</p></fieldset>

        <section class='mt-7 hidden rounded-2xl border border-slate-200 p-5' data-accident-report aria-hidden='true'><h3 class='text-xl font-black text-blue-950'>Datos del accidente o incidente</h3><div class='mt-5 grid gap-5 md:grid-cols-2'>
            <label><span class='label'>Fecha del accidente <span class='text-red-700'>*</span></span><input class='input' type='date' name='event_date' data-summary='event_date' disabled></label>
            <label><span class='label'>Hora del accidente <span class='text-red-700'>*</span></span><input class='input' type='time' name='event_time' disabled></label>
            <label><span class='label'>Hora de ingreso al trabajo</span><input class='input' type='time' name='work_start_time' disabled></label><label><span class='label'>Hora de salida del trabajo</span><input class='input' type='time' name='work_end_time' disabled></label>
            <label class='md:col-span-2'><span class='label'>Lugar específico del accidente <span class='text-red-700'>*</span></span><input class='input' name='event_location' placeholder='Sección, edificio, área, dirección o lugar del trayecto' data-summary='event_location' disabled></label>
            <label><span class='label'>¿Qué hacía la persona justo antes del accidente?</span><textarea class='input' name='activity_before_event' rows='4' disabled></textarea></label><label><span class='label'>Trabajo habitual</span><textarea class='input' name='usual_job_description' rows='4' disabled></textarea></label>
            <label class='md:col-span-2'><span class='label'>Describa cómo ocurrió el accidente <span class='text-red-700'>*</span></span><textarea class='input min-h-40 resize-y' name='narrative' data-summary='narrative' disabled></textarea></label>
            <fieldset><legend class='label'>¿Desarrollaba su trabajo habitual?</legend><div class='flex gap-3'><label class='btn-secondary gap-2'><input type='radio' name='doing_usual_job' value='Sí' disabled>Sí</label><label class='btn-secondary gap-2'><input type='radio' name='doing_usual_job' value='No' disabled>No</label></div></fieldset>
            <fieldset><legend class='label'>Clasificación de gravedad</legend><div class='flex flex-wrap gap-3'>@foreach(['Grave','Fatal','Otro','No aplica'] as $severity)<label class='btn-secondary gap-2'><input type='radio' name='accident_severity' value='{{ $severity }}' disabled>{{ $severity }}</label>@endforeach</div></fieldset>
            <fieldset><legend class='label'>Tipo de accidente</legend><div class='flex gap-3'><label class='btn-secondary gap-2'><input type='radio' name='accident_scope' value='Trabajo' disabled>Trabajo</label><label class='btn-secondary gap-2'><input type='radio' name='accident_scope' value='Trayecto' data-route-accident disabled>Trayecto</label></div></fieldset>
            <fieldset class='hidden' data-route-type><legend class='label'>Tipo de accidente de trayecto</legend><div class='grid gap-2'>@foreach(['Domicilio - Trabajo','Trabajo - Domicilio','Entre dos trabajos'] as $route)<label class='flex gap-2 text-sm'><input type='radio' name='route_type' value='{{ $route }}' disabled>{{ $route }}</label>@endforeach</div></fieldset>
            <fieldset class='md:col-span-2'><legend class='label'>Medios de prueba disponibles</legend><div class='flex flex-wrap gap-4'>@foreach(['Parte de Carabineros','Declaración','Testigos','Fotografías','Otro'] as $proof)<label class='flex gap-2 text-sm'><input type='checkbox' name='proof_types[]' value='{{ $proof }}' disabled>{{ $proof }}</label>@endforeach</div><label class='mt-3 block'><span class='label'>Detalle</span><textarea class='input' name='proof_detail' rows='2' disabled></textarea></label></fieldset>
        </div></section>

        <section class='mt-7 hidden rounded-2xl border border-slate-200 p-5' data-disease-report aria-hidden='true'><h3 class='text-xl font-black text-blue-950'>Datos de la enfermedad profesional</h3><div class='mt-5 grid gap-5 md:grid-cols-2'>
            <label class='md:col-span-2'><span class='label'>Molestias o síntomas actuales <span class='text-red-700'>*</span></span><textarea class='input min-h-32 resize-y' name='narrative' data-summary='narrative' disabled></textarea></label>
            <label><span class='label'>Fecha de detección <span class='text-red-700'>*</span></span><input class='input' type='date' name='event_date' data-summary='event_date' disabled></label><label><span class='label'>Parte del cuerpo afectada</span><input class='input' name='affected_body_part' disabled></label>
            <label><span class='label'>¿Hace cuánto presenta los síntomas?</span><input class='input' name='symptom_duration' placeholder='Días, meses o años' disabled></label><fieldset><legend class='label'>¿Había tenido estas molestias anteriormente?</legend><div class='flex gap-3'><label class='btn-secondary gap-2'><input type='radio' name='previous_symptoms' value='Sí' disabled>Sí</label><label class='btn-secondary gap-2'><input type='radio' name='previous_symptoms' value='No' disabled>No</label></div></fieldset>
            <label class='md:col-span-2'><span class='label'>Trabajo o actividad que realizaba cuando comenzaron las molestias <span class='text-red-700'>*</span></span><textarea class='input' name='activity_when_symptoms_began' rows='4' disabled></textarea></label>
            <label><span class='label'>Puesto de trabajo donde comenzaron las molestias</span><input class='input' name='symptom_job_position' disabled></label><fieldset><legend class='label'>¿Existen compañeros con los mismos síntomas?</legend><div class='flex gap-3'><label class='btn-secondary gap-2'><input type='radio' name='coworkers_same_symptoms' value='Sí' disabled>Sí</label><label class='btn-secondary gap-2'><input type='radio' name='coworkers_same_symptoms' value='No' disabled>No</label></div></fieldset>
            <label class='md:col-span-2'><span class='label'>Agentes, sustancias o condiciones laborales que podrían causar las molestias <span class='text-red-700'>*</span></span><textarea class='input' name='suspected_work_agents' rows='4' disabled></textarea></label>
            <label><span class='label'>Tiempo de exposición</span><input class='input' name='exposure_duration' placeholder='Días, meses o años' disabled></label><label><span class='label'>Lugar o establecimiento relacionado</span><input class='input' name='event_location' data-summary='event_location' disabled></label>
        </div></section>

        <label class='mt-5 block'><span class='label'>Testigos o antecedentes disponibles</span><textarea class='input min-h-24 resize-y' name='witnesses' placeholder='Nombres de testigos u otros antecedentes conocidos.'></textarea></label>
        <section class='mt-6 overflow-hidden rounded-2xl border border-sky-200 bg-white shadow-sm'>
            <div class='border-b border-sky-100 bg-gradient-to-r from-sky-50 to-white p-5 sm:p-6'>
                <div class='flex items-start gap-4'><span class='flex size-11 shrink-0 items-center justify-center rounded-xl bg-blue-950 text-xl text-white' aria-hidden='true'>↑</span><div><h3 class='text-lg font-black text-blue-950'>Evidencias del caso</h3><p class='mt-1 text-sm leading-6 text-slate-600'>Adjunta fotografías, videos, audios, declaraciones o documentos disponibles al registrar el caso.</p></div></div>
            </div>
            <div class='p-5 sm:p-6'>
                <label class='group flex min-h-40 cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50 px-6 py-8 text-center transition hover:border-sky-500 hover:bg-sky-50' data-evidence-dropzone>
                    <span class='flex size-12 items-center justify-center rounded-full bg-sky-100 text-2xl font-light text-blue-900 transition group-hover:bg-sky-200' aria-hidden='true'>+</span>
                    <span class='mt-3 font-black text-blue-950'>Seleccionar o arrastrar archivos</span>
                    <span class='mt-1 text-sm text-slate-600'>Puedes adjuntar varios archivos a la vez</span>
                    <span class='mt-3 rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-500 shadow-sm'>PDF, Word, Excel, imágenes, audio y video</span>
                    <input class='sr-only' type='file' name='evidence_files[]' multiple accept='.pdf,.doc,.docx,.xls,.xlsx,image/*,audio/*,video/*' data-evidence-files>
                </label>
                <div class='mt-5 hidden' data-evidence-panel>
                    <div class='flex items-center justify-between gap-3'><h4 class='font-black text-slate-900'>Archivos seleccionados</h4><span class='rounded-full bg-sky-100 px-3 py-1 text-xs font-bold text-sky-900' data-evidence-count></span></div>
                    <div class='mt-3 grid gap-4 sm:grid-cols-2 xl:grid-cols-3' data-evidence-list></div>
                </div>
            </div>
        </section>
    </div>

    @if(false)
    <aside class='mt-8 overflow-hidden rounded-2xl border border-sky-200 bg-sky-50'>
        <div class='flex flex-col gap-4 border-b border-sky-200 p-5 sm:flex-row sm:items-center sm:justify-between'><div><p class='eyebrow'>Asistencia experimental</p><h3 class='mt-1 text-xl font-black text-slate-950'>Propuesta de análisis con IA</h3><p class='mt-1 text-sm text-sky-900'>Usará el relato para preparar elementos que deberán ser revisados por una persona.</p></div><button type='button' class='btn-primary inline-flex shrink-0' data-generate-ai>Generar propuesta</button></div>
        <div class='p-5 text-sm text-sky-900' data-ai-empty>Completa el relato y selecciona <strong>Generar propuesta</strong>. En este prototipo se mostrará un ejemplo visual; no se envían datos a una IA.</div>
        <div class='hidden space-y-4 p-5' data-ai-results>
            <div class='grid gap-4 lg:grid-cols-2'>
                <article class='rounded-xl border border-sky-200 bg-white p-4'><h4 class='font-black text-blue-950'>Hechos sugeridos</h4><ul class='mt-3 list-disc space-y-2 pl-5 text-sm text-slate-600'><li>La persona realizaba una actividad propia de su jornada.</li><li>Se reportó una condición del entorno que requiere verificación.</li><li>La consecuencia inmediata debe contrastarse con los antecedentes.</li></ul></article>
                <article class='rounded-xl border border-sky-200 bg-white p-4'><h4 class='font-black text-blue-950'>Borrador de árbol de causas</h4><div class='mt-3 space-y-2 text-sm text-slate-600'><p class='rounded-lg bg-slate-100 p-3'><strong>Hecho final:</strong> evento informado.</p><p class='ml-4 rounded-lg bg-slate-100 p-3'><strong>Factor:</strong> condición por confirmar.</p><p class='ml-8 rounded-lg bg-slate-100 p-3'><strong>Origen:</strong> requiere investigación.</p></div></article>
            </div>

            <article class='rounded-xl border border-sky-200 bg-white p-5'>
                <div><h4 class='font-black text-blue-950'>Medidas de control y plan de acción</h4><p class='mt-1 text-sm text-slate-500'>La IA propone las medidas. Puedes editar su redacción, responsable y plazo antes de continuar.</p></div>
                <div class='mt-4 space-y-3' data-action-plan>
                    @foreach([
                        ['Inspeccionar el lugar y registrar evidencia verificable.', 'Encargado por definir', ''],
                        ['Revisar el procedimiento de trabajo relacionado con el evento.', 'Jefatura responsable', ''],
                        ['Implementar la medida validada y comprobar su cumplimiento.', 'Prevención', ''],
                    ] as [$measure, $owner, $deadline])
                        <div class='grid gap-3 rounded-xl border border-slate-200 bg-slate-50 p-4 lg:grid-cols-[1fr_13rem_11rem_auto]' data-action-row>
                            <label><span class='label'>Medida propuesta</span><textarea class='input min-h-20 resize-y' name='control_measures[]' data-control-measure>{{ $measure }}</textarea></label>
                            <label><span class='label'>Responsable</span><input class='input' name='action_owners[]' value='{{ $owner }}'></label>
                            <label><span class='label'>Plazo</span><input class='input' type='date' name='action_deadlines[]' value='{{ $deadline }}'></label>
                            <button type='button' class='self-end rounded-xl px-3 py-3 font-bold text-red-700 hover:bg-red-50' data-remove-action aria-label='Eliminar medida'>Eliminar</button>
                        </div>
                    @endforeach
                </div>
                <button type='button' class='btn-secondary mt-4' data-add-action>+ Agregar medida al plan</button>
                <template data-action-template><div class='grid gap-3 rounded-xl border border-slate-200 bg-slate-50 p-4 lg:grid-cols-[1fr_13rem_11rem_auto]' data-action-row><label><span class='label'>Medida propuesta</span><textarea class='input min-h-20 resize-y' name='control_measures[]' data-control-measure></textarea></label><label><span class='label'>Responsable</span><input class='input' name='action_owners[]'></label><label><span class='label'>Plazo</span><input class='input' type='date' name='action_deadlines[]'></label><button type='button' class='self-end rounded-xl px-3 py-3 font-bold text-red-700 hover:bg-red-50' data-remove-action aria-label='Eliminar medida'>Eliminar</button></div></template>
            </article>
        </div>
        <p class='border-t border-sky-200 px-5 py-3 text-xs font-semibold text-sky-900'>La IA solo propone. No reemplaza la investigación, la validación humana ni la decisión institucional.</p>
    </aside>
    @endif
    <div class='mt-8 flex flex-col-reverse gap-3 sm:flex-row sm:justify-between'><button type='button' class='btn-secondary' data-previous-step>← Volver</button><button type='button' class='btn-primary inline-flex' data-next-step>Revisar recopilación →</button></div>
</section>
