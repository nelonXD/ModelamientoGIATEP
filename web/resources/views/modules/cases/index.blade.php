<x-layouts.app title='Casos registrados · GIATEP'>
    <div class='space-y-6'>
        <div class='flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-slate-200 pb-5'>
            <div>
                <p class='text-xs font-bold uppercase tracking-wider text-sky-700'>Sistema Institucional GIATEP · Ley 16.744</p>
                <h1 class='mt-1 text-2xl sm:text-3xl font-black tracking-tight text-slate-950'>Casos registrados</h1>
                <p class='mt-1 text-sm text-slate-600'>Expedientes y notificaciones de accidentes y enfermedades profesionales de la Red Comunal de Salud.</p>
            </div>
            @if(auth()->user()->hasPermission('cases.create'))
                <a href='{{ route('modules.cases.create') }}' class='btn-primary inline-flex items-center gap-2 text-sm shadow-sm'>
                    <span aria-hidden='true' class='text-base font-bold leading-none'>+</span> Registrar caso
                </a>
            @endif
        </div>

        <section class='card overflow-hidden'>
            <div class='grid gap-3 border-b border-slate-200 bg-slate-50/70 p-4 md:grid-cols-[1fr_15rem_15rem]'>
                <label>
                    <span class='label text-xs font-bold uppercase tracking-wider text-slate-500'>Buscar caso</span>
                    <input type='search' class='input text-sm py-2' placeholder='Buscar por folio, persona o establecimiento...' data-search-input>
                </label>
                <label>
                    <span class='label text-xs font-bold uppercase tracking-wider text-slate-500'>Tipo de caso</span>
                    <select class='input text-sm py-2' data-filter-type>
                        <option value=''>Todos los tipos</option>
                        @foreach($caseTypes as $type)
                            <option value='{{ $type->value }}'>{{ $type->label }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    <span class='label text-xs font-bold uppercase tracking-wider text-slate-500'>Estado</span>
                    <select class='input text-sm py-2' data-filter-status>
                        <option value=''>Todos los estados</option>
                        @foreach($caseStatuses as $status)
                            <option value='{{ $status }}'>{{ $status }}</option>
                        @endforeach
                    </select>
                </label>
            </div>
            <div class='overflow-x-auto'>
                <table class='min-w-full divide-y divide-slate-200 text-left text-sm'>
                    <thead class='bg-slate-50 text-xs font-bold uppercase tracking-wider text-slate-600'>
                        <tr>
                            <th class='py-3.5 px-4'>Expediente</th>
                            <th class='py-3.5 px-4'>RUT</th>
                            <th class='py-3.5 px-4'>Nombre</th>
                            <th class='py-3.5 px-4'>Tipo</th>
                            <th class='py-3.5 px-4'>Estado</th>
                            <th class='py-3.5 px-4 text-center'>Acciones</th>
                        </tr>
                    </thead>
                    <tbody class='divide-y divide-slate-100 bg-white' data-case-list>
                        @forelse($cases as $case)
                            <tr class='cursor-pointer transition hover:bg-slate-50/80' title='Doble clic para ver antecedentes' data-case-row data-case-preview-target='case-preview-{{ $loop->iteration }}' data-case-folio='{{ $case['folio'] }}' data-case-type='{{ $case['type'] }}' data-case-status='{{ $case['status'] }}' data-has-investigation='{{ $case['has_investigation'] ? 'true' : 'false' }}'>
                                <td class='whitespace-nowrap py-4 px-4 font-bold text-slate-950'>
                                    <span class='text-blue-950 font-black text-sm'>{{ $case['folio'] }}</span>
                                    <span class='mt-1.5 hidden w-fit rounded-md bg-amber-100 px-2 py-0.5 text-[10px] font-black uppercase tracking-wide text-amber-900' data-form-review>Formulario por actualizar</span>
                                </td>
                                <td class='whitespace-nowrap py-4 px-4 font-mono text-xs text-slate-700'>{{ $case['workerRut'] }}</td>
                                <td class='py-4 px-4'>
                                    <strong class='font-bold text-slate-900 block'>{{ $case['person'] }}</strong>
                                    <span class='text-xs text-slate-500 block mt-0.5'>{{ $case['jobTitle'] }}</span>
                                </td>
                                <td class='py-4 px-4'>
                                    <span class='inline-flex rounded-full px-2.5 py-1 text-xs font-bold leading-none border {{ str_contains(strtolower($case['type']), 'enfermedad') ? 'bg-amber-50 text-amber-900 border-amber-200' : 'bg-blue-50 text-blue-900 border-blue-200' }}' data-case-type-label>
                                        {{ $case['type'] }}
                                    </span>
                                    @unless($case['requires_investigation'])
                                        <span class='mt-1 block text-[11px] font-semibold text-slate-500' data-investigation-not-applicable>No aplica investigación</span>
                                    @endunless
                                </td>
                                <td class='py-4 px-4 whitespace-nowrap'>
                                    <span class='inline-flex rounded-full px-2.5 py-1 text-xs font-bold border {{ $case['status'] === 'Cerrado' ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : ($case['status'] === 'En investigación' ? 'bg-blue-50 text-blue-900 border-blue-200' : ($case['status'] === 'Registrado' ? 'bg-cyan-50 text-cyan-900 border-cyan-200' : 'bg-slate-100 text-slate-700 border-slate-200')) }}'>
                                        {{ $case['status'] }}
                                    </span>
                                </td>
                                <td class='py-4 px-4'>
                                    <div class='flex min-w-44 flex-col gap-1.5'>
                                        @if($case['has_investigation'])
                                            <a href='{{ route('modules.investigations.index', ['case' => $case['folio']]) }}' class='text-center text-[11px] font-bold text-emerald-800 hover:text-emerald-950 hover:underline'>Ver expediente en curso ({{ $case['investigation_id'] ?? 'INV' }}) →</a>
                                        @elseif(! $case['requires_investigation'])
                                            <button type='button' disabled class='cursor-not-allowed rounded-lg border border-slate-200 bg-slate-100 px-3 py-1.5 text-center text-xs font-bold text-slate-400 opacity-70' title='Este tipo de caso no requiere investigación bajo la Ley 16.744.' data-investigation-not-applicable>Investigación no aplica</button>
                                        @else
                                            <form method='POST' action='{{ route('modules.investigations.start', ['case' => $case['folio']]) }}' data-investigation-form>
                                                @csrf
                                                <input type='hidden' name='case_type' value='{{ $case['type'] }}' data-investigation-case-type>
                                                <button type='submit' class='w-full rounded-lg bg-emerald-700 px-3 py-1.5 text-center text-xs font-bold text-white shadow-sm hover:bg-emerald-800'>Iniciar investigación</button>
                                            </form>
                                        @endif
                                        @if($case['can_close'])
                                            <form method='POST' action='{{ route('modules.cases.close', ['case' => $case['folio']]) }}' onsubmit="return confirm('¿Confirma el cierre de este caso? Esta acción marca el caso como cerrado.')">
                                                @csrf
                                                <button type='submit' class='w-full rounded-lg border border-emerald-300 bg-emerald-50 px-3 py-1.5 text-center text-xs font-bold text-emerald-800 hover:bg-emerald-100'>Cerrar caso</button>
                                            </form>
                                        @endif
                                        <div class='flex items-center gap-1.5 mt-0.5'>
                                            <a href='{{ route('modules.cases.edit', ['case' => $case['folio']]) }}' class='btn-secondary flex-1 py-1 px-2 text-center text-[11px] font-semibold text-slate-700 hover:bg-slate-50'>✎ Editar caso</a>
                                            <a href='{{ route('modules.cases.evidence.index', ['case' => $case['folio']]) }}' class='btn-secondary py-1 px-2 text-center text-[11px] font-semibold text-emerald-800 hover:bg-emerald-50'>Ver evidencia</a>
                                            <button type='button' class='btn-secondary py-1 px-2 text-[11px] font-semibold text-blue-900 hover:bg-blue-50' title='Editar tipo de caso' data-change-case-type>✎ Editar tipo de caso</button>
                                            @if(auth()->user()->hasPermission('observations.read'))
                                                <a href='{{ route('modules.observations.index', ['case' => $case['folio']]) }}' class='btn-secondary py-1 px-2 text-[11px] font-semibold text-violet-800 hover:bg-violet-50' title='Ver observaciones'>Observaciones</a>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            </tr>

                            <dialog id='case-preview-{{ $loop->iteration }}' class='fixed inset-y-0 right-0 left-0 m-0 h-auto w-auto max-h-none max-w-none rounded-none border-0 bg-white p-0 text-slate-900 shadow-2xl backdrop:bg-slate-950/50 lg:rounded-l-2xl lg:rounded-r-none' data-case-preview-dialog data-case-folio='{{ $case['folio'] }}'>
                                <div class='shrink-0 border-b border-slate-200 px-6 py-5 bg-slate-50/50'>
                                    <div class='flex items-start justify-between gap-4'>
                                        <div>
                                            <span class='text-[10px] font-bold uppercase tracking-wider text-sky-700'>Expediente Oficial · Ley 16.744</span>
                                            <h2 class='mt-1 text-2xl font-black text-slate-950'>{{ $case['folio'] }}</h2>
                                            <p class='text-xs text-slate-500 font-medium'>Establecimiento: {{ $case['establishment'] }}</p>
                                        </div>
                                        <button type='button' class='flex size-8 items-center justify-center rounded-full bg-slate-100 text-lg font-bold text-slate-500 hover:bg-slate-200 transition' aria-label='Cerrar previsualización' data-close-dialog>×</button>
                                    </div>
                                </div>
                                <div class='min-h-0 flex-1 space-y-4 overflow-y-auto p-6'>
                                    <section class='rounded-xl border border-slate-200 p-4 bg-white'>
                                        <h3 class='text-xs font-bold uppercase tracking-wider text-slate-500 border-b border-slate-100 pb-2'>Datos del empleador</h3>
                                        <dl class='mt-3 grid gap-3 sm:grid-cols-2 text-xs'>
                                            <div><dt>Razón social</dt><dd class='text-sm'>{{ $case['employerName'] }}</dd></div>
                                            <div><dt>RUT</dt><dd class='text-sm font-mono'>{{ $case['employerRut'] }}</dd></div>
                                            <div><dt>Administrador</dt><dd class='text-sm'>{{ $case['administrator'] }}</dd></div>
                                            <div><dt>Dirección</dt><dd class='text-sm'>{{ $case['employerAddress'] }}</dd></div>
                                        </dl>
                                    </section>
                                    <section class='rounded-xl border border-slate-200 p-4 bg-white'>
                                        <h3 class='text-xs font-bold uppercase tracking-wider text-slate-500 border-b border-slate-100 pb-2'>Persona accidentada</h3>
                                        <dl class='mt-3 grid gap-3 sm:grid-cols-2 text-xs'>
                                            <div><dt>Nombre</dt><dd class='text-sm'>{{ $case['person'] }}</dd></div>
                                            <div><dt>RUT</dt><dd class='text-sm font-mono'>{{ $case['workerRut'] }}</dd></div>
                                            <div><dt>Cargo</dt><dd class='text-sm'>{{ $case['jobTitle'] }}</dd></div>
                                            <div><dt>Teléfono</dt><dd class='text-sm'>{{ $case['phone'] }}</dd></div>
                                        </dl>
                                    </section>
                                    <section class='rounded-xl border border-slate-200 p-4 bg-white'>
                                        <h3 class='text-xs font-bold uppercase tracking-wider text-slate-500 border-b border-slate-100 pb-2'>Antecedentes del caso</h3>
                                        <dl class='mt-3 grid gap-3 sm:grid-cols-2 text-xs'>
                                            <div><dt>Tipo</dt><dd class='text-sm font-bold text-blue-950'>{{ $case['type'] }}</dd></div>
                                            <div><dt>Fecha y hora</dt><dd class='text-sm'>{{ $case['date'] }} · {{ $case['time'] }}</dd></div>
                                            <div class='sm:col-span-2'><dt>Lugar</dt><dd class='text-sm'>{{ $case['location'] }}</dd></div>
                                            <div class='sm:col-span-2'><dt>Relato</dt><dd class='text-xs leading-relaxed text-slate-700 bg-slate-50 p-3 rounded-lg border border-slate-200'>{{ $case['summary'] }}</dd></div>
                                            <div class='sm:col-span-2'><dt>Testigos y antecedentes</dt><dd class='text-xs text-slate-600'>{{ $case['witnesses'] }}</dd></div>
                                        </dl>
                                    </section>
                                    <section class='rounded-xl border border-slate-200 p-4 bg-slate-50/50'>
                                        <h3 class='text-xs font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200 pb-2'>Evidencias adjuntas</h3>
                                        <ul class='mt-3 grid gap-2 sm:grid-cols-2'>
                                            @forelse($case['evidenceFiles'] as $file)
                                                <li class='rounded-lg border border-slate-200 bg-white p-2.5 text-xs'>
                                                    <strong class='block truncate text-slate-900 font-semibold'>{{ $file['name'] }}</strong>
                                                    <span class='text-[10px] text-slate-500 uppercase'>{{ strtoupper(pathinfo($file['name'], PATHINFO_EXTENSION)) }} · {{ max(1, round($file['size'] / 1024)) }} KB</span>
                                                </li>
                                            @empty
                                                <li class='text-xs text-slate-500 italic sm:col-span-2'>Sin evidencias adjuntas registradas.</li>
                                            @endforelse
                                        </ul>
                                    </section>
                                </div>
                                <div class='shrink-0 flex flex-col-reverse gap-2 border-t border-slate-200 bg-slate-50 px-6 py-4 sm:flex-row sm:justify-end'>
                                    <button type='button' class='btn-secondary text-xs py-2 px-4' data-close-dialog>Cerrar</button>
                                    @if($case['has_investigation'])
                                        <a href='{{ route('modules.investigations.index', ['case' => $case['folio']]) }}' class='btn-primary inline-flex text-xs py-2 px-4'>Ver expediente en curso ({{ $case['investigation_id'] ?? 'INV' }}) →</a>
                                    @elseif(! $case['requires_investigation'])
                                        <button type='button' disabled class='btn-secondary cursor-not-allowed opacity-50 text-xs py-2 px-4' title='Este tipo de caso no requiere investigación bajo la Ley 16.744.'>Investigación no aplica</button>
                                    @else
                                        <form method='POST' action='{{ route('modules.investigations.start', ['case' => $case['folio']]) }}' data-investigation-form>
                                            @csrf
                                            <input type='hidden' name='case_type' value='{{ $case['type'] }}' data-investigation-case-type>
                                            <button type='submit' class='btn-primary inline-flex text-xs py-2 px-4'>Iniciar investigación →</button>
                                        </form>
                                    @endif
                                    @if($case['can_close'])
                                        <form method='POST' action='{{ route('modules.cases.close', ['case' => $case['folio']]) }}' onsubmit="return confirm('¿Confirma el cierre de este caso? Esta acción marca el caso como cerrado.')">
                                            @csrf
                                            <button type='submit' class='btn-primary inline-flex text-xs py-2 px-4'>Cerrar caso</button>
                                        </form>
                                    @endif
                                </div>
                            </dialog>
                        @empty
                            <tr>
                                <td colspan='7' class='py-12 px-4 text-center text-slate-500'>
                                    <p class='font-bold text-slate-700'>No hay casos registrados en el sistema.</p>
                                    <p class='mt-1 text-xs text-slate-400'>Utilice el botón "Registrar caso" para ingresar el primer expediente.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class='flex flex-col gap-2 border-t border-slate-200 px-5 py-3.5 text-xs text-slate-500 sm:flex-row sm:items-center sm:justify-between bg-slate-50/50'>
                <p>Mostrando <span data-case-count class='font-bold text-slate-700'>{{ $cases->count() }}</span> casos registrados.</p>
                <p class='text-slate-400'>Dirección Comunal de Salud de Los Ángeles</p>
            </div>
        </section>
    </div>

    <dialog class='m-auto w-[calc(100%-2rem)] max-w-xl rounded-2xl border border-slate-200 bg-white p-0 text-slate-900 shadow-2xl backdrop:bg-slate-950/50' data-case-type-dialog>
        <div class='border-b border-slate-200 p-6'>
            <p class='text-xs font-bold uppercase tracking-wider text-sky-700'>Actualización de clasificación</p>
            <h2 class='mt-1 text-2xl font-black text-slate-950'>Cambiar tipo de caso</h2>
            <p class='mt-1 text-xs text-slate-600'>Modifique la clasificación del evento ante la aparición de nuevos antecedentes periciales.</p>
        </div>
        <div class='space-y-4 p-6'>
            <div class='rounded-xl bg-slate-50 p-4 text-xs border border-slate-200'>
                <span class='text-slate-500 font-bold uppercase tracking-wider'>Expediente seleccionado</span>
                <strong class='mt-1 block text-base font-black text-blue-950' data-type-case-folio></strong>
            </div>
            <label>
                <span class='label'>Nuevo tipo de caso</span>
                <select class='input text-sm' data-new-case-type>
                    @foreach($caseTypes as $type)
                        <option value='{{ $type->value }}'>{{ $type->label }}</option>
                    @endforeach
                </select>
            </label>
            <div class='hidden rounded-xl border border-amber-300 bg-amber-50 p-4 text-xs text-amber-950' data-type-change-warning>
                <strong class='block text-sm font-bold'>Se debe actualizar el formulario del caso</strong>
                <p class='mt-1 leading-relaxed'>Los antecedentes solicitados para una enfermedad profesional son diferentes a los de un accidente. Revisa y completa el formulario correspondiente antes de continuar con la investigación.</p>
            </div>
        </div>
        <div class='flex flex-col-reverse gap-2 border-t border-slate-200 bg-slate-50 px-6 py-4 sm:flex-row sm:justify-end'>
            <button type='button' class='btn-secondary text-xs py-2 px-4' data-cancel-type-change>Cancelar</button>
            <button type='button' class='btn-primary text-xs py-2 px-4' data-confirm-type-change>Guardar cambio</button>
        </div>
    </dialog>

    @push('scripts')
        <script>
            document.querySelectorAll('[data-case-row]').forEach((row) => row.addEventListener('dblclick', (event) => {
                if (event.target.closest('a, button, input, select, textarea, form')) return;
                document.getElementById(row.dataset.casePreviewTarget)?.showModal();
            }));
            document.querySelectorAll('[data-close-dialog]').forEach((button) => button.addEventListener('click', () => button.closest('dialog')?.close()));
            document.querySelectorAll('[data-case-preview-dialog]').forEach((dialog) => dialog.addEventListener('click', (event) => {
                if (event.target !== dialog) return;
                const bounds = dialog.getBoundingClientRect();
                const clickedInsidePanel = event.clientX >= bounds.left && event.clientX <= bounds.right
                    && event.clientY >= bounds.top && event.clientY <= bounds.bottom;
                if (! clickedInsidePanel) dialog.close();
            }));

            (() => {
                const typeStorageKey = 'giatep.demo.case-types';
                const reviewStorageKey = 'giatep.demo.case-type-reviews';
                const tableBody = document.querySelector('[data-case-list]');
                const typeDialog = document.querySelector('[data-case-type-dialog]');
                const typeSelect = typeDialog.querySelector('[data-new-case-type]');
                const typeWarning = typeDialog.querySelector('[data-type-change-warning]');
                let typeOverrides = {};
                let typeReviews = {};
                let activeCaseRow = null;

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

                const isDiseaseType = (type) => type === 'Enfermedad profesional';
                const updateTypeWarning = () => {
                    const changesForm = activeCaseRow && isDiseaseType(activeCaseRow.dataset.caseType) !== isDiseaseType(typeSelect.value);
                    typeWarning.classList.toggle('hidden', ! changesForm);
                };

                const nonInvestigableTypes = ['Accidente de trayecto', 'Accidente o enfermedad común', 'Accidente común', 'Enfermedad común'];
                const updateInvestigationLink = (row) => {
                    const hasInvestigation = row.dataset.hasInvestigation === 'true';
                    if (hasInvestigation) return;

                    const actionsDiv = row.querySelector('td:last-child > div');
                    if (! actionsDiv) return;

                    const currentType = row.dataset.caseType;
                    const form = actionsDiv.querySelector('[data-investigation-form]');
                    const notApplicableBtn = actionsDiv.querySelector('[data-investigation-not-applicable]');

                    if (nonInvestigableTypes.includes(currentType)) {
                        if (form) {
                            const btn = document.createElement('button');
                            btn.type = 'button';
                            btn.disabled = true;
                            btn.className = 'cursor-not-allowed rounded-lg border border-slate-200 bg-slate-100 px-3 py-1.5 text-center text-xs font-bold text-slate-400 opacity-70';
                            btn.title = 'Este tipo de caso no requiere investigación bajo la Ley 16.744.';
                            btn.dataset.investigationNotApplicable = '';
                            btn.textContent = 'Investigación no aplica';
                            form.replaceWith(btn);
                        }
                    } else {
                        if (notApplicableBtn) {
                            const newForm = document.createElement('form');
                            newForm.method = 'POST';
                            newForm.action = '{{ route('modules.investigations.start', ['case' => '__CASE_FOLIO__']) }}'.replace('__CASE_FOLIO__', encodeURIComponent(row.dataset.caseFolio));
                            newForm.dataset.investigationForm = '';
                            newForm.innerHTML = `<input type="hidden" name="_token" value="{{ csrf_token() }}"><input type="hidden" name="case_type" data-investigation-case-type><button type="submit" class="w-full rounded-lg bg-emerald-700 px-3 py-1.5 text-center text-xs font-bold text-white shadow-sm hover:bg-emerald-800">Iniciar investigación</button>`;
                            newForm.querySelector('[data-investigation-case-type]').value = currentType;
                            notApplicableBtn.replaceWith(newForm);
                        } else if (form) {
                            const caseTypeField = form.querySelector('[data-investigation-case-type]');
                            if (caseTypeField) caseTypeField.value = currentType;
                        }
                    }
                };

                const openTypeDialog = (row) => {
                    activeCaseRow = row;
                    typeDialog.querySelector('[data-type-case-folio]').textContent = row.dataset.caseFolio;
                    typeSelect.value = row.dataset.caseType;
                    updateTypeWarning();
                    typeDialog.showModal();
                };

                tableBody.querySelectorAll('[data-case-row]').forEach((row) => {
                    const override = typeOverrides[row.dataset.caseFolio];
                    if (override) {
                        row.dataset.caseType = override;
                        const label = row.querySelector('[data-case-type-label]');
                        if (label) label.textContent = override;
                    }
                    const review = row.querySelector('[data-form-review]');
                    if (review) review.classList.toggle('hidden', ! typeReviews[row.dataset.caseFolio]);
                    updateInvestigationLink(row);
                });

                tableBody.addEventListener('click', (event) => {
                    const button = event.target.closest('[data-change-case-type]');
                    if (button) openTypeDialog(button.closest('[data-case-row]'));
                });

                document.addEventListener('submit', (event) => {
                    const form = event.target.closest('[data-investigation-form]');
                    if (form && ! window.confirm('Se iniciará una investigación para este caso. ¿Deseas continuar?')) {
                        event.preventDefault();
                    }
                });

                typeSelect.addEventListener('change', updateTypeWarning);
                typeDialog.querySelector('[data-cancel-type-change]').addEventListener('click', () => typeDialog.close());
                typeDialog.querySelector('[data-confirm-type-change]').addEventListener('click', () => {
                    if (! activeCaseRow) return;
                    const previousType = activeCaseRow.dataset.caseType;
                    const requiresUpdate = isDiseaseType(previousType) !== isDiseaseType(typeSelect.value);
                    activeCaseRow.dataset.caseType = typeSelect.value;
                    const label = activeCaseRow.querySelector('[data-case-type-label]');
                    if (label) label.textContent = typeSelect.value;
                    typeOverrides[activeCaseRow.dataset.caseFolio] = typeSelect.value;
                    localStorage.setItem(typeStorageKey, JSON.stringify(typeOverrides));
                    if (requiresUpdate) typeReviews[activeCaseRow.dataset.caseFolio] = true;
                    localStorage.setItem(reviewStorageKey, JSON.stringify(typeReviews));
                    const review = activeCaseRow.querySelector('[data-form-review]');
                    if (review) review.classList.toggle('hidden', ! typeReviews[activeCaseRow.dataset.caseFolio]);
                    const previewDialog = document.querySelector(`[data-case-preview-dialog][data-case-folio="${activeCaseRow.dataset.caseFolio}"]`);
                    const typeTerm = [...(previewDialog?.querySelectorAll('dt') || [])].find((item) => item.textContent.trim() === 'Tipo');
                    if (typeTerm?.nextElementSibling) typeTerm.nextElementSibling.textContent = typeSelect.value;
                    updateInvestigationLink(activeCaseRow);
                    typeDialog.close();
                });

                // Client-side search and filters
                const searchInput = document.querySelector('[data-search-input]');
                const filterType = document.querySelector('[data-filter-type]');
                const filterStatus = document.querySelector('[data-filter-status]');

                const applyFilters = () => {
                    const term = (searchInput?.value || '').toLowerCase().trim();
                    const type = (filterType?.value || '').toLowerCase().trim();
                    const status = (filterStatus?.value || '').toLowerCase().trim();

                    let visibleCount = 0;
                    tableBody.querySelectorAll('[data-case-row]').forEach((row) => {
                        const rowFolio = (row.dataset.caseFolio || '').toLowerCase();
                        const rowType = (row.dataset.caseType || '').toLowerCase();
                        const rowStatus = (row.dataset.caseStatus || '').toLowerCase();
                        const rowText = row.textContent.toLowerCase();

                        const matchesTerm = !term || rowText.includes(term) || rowFolio.includes(term);
                        const matchesType = !type || rowType.includes(type);
                        const matchesStatus = !status || rowStatus.includes(status);

                        const isVisible = matchesTerm && matchesType && matchesStatus;
                        row.classList.toggle('hidden', !isVisible);
                        if (isVisible) visibleCount++;
                    });

                    const counter = document.querySelector('[data-case-count]');
                    if (counter) counter.textContent = visibleCount;
                };

                searchInput?.addEventListener('input', applyFilters);
                filterType?.addEventListener('change', applyFilters);
                filterStatus?.addEventListener('change', applyFilters);
            })();
        </script>
    @endpush
</x-layouts.app>
