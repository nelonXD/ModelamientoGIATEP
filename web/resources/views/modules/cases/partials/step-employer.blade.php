<section class='card p-6 sm:p-8' data-step-panel='1'>
    <div class='flex items-start gap-4'><span class='flex size-11 shrink-0 items-center justify-center rounded-xl bg-blue-950 font-black text-white'>1</span><div><p class='eyebrow'>Paso inicial</p><h2 class='mt-1 text-2xl font-black text-slate-950'>Datos del empleador</h2><p class='mt-1 text-sm text-slate-600'>Identifica la entidad empleadora asociada al caso.</p></div></div>
    <div class='mt-7 grid gap-5 md:grid-cols-2'>
        <label><span class='label'>Razón social</span><input class='input' name='employer_name' value='Dirección Comunal de Salud de demostración' data-summary='employer_name'></label>
        <label><span class='label'>RUT de la empresa</span><input class='input' name='employer_rut' placeholder='12.345.678-5' data-summary='employer_rut'></label>
        <label><span class='label'>Organismo administrador</span><select class='input' name='administrator' data-summary='administrator'><option value=''>Seleccionar</option><option>ACHS</option><option>IST</option><option>Mutual de Seguridad</option><option>ISL</option></select></label>
        <label><span class='label'>Establecimiento</span><select class='input' name='establishment' data-summary='establishment'><option value=''>Seleccionar establecimiento</option><option>Centro de demostración</option><option>Dependencia administrativa de demostración</option></select></label>
        <label class='md:col-span-2'><span class='label'>Dirección del centro de trabajo</span><input class='input' name='employer_address' placeholder='Calle, número y comuna' data-summary='employer_address'></label>
    </div>
    <div class='mt-8 flex justify-end'><button type='button' class='btn-primary inline-flex' data-next-step>Continuar a persona accidentada →</button></div>
</section>
