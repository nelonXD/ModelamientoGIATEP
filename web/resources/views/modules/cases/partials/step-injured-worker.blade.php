<section class='card hidden p-6 sm:p-8' data-step-panel='2'>
    <div class='flex items-start gap-4'><span class='flex size-11 shrink-0 items-center justify-center rounded-xl bg-blue-950 font-black text-white'>2</span><div><p class='eyebrow'>Identificación</p><h2 class='mt-1 text-2xl font-black text-slate-950'>Datos de la persona accidentada</h2><p class='mt-1 text-sm text-slate-600'>Registra sus antecedentes laborales y de contacto.</p></div></div>
    <div class='mt-7 grid gap-5 md:grid-cols-2'>
        <label><span class='label'>RUT</span><input class='input' name='worker_rut' placeholder='12.345.678-5' data-summary='worker_rut'></label>
        <label><span class='label'>Nombre completo</span><input class='input' name='worker_name' placeholder='Nombre y apellidos' data-summary='worker_name'></label>
        <label><span class='label'>Cargo u ocupación</span><input class='input' name='job_title' placeholder='Cargo de la persona' data-summary='job_title'></label>
        <label><span class='label'>Unidad o centro de desempeño</span><input class='input' name='workplace' placeholder='Unidad de trabajo' data-summary='workplace'></label>
        <label><span class='label'>Teléfono de contacto</span><input class='input' type='tel' name='phone' placeholder='+56 9 1234 5678'></label>
        <label><span class='label'>Jornada</span><select class='input' name='work_schedule'><option>Diurna</option><option>Nocturna</option><option>Turnos rotativos</option><option>Otra</option></select></label>
    </div>
    <div class='mt-8 flex flex-col-reverse gap-3 sm:flex-row sm:justify-between'><button type='button' class='btn-secondary' data-previous-step>← Volver</button><button type='button' class='btn-primary inline-flex' data-next-step>Continuar a relato y análisis →</button></div>
</section>
