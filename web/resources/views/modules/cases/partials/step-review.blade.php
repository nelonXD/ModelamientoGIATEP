<section class='card hidden p-6 sm:p-8' data-step-panel='4'>
    <div class='flex items-start gap-4'>
        <span class='flex size-10 shrink-0 items-center justify-center rounded-xl bg-[#0b2545] font-black text-white text-sm'>4</span>
        <div>
            <p class='text-xs font-bold uppercase tracking-wider text-sky-700'>Verificación Final</p>
            <h2 class='mt-0.5 text-xl sm:text-2xl font-black text-slate-950'>Recopilación del caso</h2>
            <p class='mt-1 text-xs sm:text-sm text-slate-600'>Revise minuciosamente los antecedentes antes de proceder con el registro oficial del expediente.</p>
        </div>
    </div>
    <div class='mt-6 grid gap-4 lg:grid-cols-2 text-xs'>
        <article class='rounded-xl border border-slate-200 bg-white p-4'>
            <h3 class='text-xs font-bold uppercase tracking-wider text-slate-500 border-b border-slate-100 pb-2 mb-3'>Empleador</h3>
            <dl class='grid gap-3 sm:grid-cols-2'>
                <div><dt>Razón social</dt><dd class='text-sm' data-review='employer_name'>Sin informar</dd></div>
                <div><dt>RUT</dt><dd class='text-sm font-mono' data-review='employer_rut'>Sin informar</dd></div>
                <div><dt>Actividad económica</dt><dd class='text-sm' data-review='economic_activity'>Sin informar</dd></div>
                <div><dt>Administrador</dt><dd class='text-sm' data-review='administrator'>Sin informar</dd></div>
                <div><dt>Establecimiento</dt><dd class='text-sm' data-review='establishment_id'>Sin informar</dd></div>
                <div class='sm:col-span-2'><dt>Dirección</dt><dd class='text-sm' data-review='employer_address'>Sin informar</dd></div>
            </dl>
        </article>
        <article class='rounded-xl border border-slate-200 bg-white p-4'>
            <h3 class='text-xs font-bold uppercase tracking-wider text-slate-500 border-b border-slate-100 pb-2 mb-3'>Persona accidentada</h3>
            <dl class='grid gap-3 sm:grid-cols-2'>
                <div><dt>Nombre</dt><dd class='text-sm font-bold' data-review='worker_name'>Sin informar</dd></div>
                <div><dt>RUT</dt><dd class='text-sm font-mono' data-review='worker_rut'>Sin informar</dd></div>
                <div><dt>Profesión u oficio</dt><dd class='text-sm' data-review='job_title'>Sin informar</dd></div>
                <div><dt>Centro</dt><dd class='text-sm' data-review='workplace'>Sin informar</dd></div>
                <div><dt>Fecha de nacimiento</dt><dd class='text-sm' data-review='birth_date'>Sin informar</dd></div>
                <div><dt>Tipo de contrato</dt><dd class='text-sm' data-review='contract_type'>Sin informar</dd></div>
                <div><dt>Categoría ocupacional</dt><dd class='text-sm' data-review='occupational_category'>Sin informar</dd></div>
                <div><dt>Fecha de ingreso</dt><dd class='text-sm' data-review='hire_date'>Sin informar</dd></div>
            </dl>
        </article>
        <article class='rounded-xl border border-slate-200 bg-white p-4 lg:col-span-2'>
            <h3 class='text-xs font-bold uppercase tracking-wider text-slate-500 border-b border-slate-100 pb-2 mb-3'>Circunstancias informadas</h3>
            <dl class='grid gap-3 md:grid-cols-3'>
                <div><dt>Fecha del evento</dt><dd class='text-sm' data-review='event_date'>Sin informar</dd></div>
                <div class='md:col-span-2'><dt>Lugar específico</dt><dd class='text-sm' data-review='event_location'>Sin informar</dd></div>
                <div class='md:col-span-3'>
                    <dt>Relato de los hechos</dt>
                    <dd class='text-xs leading-relaxed text-slate-700 bg-slate-50 p-3 rounded-lg border border-slate-200 whitespace-pre-line mt-1.5' data-review='narrative'>Sin informar</dd>
                </div>
            </dl>
        </article>
        <article class='rounded-xl border border-slate-200 bg-slate-50/50 p-4 lg:col-span-2'>
            <h3 class='text-xs font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200 pb-2 mb-2'>Evidencias adjuntas</h3>
            <p class='text-xs text-slate-600' data-review-evidence>Sin archivos adjuntos.</p>
        </article>
        <article class='rounded-xl border border-blue-200 bg-blue-50/70 p-4 lg:col-span-2'>
            <div class='flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between'>
                <div>
                    <h3 class='text-xs font-black uppercase tracking-wider text-blue-950'>Así quedará registrado en el sistema</h3>
                    <p class='mt-1 text-xs text-blue-900'>Al confirmar, el boceto crea el caso y sus archivos con estas relaciones:</p>
                </div>
                <span class='inline-flex w-fit rounded-full border border-blue-200 bg-white px-2.5 py-1 text-[11px] font-bold text-blue-900'>Registro real · no solo vista previa</span>
            </div>
            <dl class='mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4'>
                <div class='rounded-lg border border-blue-100 bg-white p-3'>
                    <dt class='font-mono text-xs font-black text-slate-950'>CASO</dt>
                    <dd class='mt-1 text-[11px] leading-relaxed text-slate-600'>Tabla <code>occupational_cases</code>: empleador, trabajador, tipo, fecha, lugar, relato y estado.</dd>
                </div>
                <div class='rounded-lg border border-blue-100 bg-white p-3'>
                    <dt class='font-mono text-xs font-black text-slate-950'>EMPRESA_EMPLEADOR</dt>
                    <dd class='mt-1 text-[11px] leading-relaxed text-slate-600'>Sus datos quedan como campos de <code>CASO</code>; todavía no existe una tabla independiente.</dd>
                </div>
                <div class='rounded-lg border border-blue-100 bg-white p-3'>
                    <dt class='font-mono text-xs font-black text-slate-950'>TRABAJADOR_ACCIDENTADO/ENFERMO</dt>
                    <dd class='mt-1 text-[11px] leading-relaxed text-slate-600'>Sus datos también quedan como campos de <code>CASO</code>, no como registro reutilizable de trabajador.</dd>
                </div>
                <div class='rounded-lg border border-blue-100 bg-white p-3'>
                    <dt class='font-mono text-xs font-black text-slate-950'>EVIDENCIA</dt>
                    <dd class='mt-1 text-[11px] leading-relaxed text-slate-600'>Cada archivo crea un registro en <code>case_evidences</code> asociado al caso.</dd>
                </div>
                <div class='rounded-lg border border-blue-100 bg-white p-3 sm:col-span-2'>
                    <dt class='font-mono text-xs font-black text-slate-950'>DETALLE_ACCIDENTE / DETALLE_ENFERMEDAD</dt>
                    <dd class='mt-1 text-[11px] leading-relaxed text-slate-600'>Los campos complementarios específicos se guardan dentro de <code>details</code> (JSON) en <code>occupational_cases</code>; aún no son tablas separadas ni catálogos.</dd>
                </div>
                <div class='rounded-lg border border-blue-100 bg-white p-3 sm:col-span-2'>
                    <dt class='font-mono text-xs font-black text-slate-950'>INVESTIGACION</dt>
                    <dd class='mt-1 text-[11px] leading-relaxed text-slate-600'>No se crea en este paso: se vincula después al caso al iniciar o guardar una entrevista/análisis. Actualmente hay como máximo una investigación por caso.</dd>
                </div>
            </dl>
        </article>
    </div>
    <label class='mt-6 flex items-start gap-3 rounded-xl border border-slate-200 bg-slate-50 p-4 text-xs font-medium text-slate-800'>
        <input type='checkbox' class='mt-0.5 accent-blue-800 size-4' data-confirm-review required>
        <span>Declaro que los antecedentes registrados corresponden formalmente a la notificación recibida del evento para la apertura oficial del expediente bajo la Ley N° 16.744.</span>
    </label>
    <div class='mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-between border-t border-slate-100 pt-5'>
        <button type='button' class='btn-secondary text-xs py-2.5 px-4' data-previous-step>← Volver y corregir</button>
        <button type='submit' class='btn-primary inline-flex text-xs py-2.5 px-5' data-finish disabled>Registrar caso oficial</button>
    </div>
    <div class='mt-4 hidden rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-xs font-bold text-emerald-800' role='status' data-prototype-message>Registrando caso en el sistema...</div>
</section>
