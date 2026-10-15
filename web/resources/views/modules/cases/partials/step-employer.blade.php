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
                    <input class='input text-sm' name='employer_name' value='Dirección Comunal de Salud de Los Ángeles' data-summary='employer_name' required>
                </label>
                <label>
                    <span class='label text-xs'>RUT de la empresa <span class='text-red-700'>*</span></span>
                    <input class='input text-sm font-mono' name='employer_rut' value='69.170.102-6' placeholder='69.170.102-6' data-summary='employer_rut' required>
                </label>
                <label class='md:col-span-2'>
                    <span class='label text-xs'>Dirección de la empresa <span class='text-red-700'>*</span></span>
                    <input class='input text-sm' name='employer_address' value='Calle Valdivia #150, Los Ángeles' placeholder='Calle, número, oficina' data-summary='employer_address' required>
                </label>
                <label>
                    <span class='label text-xs'>Comuna <span class='text-red-700'>*</span></span>
                    <select class='input text-sm' name='employer_commune' required>
                        <option value=''>Seleccionar comuna</option>
                        @foreach(['Alto Biobío','Antuco','Arauco','Cabrero','Cañete','Chiguayante','Concepción','Contulmo','Coronel','Curanilahue','Florida','Hualpén','Hualqui','Laja','Lebu','Los Álamos','Los Ángeles','Lota','Mulchén','Nacimiento','Negrete','Penco','Quilaco','Quilleco','San Pedro de la Paz','San Rosendo','Santa Bárbara','Santa Juana','Talcahuano','Tirúa','Tomé','Tucapel','Otra comuna'] as $commune)
                            <option @selected($commune === 'Los Ángeles')>{{ $commune }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    <span class='label text-xs'>Número de teléfono</span>
                    <input class='input text-sm' type='tel' name='employer_phone' value='43 2321200' placeholder='43 2321200'>
                </label>
                <label>
                    <span class='label text-xs'>Actividad económica <span class='text-red-700'>*</span></span>
                    <input class='input text-sm' name='economic_activity' value='Servicios de Salud Pública Municipal (APS)' placeholder='Servicios de salud municipal' required>
                </label>
                <label>
                    <span class='label text-xs'>Tipo de empresa <span class='text-red-700'>*</span></span>
                    <select class='input text-sm' name='company_type' data-company-type required>
                        <option>Principal</option>
                        <option>Contratista</option>
                        <option>Subcontratista</option>
                        <option>Servicios transitorios</option>
                    </select>
                </label>
                <label>
                    <span class='label text-xs'>Organismo administrador <span class='text-red-700'>*</span></span>
                    <select class='input text-sm' name='administrator' data-summary='administrator' required>
                        <option @selected(true)>ACHS</option>
                        <option>IST</option>
                        <option>Mutual de Seguridad</option>
                        <option>ISL</option>
                    </select>
                </label>
                <label>
                    <span class='label text-xs'>Establecimiento o centro de trabajo <span class='text-red-700'>*</span></span>
                    <select class='input text-sm' name='establishment' data-summary='establishment' required>
                        <option value=''>Seleccionar establecimiento</option>
                        @if(isset($establishments) && count($establishments))
                            @foreach($establishments as $est)
                                <option value='{{ $est->name }}'>{{ $est->name }} ({{ $est->type }})</option>
                            @endforeach
                        @else
                            <option>CESFAM Norte</option>
                            <option>CESFAM Dos de Septiembre</option>
                            <option>CESFAM Nuevo Horizonte</option>
                            <option>CESFAM Paillihue</option>
                            <option>CESFAM Sur</option>
                            <option>Dirección Comunal de Salud</option>
                        @endif
                    </select>
                </label>
            </div>
        </div>
    </div>
    <div class='mt-6 flex justify-end border-t border-slate-100 pt-5'>
        <button type='button' class='btn-primary inline-flex text-xs py-2.5 px-5' data-next-step>Continuar a persona accidentada →</button>
    </div>
</section>
