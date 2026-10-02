<section class='card hidden p-6 sm:p-8' data-step-panel='3'>
    <div class='flex items-start gap-4'><span class='flex size-11 shrink-0 items-center justify-center rounded-xl bg-blue-950 font-black text-white'>3</span><div><p class='eyebrow'>Circunstancias y apoyo</p><h2 class='mt-1 text-2xl font-black text-slate-950'>Relato del caso y propuesta asistida</h2><p class='mt-1 text-sm text-slate-600'>Describe lo ocurrido y, si lo deseas, genera una propuesta preliminar para revisarla.</p></div></div>
    <div class='mt-7 grid gap-5 md:grid-cols-2'>
        <fieldset class='md:col-span-2'><legend class='label'>Tipo de caso</legend><div class='grid gap-3 sm:grid-cols-3'>@foreach(['Accidente del trabajo', 'Accidente de trayecto', 'Enfermedad profesional'] as $type)<label class='flex cursor-pointer items-center gap-3 rounded-xl border border-slate-300 bg-white p-4 font-bold text-slate-700 has-checked:border-blue-800 has-checked:bg-blue-50 has-checked:text-blue-950'><input type='radio' name='case_type' value='{{ $type }}' class='accent-blue-800' @checked($loop->first)>{{ $type }}</label>@endforeach</div></fieldset>
        <label><span class='label'>Fecha del suceso o detección</span><input class='input' type='date' name='event_date' data-summary='event_date'></label>
        <label><span class='label'>Hora aproximada</span><input class='input' type='time' name='event_time'></label>
        <label class='md:col-span-2'><span class='label'>Lugar específico</span><input class='input' name='event_location' placeholder='Ej.: pasillo, box, estacionamiento o trayecto' data-summary='event_location'></label>
        <label class='md:col-span-2'><span class='label'>Relato de lo ocurrido</span><textarea class='input min-h-40 resize-y' name='narrative' placeholder='Describe la secuencia de hechos de manera clara, evitando conclusiones anticipadas.' data-summary='narrative'></textarea><span class='hint'>Incluye actividad realizada, condiciones del lugar y consecuencias inmediatas.</span></label>
        <label class='md:col-span-2'><span class='label'>Testigos o antecedentes disponibles</span><textarea class='input min-h-24 resize-y' name='witnesses' placeholder='Nombres, documentos, fotografías u otros antecedentes disponibles.'></textarea></label>
    </div>

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
    <div class='mt-8 flex flex-col-reverse gap-3 sm:flex-row sm:justify-between'><button type='button' class='btn-secondary' data-previous-step>← Volver</button><button type='button' class='btn-primary inline-flex' data-next-step>Revisar recopilación →</button></div>
</section>
