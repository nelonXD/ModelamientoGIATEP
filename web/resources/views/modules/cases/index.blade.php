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
            <div class='grid gap-3 border-b border-slate-200 p-5 md:grid-cols-[1fr_14rem_14rem]'>
                <label><span class='label'>Buscar caso</span><input type='search' class='input' placeholder='Folio, persona o establecimiento'></label>
                <label><span class='label'>Tipo</span><select class='input'><option>Todos los tipos</option><option>Accidente del trabajo</option><option>Accidente de trayecto</option><option>Enfermedad profesional</option></select></label>
                <label><span class='label'>Estado</span><select class='input'><option>Todos los estados</option><option>Borrador</option><option>En revisión</option><option>Cerrado</option></select></label>
            </div>
            <div class='overflow-x-auto'>
                <table class='min-w-full divide-y divide-slate-200 text-left text-sm'>
                    <thead class='bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-500'><tr><th>Folio</th><th>Persona afectada</th><th>Tipo</th><th>Establecimiento</th><th>Fecha</th><th>Estado</th><th><span class='sr-only'>Acciones</span></th></tr></thead>
                    <tbody class='divide-y divide-slate-100 bg-white' data-case-list>
                        @foreach($cases as $case)
                            <tr class='transition hover:bg-slate-50'>
                                <td><span class='font-bold text-blue-900'>{{ $case['folio'] }}</span></td><td>{{ $case['person'] }}</td><td>{{ $case['type'] }}</td><td>{{ $case['establishment'] }}</td><td class='whitespace-nowrap'>{{ $case['date'] }}</td>
                                <td><span class='status {{ $case['status'] === 'Borrador' ? 'status-pending' : 'bg-sky-100 text-sky-800' }}'>{{ $case['status'] }}</span></td>
                                <td><div class='flex min-w-44 justify-end gap-3'><button type='button' class='font-bold text-blue-800 hover:text-blue-950' data-case-preview='case-preview-{{ $loop->iteration }}'>Previsualizar</button><a href='{{ route('modules.investigations.index', ['case' => $case['folio']]) }}' class='font-bold text-emerald-700 hover:text-emerald-900'>Iniciar investigación</a></div></td>
                            </tr>

                            <dialog id='case-preview-{{ $loop->iteration }}' class='m-auto w-[calc(100%-2rem)] max-w-2xl rounded-2xl border border-slate-200 bg-white p-0 text-slate-900 shadow-2xl backdrop:bg-slate-950/50'>
                                <div class='border-b border-slate-200 p-6'><div class='flex items-start justify-between gap-4'><div><p class='eyebrow'>Previsualización del caso</p><h2 class='mt-2 text-2xl font-black'>{{ $case['folio'] }}</h2><p class='mt-1 text-sm text-slate-500'>Datos ficticios para validar el diseño.</p></div><button type='button' class='flex size-10 items-center justify-center rounded-full bg-slate-100 text-xl font-bold text-slate-600 hover:bg-slate-200' aria-label='Cerrar previsualización' data-close-dialog>×</button></div></div>
                                <div class='max-h-[65vh] space-y-5 overflow-y-auto p-6'><section class='rounded-xl border border-slate-200 p-4'><h3 class='font-black text-blue-950'>Datos del empleador</h3><dl class='mt-3 grid gap-4 sm:grid-cols-2'><div><dt>Razón social</dt><dd>{{ $case['employerName'] }}</dd></div><div><dt>RUT</dt><dd>{{ $case['employerRut'] }}</dd></div><div><dt>Administrador</dt><dd>{{ $case['administrator'] }}</dd></div><div><dt>Dirección</dt><dd>{{ $case['employerAddress'] }}</dd></div></dl></section><section class='rounded-xl border border-slate-200 p-4'><h3 class='font-black text-blue-950'>Persona accidentada</h3><dl class='mt-3 grid gap-4 sm:grid-cols-2'><div><dt>Nombre</dt><dd>{{ $case['person'] }}</dd></div><div><dt>RUT</dt><dd>{{ $case['workerRut'] }}</dd></div><div><dt>Cargo</dt><dd>{{ $case['jobTitle'] }}</dd></div><div><dt>Teléfono</dt><dd>{{ $case['phone'] }}</dd></div></dl></section><section class='rounded-xl border border-slate-200 p-4'><h3 class='font-black text-blue-950'>Relato y circunstancias</h3><dl class='mt-3 grid gap-4 sm:grid-cols-2'><div><dt>Tipo</dt><dd>{{ $case['type'] }}</dd></div><div><dt>Fecha y hora</dt><dd>{{ $case['date'] }} · {{ $case['time'] }}</dd></div><div class='sm:col-span-2'><dt>Lugar</dt><dd>{{ $case['location'] }}</dd></div><div class='sm:col-span-2'><dt>Relato</dt><dd class='text-sm font-normal leading-6 text-slate-600'>{{ $case['summary'] }}</dd></div><div class='sm:col-span-2'><dt>Antecedentes</dt><dd class='text-sm font-normal leading-6 text-slate-600'>{{ $case['witnesses'] }}</dd></div></dl></section><section class='rounded-xl border border-sky-200 bg-sky-50 p-4'><h3 class='font-black text-blue-950'>Medidas y plan de acción</h3><ul class='mt-3 space-y-2'>@foreach($case['measures'] as $measure)<li class='rounded-lg bg-white p-3 text-sm'><strong class='block text-slate-900'>{{ $measure['measure'] }}</strong><span class='text-xs text-slate-500'>Responsable: {{ $measure['owner'] }} · Plazo: {{ $measure['deadline'] }}</span></li>@endforeach</ul></section></div>
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
        <div class='max-h-[65vh] space-y-5 overflow-y-auto p-6'><section class='rounded-xl border border-slate-200 p-4'><h3 class='font-black text-blue-950'>Datos del empleador</h3><dl class='mt-3 grid gap-4 sm:grid-cols-2'><div><dt>Razón social</dt><dd data-stored-preview='employerName'></dd></div><div><dt>RUT</dt><dd data-stored-preview='employerRut'></dd></div><div><dt>Administrador</dt><dd data-stored-preview='administrator'></dd></div><div><dt>Dirección</dt><dd data-stored-preview='employerAddress'></dd></div></dl></section><section class='rounded-xl border border-slate-200 p-4'><h3 class='font-black text-blue-950'>Persona accidentada</h3><dl class='mt-3 grid gap-4 sm:grid-cols-2'><div><dt>Nombre</dt><dd data-stored-preview='person'></dd></div><div><dt>RUT</dt><dd data-stored-preview='workerRut'></dd></div><div><dt>Cargo</dt><dd data-stored-preview='jobTitle'></dd></div><div><dt>Teléfono</dt><dd data-stored-preview='phone'></dd></div></dl></section><section class='rounded-xl border border-slate-200 p-4'><h3 class='font-black text-blue-950'>Relato y circunstancias</h3><dl class='mt-3 grid gap-4 sm:grid-cols-2'><div><dt>Tipo</dt><dd data-stored-preview='type'></dd></div><div><dt>Fecha</dt><dd data-stored-preview='date'></dd></div><div><dt>Hora</dt><dd data-stored-preview='time'></dd></div><div><dt>Lugar</dt><dd data-stored-preview='location'></dd></div><div class='sm:col-span-2'><dt>Relato</dt><dd class='whitespace-pre-line text-sm font-normal leading-6 text-slate-600' data-stored-preview='summary'></dd></div><div class='sm:col-span-2'><dt>Antecedentes</dt><dd class='whitespace-pre-line text-sm font-normal leading-6 text-slate-600' data-stored-preview='witnesses'></dd></div></dl></section><section class='rounded-xl border border-sky-200 bg-sky-50 p-4'><h3 class='font-black text-blue-950'>Medidas y plan de acción</h3><ul class='mt-3 space-y-2' data-stored-measures></ul></section></div>
        <div class='flex flex-col-reverse gap-3 border-t border-slate-200 bg-slate-50 p-5 sm:flex-row sm:justify-end'><button type='button' class='btn-secondary' data-close-stored-dialog>Cerrar</button><a href='{{ route('modules.investigations.index') }}' class='btn-primary inline-flex' data-stored-investigation>Iniciar investigación →</a></div>
    </dialog>
    @push('scripts')
        <script>
            document.querySelectorAll('[data-case-preview]').forEach((button) => button.addEventListener('click', () => document.getElementById(button.dataset.casePreview)?.showModal()));
            document.querySelectorAll('[data-close-dialog]').forEach((button) => button.addEventListener('click', () => button.closest('dialog')?.close()));

            (() => {
                const storageKey = 'giatep.demo.cases';
                const tableBody = document.querySelector('[data-case-list]');
                const dialog = document.querySelector('[data-stored-case-dialog]');
                let storedCases = [];

                try {
                    const parsed = JSON.parse(localStorage.getItem(storageKey) || '[]');
                    storedCases = Array.isArray(parsed) ? parsed : [];
                } catch (error) {
                    storedCases = [];
                }

                const text = (value) => String(value || 'Sin informar');
                const appendCell = (row, content, className = '') => {
                    const cell = document.createElement('td');
                    cell.textContent = text(content);
                    cell.className = className;
                    row.append(cell);
                };

                const openStoredPreview = (caseItem) => {
                    dialog.querySelectorAll('[data-stored-preview]').forEach((output) => {
                        output.textContent = text(caseItem[output.dataset.storedPreview]);
                    });

                    const list = dialog.querySelector('[data-stored-measures]');
                    const measures = Array.isArray(caseItem.measures) ? caseItem.measures : [];
                    list.replaceChildren();
                    (measures.length ? measures : [{ measure: 'No hay medidas propuestas.', owner: 'Pendiente', deadline: 'Sin definir' }]).forEach((action) => {
                        const item = document.createElement('li');
                        item.className = 'rounded-lg bg-white p-3 text-sm';
                        const title = document.createElement('strong');
                        title.className = 'block text-slate-900';
                        title.textContent = text(action.measure);
                        const detail = document.createElement('span');
                        detail.className = 'text-xs text-slate-500';
                        detail.textContent = `Responsable: ${text(action.owner)} · Plazo: ${text(action.deadline)}`;
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
                    appendCell(row, caseItem.folio, 'font-bold text-blue-900');
                    appendCell(row, caseItem.person);
                    appendCell(row, caseItem.type);
                    appendCell(row, caseItem.establishment);
                    appendCell(row, caseItem.date, 'whitespace-nowrap');

                    const statusCell = document.createElement('td');
                    const status = document.createElement('span');
                    status.className = 'status status-pending';
                    status.textContent = text(caseItem.status);
                    statusCell.append(status);
                    row.append(statusCell);

                    const actionCell = document.createElement('td');
                    const actions = document.createElement('div');
                    actions.className = 'flex min-w-44 justify-end gap-3';
                    const preview = document.createElement('button');
                    preview.type = 'button';
                    preview.className = 'font-bold text-blue-800 hover:text-blue-950';
                    preview.textContent = 'Previsualizar';
                    preview.addEventListener('click', () => openStoredPreview(caseItem));
                    const investigation = document.createElement('a');
                    const investigationUrl = new URL('{{ route('modules.investigations.index') }}', window.location.origin);
                    investigationUrl.searchParams.set('case', caseItem.folio);
                    investigation.href = investigationUrl.toString();
                    investigation.className = 'font-bold text-emerald-700 hover:text-emerald-900';
                    investigation.textContent = 'Iniciar investigación';
                    actions.append(preview, investigation);
                    actionCell.append(actions);
                    row.append(actionCell);
                    tableBody.prepend(row);
                });

                document.querySelector('[data-case-count]').textContent = {{ $cases->count() }} + storedCases.length;
                document.querySelectorAll('[data-close-stored-dialog]').forEach((button) => button.addEventListener('click', () => dialog.close()));
            })();
        </script>
    @endpush
</x-layouts.app>
