<x-layouts.app title='Casos · GIATEP'>
    <div class='mx-auto max-w-7xl space-y-6'>
        <section class='flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between'>
            <div>
                <p class='eyebrow'>Gestión de casos</p>
                <h1 class='mt-2 text-3xl font-black tracking-tight text-slate-950'>Casos registrados</h1>
                <p class='mt-2 max-w-2xl text-sm leading-6 text-slate-600'>Consulta los borradores y expedientes disponibles dentro de tus establecimientos autorizados.</p>
            </div>
            @if(auth()->user()->hasPermission('cases.create'))
                <a href='{{ route('modules.cases.create') }}' class='btn-primary inline-flex gap-2'><span aria-hidden='true'>+</span> Registrar caso</a>
            @endif
        </section>

        <div class='rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-900'><strong>Prototipo de diseño:</strong> los registros mostrados son ficticios y no representan casos reales.</div>

        <section class='card overflow-hidden'>
            <div class='grid gap-3 border-b border-slate-200 bg-slate-50/60 p-5 md:grid-cols-[1fr_14rem_14rem]'>
                <label><span class='label'>Buscar caso</span><input type='search' class='input' placeholder='Folio, persona o establecimiento'></label>
                <label><span class='label'>Tipo</span><select class='input'><option>Todos los tipos</option><option>Accidente del trabajo</option><option>Accidente de trayecto</option><option>Enfermedad profesional</option></select></label>
                <label><span class='label'>Estado</span><select class='input'><option>Todos los estados</option><option>Borrador</option><option>En revisión</option><option>Cerrado</option></select></label>
            </div>
            <div class='overflow-x-auto'>
                <table class='min-w-full divide-y divide-slate-200 text-left text-sm'>
                    <thead class='bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-500'><tr><th>Expediente</th><th>Persona afectada</th><th>Empleador</th><th>Antecedentes</th><th>Estado</th><th><span class='sr-only'>Acciones</span></th></tr></thead>
                    <tbody class='divide-y divide-slate-100 bg-white' data-case-list>
                        @foreach($cases as $case)
                            <tr class='transition hover:bg-slate-50' data-case-row data-case-folio='{{ $case['folio'] }}' data-case-type='{{ $case['type'] }}'>
                                <td><span class='font-black text-blue-950'>{{ $case['folio'] }}</span><span class='mt-1 block text-xs font-semibold text-slate-500' data-case-type-label>{{ $case['type'] }}</span><span class='mt-2 hidden w-fit rounded-full bg-amber-100 px-2 py-1 text-[10px] font-black uppercase tracking-wide text-amber-900' data-form-review>Formulario por actualizar</span></td><td><span class='font-bold text-slate-900'>{{ $case['person'] }}</span><span class='mt-1 block text-xs text-slate-500'>{{ $case['workerRut'] }} · {{ $case['jobTitle'] }}</span></td><td><span class='font-semibold text-slate-800'>{{ $case['employerName'] }}</span><span class='mt-1 block text-xs text-slate-500'>{{ $case['establishment'] }}</span></td><td><span class='whitespace-nowrap font-semibold text-slate-800'>{{ $case['date'] }}</span><span class='mt-1 block text-xs text-sky-800'>{{ count($case['evidenceFiles']) }} {{ count($case['evidenceFiles']) === 1 ? 'evidencia' : 'evidencias' }}</span></td>
                                <td><span class='status {{ $case['status'] === 'Borrador' ? 'status-pending' : 'bg-sky-100 text-sky-800' }}'>{{ $case['status'] }}</span></td>
                                <td><div class='flex min-w-44 flex-col items-stretch gap-2'><button type='button' class='rounded-lg border border-slate-300 px-3 py-2 text-center font-bold text-blue-900 hover:bg-slate-50' data-case-preview='case-preview-{{ $loop->iteration }}'>Ver antecedentes</button><button type='button' class='rounded-lg border border-amber-300 px-3 py-2 text-center font-bold text-amber-900 hover:bg-amber-50' data-change-case-type>Cambiar tipo</button><a href='{{ route('modules.investigations.index', ['case' => $case['folio'], 'case_type' => $case['type']]) }}' class='rounded-lg bg-emerald-700 px-3 py-2 text-center font-bold text-white hover:bg-emerald-800' data-investigation-link>Iniciar investigación</a></div></td>
                            </tr>

                            <dialog id='case-preview-{{ $loop->iteration }}' class='m-auto w-[calc(100%-2rem)] max-w-2xl rounded-2xl border border-slate-200 bg-white p-0 text-slate-900 shadow-2xl backdrop:bg-slate-950/50' data-case-preview-dialog data-case-folio='{{ $case['folio'] }}'>
                                <div class='border-b border-slate-200 p-6'><div class='flex items-start justify-between gap-4'><div><p class='eyebrow'>Previsualización del caso</p><h2 class='mt-2 text-2xl font-black'>{{ $case['folio'] }}</h2><p class='mt-1 text-sm text-slate-500'>Datos ficticios para validar el diseño.</p></div><button type='button' class='flex size-10 items-center justify-center rounded-full bg-slate-100 text-xl font-bold text-slate-600 hover:bg-slate-200' aria-label='Cerrar previsualización' data-close-dialog>×</button></div></div>
                                <div class='max-h-[65vh] space-y-5 overflow-y-auto p-6'><section class='rounded-xl border border-slate-200 p-4'><h3 class='font-black text-blue-950'>Datos del empleador</h3><dl class='mt-3 grid gap-4 sm:grid-cols-2'><div><dt>Razón social</dt><dd>{{ $case['employerName'] }}</dd></div><div><dt>RUT</dt><dd>{{ $case['employerRut'] }}</dd></div><div><dt>Administrador</dt><dd>{{ $case['administrator'] }}</dd></div><div><dt>Dirección</dt><dd>{{ $case['employerAddress'] }}</dd></div></dl></section><section class='rounded-xl border border-slate-200 p-4'><h3 class='font-black text-blue-950'>Persona accidentada</h3><dl class='mt-3 grid gap-4 sm:grid-cols-2'><div><dt>Nombre</dt><dd>{{ $case['person'] }}</dd></div><div><dt>RUT</dt><dd>{{ $case['workerRut'] }}</dd></div><div><dt>Cargo</dt><dd>{{ $case['jobTitle'] }}</dd></div><div><dt>Teléfono</dt><dd>{{ $case['phone'] }}</dd></div></dl></section><section class='rounded-xl border border-slate-200 p-4'><h3 class='font-black text-blue-950'>Antecedentes del caso</h3><dl class='mt-3 grid gap-4 sm:grid-cols-2'><div><dt>Tipo</dt><dd>{{ $case['type'] }}</dd></div><div><dt>Fecha y hora</dt><dd>{{ $case['date'] }} · {{ $case['time'] }}</dd></div><div class='sm:col-span-2'><dt>Lugar</dt><dd>{{ $case['location'] }}</dd></div><div class='sm:col-span-2'><dt>Relato</dt><dd class='text-sm font-normal leading-6 text-slate-600'>{{ $case['summary'] }}</dd></div><div class='sm:col-span-2'><dt>Testigos y antecedentes</dt><dd class='text-sm font-normal leading-6 text-slate-600'>{{ $case['witnesses'] }}</dd></div></dl></section><section class='rounded-xl border border-sky-200 bg-sky-50 p-4'><h3 class='font-black text-blue-950'>Evidencias adjuntas</h3><ul class='mt-3 grid gap-2 sm:grid-cols-2'>@forelse($case['evidenceFiles'] as $file)<li class='rounded-lg border border-sky-100 bg-white p-3'><strong class='block truncate text-sm text-slate-900'>{{ $file['name'] }}</strong><span class='text-xs text-slate-500'>{{ strtoupper(pathinfo($file['name'], PATHINFO_EXTENSION)) }} · {{ max(1, round($file['size'] / 1024)) }} KB</span></li>@empty<li class='text-sm text-slate-600'>Sin evidencias adjuntas.</li>@endforelse</ul></section></div>
                                <div class='flex flex-col-reverse gap-3 border-t border-slate-200 bg-slate-50 p-5 sm:flex-row sm:justify-end'><button type='button' class='btn-secondary' data-close-dialog>Cerrar</button><a href='{{ route('modules.investigations.index', ['case' => $case['folio']]) }}' class='btn-primary inline-flex'>Iniciar investigación →</a></div>
                            </dialog>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class='flex flex-col gap-2 border-t border-slate-200 px-5 py-4 text-sm text-slate-500 sm:flex-row sm:items-center sm:justify-between'><p>Mostrando <span data-case-count>{{ $cases->count() }}</span> casos de demostración.</p><p>Los casos creados se guardan solamente en este navegador.</p></div>
        </section>
    </div>

    <dialog class='m-auto w-[calc(100%-2rem)] max-w-4xl rounded-2xl border border-slate-200 bg-white p-0 text-slate-900 shadow-2xl backdrop:bg-slate-950/50' data-stored-case-dialog>
        <div class='border-b border-slate-200 p-6'><div class='flex items-start justify-between gap-4'><div><p class='eyebrow'>Caso guardado en este navegador</p><h2 class='mt-2 text-2xl font-black' data-stored-preview='folio'></h2></div><button type='button' class='flex size-10 items-center justify-center rounded-full bg-slate-100 text-xl font-bold' data-close-stored-dialog aria-label='Cerrar'>×</button></div></div>
        <div class='max-h-[65vh] space-y-5 overflow-y-auto p-6'><section class='rounded-xl border border-slate-200 p-4'><h3 class='font-black text-blue-950'>Datos del empleador</h3><dl class='mt-3 grid gap-4 sm:grid-cols-2'><div><dt>Razón social</dt><dd data-stored-preview='employerName'></dd></div><div><dt>RUT</dt><dd data-stored-preview='employerRut'></dd></div><div><dt>Administrador</dt><dd data-stored-preview='administrator'></dd></div><div><dt>Dirección</dt><dd data-stored-preview='employerAddress'></dd></div></dl></section><section class='rounded-xl border border-slate-200 p-4'><h3 class='font-black text-blue-950'>Persona accidentada</h3><dl class='mt-3 grid gap-4 sm:grid-cols-2'><div><dt>Nombre</dt><dd data-stored-preview='person'></dd></div><div><dt>RUT</dt><dd data-stored-preview='workerRut'></dd></div><div><dt>Cargo</dt><dd data-stored-preview='jobTitle'></dd></div><div><dt>Teléfono</dt><dd data-stored-preview='phone'></dd></div><div><dt>Nacionalidad</dt><dd data-stored-preview='nationality'></dd></div><div><dt>Comuna</dt><dd data-stored-preview='workerCommune'></dd></div></dl></section><section class='rounded-xl border border-slate-200 p-4'><h3 class='font-black text-blue-950'>Antecedentes del caso</h3><dl class='mt-3 grid gap-4 sm:grid-cols-2'><div><dt>Tipo</dt><dd data-stored-preview='type'></dd></div><div><dt>Fecha</dt><dd data-stored-preview='date'></dd></div><div><dt>Hora</dt><dd data-stored-preview='time'></dd></div><div><dt>Lugar</dt><dd data-stored-preview='location'></dd></div><div class='sm:col-span-2'><dt>Relato</dt><dd class='whitespace-pre-line text-sm font-normal leading-6 text-slate-600' data-stored-preview='summary'></dd></div><div class='sm:col-span-2'><dt>Testigos y antecedentes</dt><dd class='whitespace-pre-line text-sm font-normal leading-6 text-slate-600' data-stored-preview='witnesses'></dd></div></dl></section><section class='rounded-xl border border-sky-200 bg-sky-50 p-4'><div class='flex items-center justify-between'><h3 class='font-black text-blue-950'>Evidencias adjuntas</h3><span class='status bg-white text-sky-800' data-stored-evidence-count></span></div><ul class='mt-3 grid gap-2 sm:grid-cols-2' data-stored-evidence></ul></section></div>
        <div class='flex flex-col-reverse gap-3 border-t border-slate-200 bg-slate-50 p-5 sm:flex-row sm:justify-end'><button type='button' class='btn-secondary' data-close-stored-dialog>Cerrar</button><a href='{{ route('modules.investigations.index') }}' class='btn-primary inline-flex' data-stored-investigation>Iniciar investigación →</a></div>
    </dialog>
    <dialog class='m-auto w-[calc(100%-2rem)] max-w-xl rounded-2xl border border-slate-200 bg-white p-0 text-slate-900 shadow-2xl backdrop:bg-slate-950/50' data-case-type-dialog>
        <div class='border-b border-slate-200 p-6'><p class='eyebrow'>Actualizar clasificación</p><h2 class='mt-2 text-2xl font-black text-slate-950'>Cambiar tipo de caso</h2><p class='mt-2 text-sm text-slate-600'>La clasificación puede modificarse cuando aparezcan nuevos antecedentes.</p></div>
        <div class='space-y-4 p-6'><div class='rounded-xl bg-slate-50 p-4 text-sm'><span class='text-slate-500'>Expediente</span><strong class='mt-1 block text-blue-950' data-type-case-folio></strong></div><label><span class='label'>Nuevo tipo de caso</span><select class='input' data-new-case-type>@foreach(['Accidente de trabajo','Accidente de trayecto','Enfermedad profesional','Accidente grave','Accidente fatal','Accidente de trabajo sin días perdidos','Incidente o suceso peligroso','Accidente o enfermedad común'] as $type)<option>{{ $type }}</option>@endforeach</select></label>
            <div class='hidden rounded-xl border border-amber-300 bg-amber-50 p-4 text-sm text-amber-950' data-type-change-warning><strong class='block'>Se debe actualizar el formulario del caso</strong><p class='mt-1 leading-6'>Los antecedentes solicitados para una enfermedad profesional son diferentes a los de un accidente. Revisa y completa el formulario correspondiente antes de continuar con la investigación.</p></div>
        </div>
        <div class='flex flex-col-reverse gap-3 border-t border-slate-200 bg-slate-50 p-5 sm:flex-row sm:justify-end'><button type='button' class='btn-secondary' data-cancel-type-change>Cancelar</button><button type='button' class='btn-primary' data-confirm-type-change>Guardar cambio</button></div>
    </dialog>
    @push('scripts')
        <script>
            document.querySelectorAll('[data-case-preview]').forEach((button) => button.addEventListener('click', () => document.getElementById(button.dataset.casePreview)?.showModal()));
            document.querySelectorAll('[data-close-dialog]').forEach((button) => button.addEventListener('click', () => button.closest('dialog')?.close()));

            (() => {
                const storageKey = 'giatep.demo.cases';
                const typeStorageKey = 'giatep.demo.case-types';
                const reviewStorageKey = 'giatep.demo.case-type-reviews';
                const tableBody = document.querySelector('[data-case-list]');
                const dialog = document.querySelector('[data-stored-case-dialog]');
                const typeDialog = document.querySelector('[data-case-type-dialog]');
                const typeSelect = typeDialog.querySelector('[data-new-case-type]');
                const typeWarning = typeDialog.querySelector('[data-type-change-warning]');
                let storedCases = [];
                let typeOverrides = {};
                let typeReviews = {};
                let activeCaseRow = null;

                try {
                    const parsed = JSON.parse(localStorage.getItem(storageKey) || '[]');
                    storedCases = Array.isArray(parsed) ? parsed : [];
                } catch (error) {
                    storedCases = [];
                }
                try {
                    typeOverrides = JSON.parse(localStorage.getItem(typeStorageKey) || '{}');
                } catch (error) {
                    typeOverrides = {};
                }
                try {
                    typeReviews = JSON.parse(localStorage.getItem(reviewStorageKey) || '{}');
                } catch (error) {
                    typeReviews = {};
                }

                const text = (value) => String(value || 'Sin informar');
                const appendCell = (row, content, className = '') => {
                    const cell = document.createElement('td');
                    cell.textContent = text(content);
                    cell.className = className;
                    row.append(cell);
                };
                const isDiseaseType = (type) => type === 'Enfermedad profesional';
                const updateTypeWarning = () => {
                    const changesForm = activeCaseRow && isDiseaseType(activeCaseRow.dataset.caseType) !== isDiseaseType(typeSelect.value);
                    typeWarning.classList.toggle('hidden', ! changesForm);
                };
                const updateInvestigationLink = (row) => {
                    const link = row.querySelector('[data-investigation-link]');
                    if (! link) return;
                    const url = new URL(link.href, window.location.origin);
                    url.searchParams.set('case_type', row.dataset.caseType);
                    link.href = url.toString();
                };
                const openTypeDialog = (row) => {
                    activeCaseRow = row;
                    typeDialog.querySelector('[data-type-case-folio]').textContent = row.dataset.caseFolio;
                    typeSelect.value = row.dataset.caseType;
                    updateTypeWarning();
                    typeDialog.showModal();
                };

                const openStoredPreview = (caseItem) => {
                    dialog.querySelectorAll('[data-stored-preview]').forEach((output) => {
                        output.textContent = text(caseItem[output.dataset.storedPreview]);
                    });

                    const list = dialog.querySelector('[data-stored-evidence]');
                    const evidence = Array.isArray(caseItem.evidenceFiles) ? caseItem.evidenceFiles : [];
                    list.replaceChildren();
                    dialog.querySelector('[data-stored-evidence-count]').textContent = `${evidence.length} ${evidence.length === 1 ? 'archivo' : 'archivos'}`;
                    (evidence.length ? evidence : [{ name: 'Sin evidencias adjuntas', size: 0, type: '' }]).forEach((file) => {
                        const item = document.createElement('li');
                        item.className = 'rounded-lg border border-sky-100 bg-white p-3 text-sm';
                        const title = document.createElement('strong');
                        title.className = 'block truncate text-slate-900';
                        title.textContent = text(file.name);
                        const detail = document.createElement('span');
                        detail.className = 'text-xs text-slate-500';
                        detail.textContent = file.size ? `${text(file.type || 'Archivo')} · ${Math.max(1, Math.round(file.size / 1024))} KB` : 'No se seleccionaron archivos';
                        item.append(title, detail);
                        list.append(item);
                    });

                    const investigationUrl = new URL('{{ route('modules.investigations.index') }}', window.location.origin);
                    investigationUrl.searchParams.set('case', caseItem.folio);
                    dialog.querySelector('[data-stored-investigation]').href = investigationUrl.toString();
                    dialog.showModal();
                };

                storedCases.forEach((caseItem) => {
                    const row = document.createElement('tr');
                    row.className = 'transition bg-emerald-50/30 hover:bg-emerald-50';
                    caseItem.type = typeOverrides[caseItem.folio] || caseItem.type;
                    row.dataset.caseRow = '';
                    row.dataset.caseFolio = caseItem.folio;
                    row.dataset.caseType = caseItem.type;
                    const expedienteCell = document.createElement('td');
                    expedienteCell.innerHTML = '<span class="font-black text-blue-950"></span><span class="mt-1 block text-xs font-semibold text-slate-500" data-case-type-label></span><span class="mt-2 hidden w-fit rounded-full bg-amber-100 px-2 py-1 text-[10px] font-black uppercase tracking-wide text-amber-900" data-form-review>Formulario por actualizar</span>';
                    expedienteCell.children[0].textContent = text(caseItem.folio);
                    expedienteCell.children[1].textContent = text(caseItem.type);
                    row.append(expedienteCell);
                    const personCell = document.createElement('td');
                    personCell.innerHTML = '<span class="font-bold text-slate-900"></span><span class="mt-1 block text-xs text-slate-500"></span>';
                    personCell.children[0].textContent = text(caseItem.person);
                    personCell.children[1].textContent = `${text(caseItem.workerRut)} · ${text(caseItem.jobTitle)}`;
                    row.append(personCell);
                    const employerCell = document.createElement('td');
                    employerCell.innerHTML = '<span class="font-semibold text-slate-800"></span><span class="mt-1 block text-xs text-slate-500"></span>';
                    employerCell.children[0].textContent = text(caseItem.employerName);
                    employerCell.children[1].textContent = text(caseItem.establishment);
                    row.append(employerCell);
                    const backgroundCell = document.createElement('td');
                    const evidenceTotal = Array.isArray(caseItem.evidenceFiles) ? caseItem.evidenceFiles.length : 0;
                    backgroundCell.innerHTML = '<span class="whitespace-nowrap font-semibold text-slate-800"></span><span class="mt-1 block text-xs text-sky-800"></span>';
                    backgroundCell.children[0].textContent = text(caseItem.date);
                    backgroundCell.children[1].textContent = `${evidenceTotal} ${evidenceTotal === 1 ? 'evidencia' : 'evidencias'}`;
                    row.append(backgroundCell);

                    const statusCell = document.createElement('td');
                    const status = document.createElement('span');
                    status.className = 'status status-pending';
                    status.textContent = text(caseItem.status);
                    statusCell.append(status);
                    row.append(statusCell);

                    const actionCell = document.createElement('td');
                    const actions = document.createElement('div');
                    actions.className = 'flex min-w-44 flex-col items-stretch gap-2';
                    const preview = document.createElement('button');
                    preview.type = 'button';
                    preview.className = 'rounded-lg border border-slate-300 px-3 py-2 text-center font-bold text-blue-900 hover:bg-slate-50';
                    preview.textContent = 'Ver antecedentes';
                    preview.addEventListener('click', () => openStoredPreview(caseItem));
                    const changeType = document.createElement('button');
                    changeType.type = 'button';
                    changeType.className = 'rounded-lg border border-amber-300 px-3 py-2 text-center font-bold text-amber-900 hover:bg-amber-50';
                    changeType.textContent = 'Cambiar tipo';
                    changeType.dataset.changeCaseType = '';
                    const investigation = document.createElement('a');
                    const investigationUrl = new URL('{{ route('modules.investigations.index') }}', window.location.origin);
                    investigationUrl.searchParams.set('case', caseItem.folio);
                    investigation.href = investigationUrl.toString();
                    investigation.className = 'rounded-lg bg-emerald-700 px-3 py-2 text-center font-bold text-white hover:bg-emerald-800';
                    investigation.dataset.investigationLink = '';
                    investigation.textContent = 'Iniciar investigación';
                    actions.append(preview, changeType, investigation);
                    actionCell.append(actions);
                    row.append(actionCell);
                    tableBody.prepend(row);
                });

                tableBody.querySelectorAll('[data-case-row]').forEach((row) => {
                    const override = typeOverrides[row.dataset.caseFolio];
                    if (override) {
                        row.dataset.caseType = override;
                        row.querySelector('[data-case-type-label]').textContent = override;
                    }
                    row.querySelector('[data-form-review]').classList.toggle('hidden', ! typeReviews[row.dataset.caseFolio]);
                    updateInvestigationLink(row);
                });

                tableBody.addEventListener('click', (event) => {
                    const button = event.target.closest('[data-change-case-type]');
                    if (button) openTypeDialog(button.closest('[data-case-row]'));
                });
                typeSelect.addEventListener('change', updateTypeWarning);
                typeDialog.querySelector('[data-cancel-type-change]').addEventListener('click', () => typeDialog.close());
                typeDialog.querySelector('[data-confirm-type-change]').addEventListener('click', () => {
                    if (! activeCaseRow) return;
                    const previousType = activeCaseRow.dataset.caseType;
                    const requiresUpdate = isDiseaseType(previousType) !== isDiseaseType(typeSelect.value);
                    activeCaseRow.dataset.caseType = typeSelect.value;
                    activeCaseRow.querySelector('[data-case-type-label]').textContent = typeSelect.value;
                    typeOverrides[activeCaseRow.dataset.caseFolio] = typeSelect.value;
                    localStorage.setItem(typeStorageKey, JSON.stringify(typeOverrides));
                    if (requiresUpdate) typeReviews[activeCaseRow.dataset.caseFolio] = true;
                    localStorage.setItem(reviewStorageKey, JSON.stringify(typeReviews));
                    activeCaseRow.querySelector('[data-form-review]').classList.toggle('hidden', ! typeReviews[activeCaseRow.dataset.caseFolio]);
                    const previewDialog = document.querySelector(`[data-case-preview-dialog][data-case-folio="${activeCaseRow.dataset.caseFolio}"]`);
                    const typeTerm = [...(previewDialog?.querySelectorAll('dt') || [])].find((item) => item.textContent.trim() === 'Tipo');
                    if (typeTerm?.nextElementSibling) typeTerm.nextElementSibling.textContent = typeSelect.value;
                    const storedCase = storedCases.find((item) => item.folio === activeCaseRow.dataset.caseFolio);
                    if (storedCase) {
                        storedCase.type = typeSelect.value;
                        localStorage.setItem(storageKey, JSON.stringify(storedCases));
                    }
                    updateInvestigationLink(activeCaseRow);
                    typeDialog.close();
                });

                document.querySelector('[data-case-count]').textContent = {{ $cases->count() }} + storedCases.length;
                document.querySelectorAll('[data-close-stored-dialog]').forEach((button) => button.addEventListener('click', () => dialog.close()));
            })();
        </script>
    @endpush
</x-layouts.app>
