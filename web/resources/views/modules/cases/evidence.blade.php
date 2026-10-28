<x-layouts.app title='Evidencias del caso · GIATEP'>
    <div class='mx-auto w-full max-w-[1440px] space-y-6'>
        <header class='border-b border-slate-200 pb-5'>
            <a href='{{ route('modules.cases.index') }}' class='inline-flex items-center gap-1.5 text-xs font-bold text-sky-700 hover:text-sky-900'>← Volver a casos</a>
            <p class='mt-3 text-xs font-bold uppercase tracking-wider text-sky-700'>Expediente {{ $case->folio }}</p>
            <h1 class='mt-1 text-2xl font-black tracking-tight text-slate-950 sm:text-3xl'>Evidencias del caso</h1>
            <p class='mt-1 text-sm text-slate-600'>Archivos y antecedentes documentales asociados al caso.</p>
        </header>

        @if($errors->any())
            <div class='rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800' role='alert'>{{ $errors->first() }}</div>
        @endif

        @if(auth()->user()->hasPermission('cases.create'))
            <form method='POST' action='{{ route('modules.cases.evidence.store', ['case' => $case->folio]) }}' enctype='multipart/form-data' class='card p-6'>
                @csrf
                <h2 class='text-lg font-black text-slate-950'>Agregar nueva evidencia</h2>
                <p class='mt-1 text-xs text-slate-600'>Puede seleccionar hasta 10 archivos de 20 MB cada uno.</p>
                <input class='input mt-5 bg-white text-sm' type='file' name='evidence_files[]' multiple required accept='.pdf,.doc,.docx,.xls,.xlsx,image/*,audio/*,video/*'>
                <div class='mt-5 flex justify-end'>
                    <button class='btn-primary' type='submit'>Guardar evidencia</button>
                </div>
            </form>
        @endif

        <section class='card p-6'>
            <div class='flex flex-col gap-4 border-b border-slate-200 pb-5 sm:flex-row sm:items-end sm:justify-between'>
                <div>
                    <h2 class='text-lg font-black text-slate-950'>Evidencias registradas</h2>
                    <p class='mt-1 text-xs text-slate-500'><span data-evidence-count>{{ $case->evidences->count() }}</span> archivos</p>
                </div>
                <label class='w-full sm:w-72 sm:shrink-0'>
                    <span class='label text-xs'>Filtrar por tipo</span>
                    <select class='input text-sm' data-evidence-filter>
                        <option value='all'>Todos los archivos</option>
                        <option value='image'>Imágenes</option>
                        <option value='document'>Documentos</option>
                        <option value='video'>Videos</option>
                        <option value='audio'>Audios</option>
                    </select>
                </label>
            </div>
            <div class='mt-5 grid items-start gap-3 sm:grid-cols-2 lg:grid-cols-3' data-evidence-list>
                @forelse($case->evidences as $evidence)
                    @php
                        $mimeType = (string) $evidence->mime_type;
                        $evidenceCategory = str_starts_with($mimeType, 'image/') ? 'image'
                            : (str_starts_with($mimeType, 'video/') ? 'video'
                                : (str_starts_with($mimeType, 'audio/') ? 'audio' : 'document'));
                    @endphp
                    <article class='rounded-xl border border-slate-200 bg-slate-50 p-4' data-evidence-item data-evidence-category='{{ $evidenceCategory }}'>
                        <strong class='block truncate text-sm text-slate-900' title='{{ $evidence->original_name }}'>{{ $evidence->original_name }}</strong>
                        <span class='mt-1 block text-[11px] uppercase text-slate-500'>{{ strtoupper(pathinfo($evidence->original_name, PATHINFO_EXTENSION)) }} · {{ max(1, round($evidence->size / 1024)) }} KB</span>
                        @if(in_array($evidence->mime_type, ['application/pdf', 'image/jpeg', 'image/png', 'image/webp'], true))
                            @if(str_starts_with($evidence->mime_type, 'image/'))
                                <img src='{{ route('modules.cases.evidence.preview', ['case' => $case->folio, 'evidence' => $evidence->id]) }}' alt='{{ $evidence->original_name }}' loading='lazy' title='Doble clic para ampliar' class='mt-4 max-h-80 w-full cursor-zoom-in rounded-lg border border-slate-200 bg-white object-contain' data-expandable-evidence='{{ route('modules.cases.evidence.preview', ['case' => $case->folio, 'evidence' => $evidence->id]) }}' data-expandable-evidence-title='{{ $evidence->original_name }}'>
                            @else
                                <div class='relative mt-4 h-64 overflow-hidden rounded-lg border border-slate-200 bg-white'>
                                    <iframe src='{{ route('modules.cases.evidence.preview', ['case' => $case->folio, 'evidence' => $evidence->id]) }}' title='{{ $evidence->original_name }}' loading='lazy' class='h-full w-full'></iframe>
                                    <button type='button' aria-label='Doble clic para ampliar {{ $evidence->original_name }}' title='Doble clic para ampliar' class='absolute inset-0 size-full cursor-zoom-in bg-transparent' data-expandable-evidence='{{ route('modules.cases.evidence.preview', ['case' => $case->folio, 'evidence' => $evidence->id]) }}' data-expandable-evidence-title='{{ $evidence->original_name }}'></button>
                                </div>
                                <a href='{{ route('modules.cases.evidence.preview', ['case' => $case->folio, 'evidence' => $evidence->id]) }}' target='_blank' rel='noopener' class='btn-secondary mt-2 inline-flex w-full justify-center py-1.5 text-xs'>Abrir documento</a>
                            @endif
                        @endif
                        <a href='{{ route('modules.cases.evidence.download', ['case' => $case->folio, 'evidence' => $evidence->id]) }}' class='btn-secondary mt-2 inline-flex w-full justify-center py-1.5 text-xs'>Descargar</a>
                        @if(auth()->user()->hasPermission('cases.create'))
                            <form method='POST' action='{{ route('modules.cases.evidence.delete', ['case' => $case->folio, 'evidence' => $evidence->id]) }}' class='mt-2' data-delete-evidence>
                                @csrf
                                @method('DELETE')
                                <button type='submit' class='btn-secondary w-full justify-center border-red-200 py-1.5 text-xs font-bold text-red-700 hover:border-red-300 hover:bg-red-50'>Eliminar evidencia</button>
                            </form>
                        @endif
                    </article>
                @empty
                    <p class='rounded-xl border border-dashed border-slate-300 bg-slate-50 p-5 text-sm text-slate-500 sm:col-span-2 lg:col-span-3' data-evidence-empty>Este caso todavía no tiene evidencias adjuntas.</p>
                @endforelse
                <p class='hidden rounded-xl border border-dashed border-slate-300 bg-slate-50 p-5 text-sm text-slate-500 sm:col-span-2 lg:col-span-3' data-evidence-no-results>No hay archivos de este tipo.</p>
            </div>
        </section>

        <dialog class='fixed inset-3 m-0 h-[calc(100dvh-1.5rem)] w-[calc(100vw-1.5rem)] max-h-none max-w-none rounded-xl border border-slate-200 bg-slate-950 p-3 shadow-2xl backdrop:bg-slate-950/85 sm:inset-5 sm:h-[calc(100dvh-2.5rem)] sm:w-[calc(100vw-2.5rem)]' data-image-dialog>
            <div class='mb-2 flex items-center justify-between gap-4'>
                <p class='truncate text-sm font-bold text-white' data-expanded-evidence-title></p>
                <button type='button' class='shrink-0 rounded-lg bg-white/10 px-4 py-2 text-sm font-bold text-white hover:bg-white/20' data-close-image>Cerrar</button>
            </div>
            <div class='grid h-[calc(100%-3.25rem)] min-h-0 place-items-center overflow-hidden' data-expanded-evidence-container>
                <img src='' alt='' class='hidden h-full w-full object-contain' data-expanded-image>
                <iframe src='' title='' class='hidden h-full w-full rounded-lg bg-white' data-expanded-document></iframe>
            </div>
        </dialog>

    </div>

    @push('scripts')
        <script>
            (() => {
                document.querySelectorAll('[data-delete-evidence]').forEach((form) => form.addEventListener('submit', (event) => {
                    window.alert('Advertencia: esta evidencia se eliminará definitivamente y no se podrá recuperar.');
                    if (!window.confirm('¿Confirmas que deseas eliminar esta evidencia?')) event.preventDefault();
                }));

                const evidenceFilter = document.querySelector('[data-evidence-filter]');
                const evidenceItems = [...document.querySelectorAll('[data-evidence-item]')];
                const evidenceCount = document.querySelector('[data-evidence-count]');
                const emptyState = document.querySelector('[data-evidence-empty]');
                const noResults = document.querySelector('[data-evidence-no-results]');

                evidenceFilter?.addEventListener('change', () => {
                    let visibleCount = 0;

                    evidenceItems.forEach((item) => {
                        const isVisible = evidenceFilter.value === 'all' || item.dataset.evidenceCategory === evidenceFilter.value;
                        item.classList.toggle('hidden', !isVisible);
                        if (isVisible) visibleCount++;
                    });

                    if (evidenceCount) evidenceCount.textContent = visibleCount;
                    emptyState?.classList.toggle('hidden', evidenceItems.length > 0);
                    noResults?.classList.toggle('hidden', visibleCount > 0 || evidenceItems.length === 0);
                });

                const dialog = document.querySelector('[data-image-dialog]');
                const expandedImage = dialog.querySelector('[data-expanded-image]');
                const expandedDocument = dialog.querySelector('[data-expanded-document]');
                const expandedTitle = dialog.querySelector('[data-expanded-evidence-title]');

                document.querySelectorAll('[data-expandable-evidence]').forEach((evidence) => evidence.addEventListener('dblclick', () => {
                    const isImage = evidence instanceof HTMLImageElement;
                    expandedTitle.textContent = evidence.dataset.expandableEvidenceTitle;
                    expandedImage.classList.toggle('hidden', !isImage);
                    expandedDocument.classList.toggle('hidden', isImage);
                    if (isImage) {
                        expandedImage.src = evidence.dataset.expandableEvidence;
                        expandedImage.alt = evidence.dataset.expandableEvidenceTitle;
                    } else {
                        expandedDocument.src = evidence.dataset.expandableEvidence;
                        expandedDocument.title = evidence.dataset.expandableEvidenceTitle;
                    }
                    dialog.showModal();
                }));

                dialog.querySelector('[data-close-image]').addEventListener('click', () => dialog.close());
                dialog.addEventListener('close', () => {
                    expandedImage.removeAttribute('src');
                    expandedDocument.removeAttribute('src');
                    expandedTitle.textContent = '';
                });
                dialog.addEventListener('click', (event) => {
                    if (event.target === dialog) dialog.close();
                });
            })();
        </script>
    @endpush
</x-layouts.app>
