<x-layouts.app title="Matriz de factores causales · GIATEP">
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <a href="{{ route('modules.investigations.index') }}" class="text-sm font-bold text-sky-700 hover:text-sky-900">← Volver a investigaciones</a>
                <p class="mt-4 text-xs font-black uppercase tracking-wider text-sky-700">Herramienta de consulta</p>
                <h1 class="mt-1 text-3xl font-black tracking-tight text-slate-950">Matriz de factores causales</h1>
                <p class="mt-2 max-w-3xl text-sm leading-relaxed text-slate-600">Consulta los factores disponibles para clasificar causas en una investigación. Usa el código, categoría o descripción para encontrar un factor.</p>
            </div>
            <div class="rounded-xl border border-sky-200 bg-sky-50 px-5 py-3 text-center sm:text-right">
                <span class="block text-2xl font-black text-sky-900">{{ number_format($activeCount, 0, ',', '.') }}</span>
                <span class="text-xs font-semibold text-sky-800">factores activos</span>
            </div>
        </div>

        <section class="card p-5 sm:p-6" aria-label="Filtros de factores">
            <form method="GET" action="{{ route('modules.cause-factors.index') }}" class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_minmax(14rem,0.45fr)_auto] lg:items-end">
                <label class="block">
                    <span class="label">Buscar en la matriz</span>
                    <input class="input" type="search" name="q" value="{{ $search }}" placeholder="Código, categoría o descripción…">
                </label>
                <label class="block">
                    <span class="label">Categoría</span>
                    <select class="input" name="categoria">
                        <option value="">Todas las categorías</option>
                        @foreach($categories as $category)
                            <option value="{{ $category }}" @selected($selectedCategory === $category)>{{ $category }}</option>
                        @endforeach
                    </select>
                </label>
                <div class="flex gap-2">
                    <button class="btn-primary" type="submit">Buscar</button>
                    @if($search || $selectedCategory)
                        <a class="btn-secondary" href="{{ route('modules.cause-factors.index') }}">Limpiar</a>
                    @endif
                </div>
            </form>
        </section>

        <section class="card overflow-hidden" aria-label="Factores causales">
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-200 px-5 py-4">
                <h2 class="font-black text-slate-950">Factores de la matriz</h2>
                <p class="text-xs text-slate-500">{{ number_format($factors->total(), 0, ',', '.') }} resultados</p>
            </div>

            @if($factors->isEmpty())
                <div class="px-5 py-12 text-center">
                    <p class="font-bold text-slate-800">No encontramos factores con esos filtros.</p>
                    <p class="mt-1 text-sm text-slate-500">Prueba otro término o selecciona todas las categorías.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[58rem] border-collapse text-left text-sm">
                        <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-600">
                            <tr>
                                <th scope="col" class="px-5 py-3">Código</th>
                                <th scope="col" class="px-5 py-3">Categoría / subcategoría</th>
                                <th scope="col" class="px-5 py-3">Factor y descripción</th>
                                <th scope="col" class="px-5 py-3">Página</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($factors as $factor)
                                <tr class="align-top hover:bg-slate-50/80">
                                    <td class="whitespace-nowrap px-5 py-4">
                                        <span class="inline-flex rounded-lg border border-sky-200 bg-sky-50 px-2.5 py-1 font-mono text-xs font-black text-sky-900">{{ $factor->codigo_efectivo }}</span>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="block font-bold text-slate-900">{{ $factor->categoria }}</span>
                                        <span class="mt-1 block text-xs text-slate-500">{{ $factor->subcategoria }}</span>
                                    </td>
                                    <td class="max-w-3xl px-5 py-4">
                                        <p class="font-bold leading-relaxed text-slate-900">{{ $factor->glosa }}</p>
                                        @if($factor->explicacion_glosa)
                                            <p class="mt-1.5 whitespace-normal text-xs leading-relaxed text-slate-600">{{ $factor->explicacion_glosa }}</p>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4 text-xs text-slate-500">{{ $factor->pagina_pdf ? 'PDF '.$factor->pagina_pdf : '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-slate-200 px-5 py-4">{{ $factors->links() }}</div>
            @endif
        </section>
    </div>
</x-layouts.app>
