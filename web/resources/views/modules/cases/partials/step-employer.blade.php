<section class='card p-6 sm:p-8' data-step-panel='1'>
    <div class='flex items-start gap-4'><span class='flex size-11 shrink-0 items-center justify-center rounded-xl bg-blue-950 font-black text-white'>1</span><div><p class='eyebrow'>Paso inicial</p><h2 class='mt-1 text-2xl font-black text-slate-950'>Datos del empleador</h2><p class='mt-1 text-sm text-slate-600'>Identifica la entidad empleadora asociada al caso.</p></div></div>
    <div class='mt-7 space-y-6'>
        <div class='rounded-2xl border border-slate-200 bg-slate-50/70 p-5'>
            <h3 class='text-base font-black text-slate-900'>Identificación de la empresa</h3>
            <p class='mt-1 text-sm text-slate-600'>Información legal y de contacto del empleador.</p>
        <div class='grid gap-5 md:grid-cols-2'>
            <label><span class='label'>Nombre o razón social <span class='text-red-700'>*</span></span><input class='input' name='employer_name' value='Dirección Comunal de Salud de demostración' data-summary='employer_name'></label>
            <label><span class='label'>RUT de la empresa <span class='text-red-700'>*</span></span><input class='input' name='employer_rut' placeholder='12.345.678-5' data-summary='employer_rut'></label>
            <label class='md:col-span-2'><span class='label'>Dirección de la empresa <span class='text-red-700'>*</span></span><input class='input' name='employer_address' placeholder='Calle, número, departamento, población o villa' data-summary='employer_address'></label>
            <label><span class='label'>Comuna <span class='text-red-700'>*</span></span><select class='input' name='employer_commune'><option value=''>Seleccionar comuna</option>@foreach(['Alto Biobío','Antuco','Arauco','Cabrero','Cañete','Chiguayante','Concepción','Contulmo','Coronel','Curanilahue','Florida','Hualpén','Hualqui','Laja','Lebu','Los Álamos','Los Ángeles','Lota','Mulchén','Nacimiento','Negrete','Penco','Quilaco','Quilleco','San Pedro de la Paz','San Rosendo','Santa Bárbara','Santa Juana','Talcahuano','Tirúa','Tomé','Tucapel','Otra comuna'] as $commune)<option @selected($commune === 'Los Ángeles')>{{ $commune }}</option>@endforeach</select></label>
            <label><span class='label'>Número de teléfono</span><input class='input' type='tel' name='employer_phone' placeholder='43 2123456'></label>
            <label><span class='label'>Actividad económica <span class='text-red-700'>*</span></span><input class='input' name='economic_activity' placeholder='Actividad principal de la empresa'></label>
            <label><span class='label'>Tipo de empresa <span class='text-red-700'>*</span></span><select class='input' name='company_type' data-company-type><option value=''>Seleccionar tipo de empresa</option><option>Principal</option><option>Contratista</option><option>Subcontratista</option><option>Servicios transitorios</option></select></label>
            <label><span class='label'>Organismo administrador <span class='text-red-700'>*</span></span><select class='input' name='administrator' data-summary='administrator'><option value=''>Seleccionar</option><option>ACHS</option><option>IST</option><option>Mutual de Seguridad</option><option>ISL</option></select></label>
            <label><span class='label'>Establecimiento o centro de trabajo <span class='text-red-700'>*</span></span><select class='input' name='establishment' data-summary='establishment'><option value=''>Seleccionar establecimiento</option><option>Centro de demostración</option><option>Dependencia administrativa de demostración</option></select></label>
        </div>
        </div>
    </div>
    <div class='mt-8 flex justify-end'><button type='button' class='btn-primary inline-flex' data-next-step>Continuar a persona accidentada →</button></div>
</section>
