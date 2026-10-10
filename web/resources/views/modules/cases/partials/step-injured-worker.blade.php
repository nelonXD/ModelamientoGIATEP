<section class='card hidden p-6 sm:p-8' data-step-panel='2'>
    <div class='flex items-start gap-4'><span class='flex size-11 shrink-0 items-center justify-center rounded-xl bg-blue-950 font-black text-white'>2</span><div><p class='eyebrow'>Identificación</p><h2 class='mt-1 text-2xl font-black text-slate-950'>Datos de la persona accidentada</h2><p class='mt-1 text-sm text-slate-600'>Registra sus antecedentes laborales y de contacto.</p></div></div>
    <div class='mt-7 space-y-6'>
        <div class='rounded-2xl border border-slate-200 bg-slate-50/70 p-5'>
        <h3 class='text-base font-black text-slate-900'>Identificación y contacto</h3>
        <div class='mt-5 grid gap-5 md:grid-cols-2'>
            <label><span class='label'>Nombre completo <span class='text-red-700'>*</span></span><input class='input' name='worker_name' placeholder='Nombres, apellido paterno y apellido materno' data-summary='worker_name'></label>
            <label><span class='label'>RUT o RUN <span class='text-red-700'>*</span></span><input class='input' name='worker_rut' placeholder='12.345.678-5' data-summary='worker_rut'></label>
            <label class='md:col-span-2'><span class='label'>Dirección particular</span><input class='input' name='worker_address' placeholder='Calle, número, departamento, población o villa'></label>
            <label><span class='label'>Comuna</span><select class='input' name='worker_commune'><option value=''>Seleccionar comuna</option>@foreach(['Alto Biobío','Antuco','Arauco','Cabrero','Cañete','Chiguayante','Concepción','Contulmo','Coronel','Curanilahue','Florida','Hualpén','Hualqui','Laja','Lebu','Los Álamos','Los Ángeles','Lota','Mulchén','Nacimiento','Negrete','Penco','Quilaco','Quilleco','San Pedro de la Paz','San Rosendo','Santa Bárbara','Santa Juana','Talcahuano','Tirúa','Tomé','Tucapel','Otra comuna'] as $commune)<option>{{ $commune }}</option>@endforeach</select></label><label><span class='label'>Teléfono de contacto</span><input class='input' type='tel' name='phone' placeholder='+56 9 1234 5678'></label>
            <label><span class='label'>Sexo</span><select class='input' name='worker_sex'><option value=''>Seleccionar</option><option>Hombre</option><option>Mujer</option><option>Otro</option></select></label>
            <label><span class='label'>Fecha de nacimiento</span><input class='input' type='date' name='birth_date' data-worker-birth-date></label>
            <label><span class='label'>Edad</span><input class='input' type='number' name='worker_age' min='0' readonly data-worker-age></label>
            <label><span class='label'>Nacionalidad</span><select class='input' name='nationality'><option value=''>Seleccionar nacionalidad</option>@foreach(['Chilena','Argentina','Boliviana','Brasileña','Colombiana','Ecuatoriana','Paraguaya','Peruana','Uruguaya','Venezolana','Haitiana','Dominicana','Cubana','Mexicana','Estadounidense','Canadiense','Española','Francesa','Italiana','Alemana','Portuguesa','China','Coreana','Japonesa','Otra nacionalidad'] as $nationality)<option @selected($nationality === 'Chilena')>{{ $nationality }}</option>@endforeach</select></label>
        </div>
        </div>
        <div class='rounded-2xl border border-slate-200 p-5'>
        <h3 class='text-base font-black text-slate-900'>Antecedentes laborales</h3>
        <div class='mt-5 grid gap-5 md:grid-cols-2'>
            <label class='md:col-span-2'><span class='label'>Pertenencia a pueblo originario</span><select class='input' name='indigenous_people'><option value=''>Seleccionar</option>@foreach(['Alacalufe','Colla','Quechua','Atacameño','Diaguita','Rapanui','Aimara','Mapuche','Yamana (Yagán)','Ninguno','Otro'] as $people)<option>{{ $people }}</option>@endforeach</select></label>
            <label><span class='label'>Profesión u oficio <span class='text-red-700'>*</span></span><input class='input' name='job_title' placeholder='Profesión, oficio o cargo' data-summary='job_title'></label>
            <label><span class='label'>Unidad o centro de desempeño <span class='text-red-700'>*</span></span><input class='input' name='workplace' placeholder='Unidad de trabajo' data-summary='workplace'></label>
            <label><span class='label'>Fecha de ingreso a la empresa</span><input class='input' type='date' name='hire_date'></label>
            <label><span class='label'>Antigüedad en la empresa</span><input class='input' name='company_tenure' placeholder='Ej.: 2 años y 4 meses'></label>
            <label><span class='label'>Tipo de contrato</span><select class='input' name='contract_type'><option value=''>Seleccionar</option>@foreach(['Indefinido','Plazo fijo','Obra o faena','Temporada'] as $contract)<option>{{ $contract }}</option>@endforeach</select></label>
            <label><span class='label'>Tipo de ingreso</span><select class='input' name='income_type'><option value=''>Seleccionar</option>@foreach(['Remuneración fija','Remuneración variable','Honorarios'] as $income)<option>{{ $income }}</option>@endforeach</select></label>
            <label class='md:col-span-2'><span class='label'>Categoría ocupacional</span><select class='input' name='occupational_category'><option value=''>Seleccionar</option>@foreach(['Empleador','Trabajador dependiente','Trabajador independiente','Familiar no remunerado','Trabajador voluntario'] as $category)<option>{{ $category }}</option>@endforeach</select></label>
            <label><span class='label'>Jornada</span><select class='input' name='work_schedule'><option>Diurna</option><option>Nocturna</option><option>Turnos rotativos</option><option>Otra</option></select></label>
        </div>
        </div>
    </div>
    <div class='mt-8 flex flex-col-reverse gap-3 sm:flex-row sm:justify-between'><button type='button' class='btn-secondary' data-previous-step>← Volver</button><button type='button' class='btn-primary inline-flex' data-next-step>Continuar a relato y análisis →</button></div>
</section>
