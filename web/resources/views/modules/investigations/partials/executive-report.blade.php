@php
    $caseData = $case ? [
        'folio' => $case->folio,
        'worker' => $case->worker_name,
        'rut' => $case->worker_rut,
        'job' => $case->job_title,
        'type' => $case->case_type,
        'date' => $case->event_date?->format('d-m-Y'),
        'time' => $case->event_time,
        'location' => $case->event_location,
        'establishment' => $case->establishment?->name ?? $case->workplace,
        'employer' => $case->employer_name,
        'administrator' => $case->administrator,
        'story' => $case->narrative,
    ] : [
        'folio' => $selected['case_id'], 'worker' => $selected['worker'] ?? null, 'rut' => null,
        'job' => null, 'type' => $selected['case_type'] ?? null, 'date' => null, 'time' => null,
        'location' => null, 'establishment' => $selected['establishment'] ?? null, 'employer' => null,
        'administrator' => null, 'story' => null,
    ];
    $finalStory = $investigation?->final_story ?: $caseData['story'];
    $reportFacts = $investigation && ! \App\Support\OriginalGiatepSchema::isActive() ? $investigation->facts : collect();
    $reportNodes = $investigation && ! \App\Support\OriginalGiatepSchema::isActive() ? $investigation->causalNodes : collect();
    $reportMeasures = $investigation && ! \App\Support\OriginalGiatepSchema::isActive() ? $investigation->measures : collect();
    $reportInterviews = $investigation && ! \App\Support\OriginalGiatepSchema::isActive() ? $investigation->interviews : collect();
    $reportFactors = $investigation && ! \App\Support\OriginalGiatepSchema::isActive() ? $investigation->causeFactors : collect();
@endphp

<article class="mx-auto max-w-6xl space-y-5" aria-label="Informe de investigación">
    <header class="card border-t-4 border-t-blue-900 p-6 shadow-sm sm:p-8">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.16em] text-blue-800">Dirección Comunal de Salud · GIATEP</p>
                <h2 class="mt-2 text-2xl font-black text-slate-950 sm:text-3xl">Informe de investigación</h2>
                <p class="mt-2 text-sm text-slate-600">Expediente {{ $selected['id'] }} · Caso {{ $caseData['folio'] }}</p>
            </div>
            <div class="text-right">
                <span class="status {{ $statusClass($selected['status']) }}">{{ $selected['status'] }}</span>
                <p class="mt-2 text-xs text-slate-500">Última actualización: {{ $selected['updated'] ?? 'Sin registro' }}</p>
            </div>
        </div>
        <div class="mt-6 grid gap-3 border-t border-slate-200 pt-5 sm:grid-cols-3">
            <div><span class="label">Caso asignado</span><p class="font-bold text-slate-900">{{ $caseData['folio'] }}</p></div>
            <div><span class="label">Responsable de la investigación</span><p class="font-bold text-slate-900">{{ $investigation?->owner?->name ?? $selected['owner'] ?? 'Sin asignar' }}</p></div>
            <div><span class="label">Avance informado</span><p class="font-bold text-slate-900">{{ $selected['progress'] ?? 0 }}%</p></div>
        </div>
    </header>

    <section class="card p-6 shadow-sm sm:p-8">
        <h3 class="border-b border-slate-200 pb-3 text-lg font-black text-slate-950">1. Identificación del caso</h3>
        <dl class="mt-4 grid gap-x-8 gap-y-4 sm:grid-cols-2 lg:grid-cols-3">
            <div><dt>Persona afectada</dt><dd>{{ $caseData['worker'] ?: 'Sin información' }}</dd></div>
            <div><dt>RUT</dt><dd>{{ $caseData['rut'] ?: 'Sin información' }}</dd></div>
            <div><dt>Cargo</dt><dd>{{ $caseData['job'] ?: 'Sin información' }}</dd></div>
            <div><dt>Tipo de caso</dt><dd>{{ $caseData['type'] ?: 'Sin información' }}</dd></div>
            <div><dt>Fecha y hora</dt><dd>{{ $caseData['date'] ?: 'Sin información' }}{{ $caseData['time'] ? ' · '.$caseData['time'] : '' }}</dd></div>
            <div><dt>Lugar</dt><dd>{{ $caseData['location'] ?: 'Sin información' }}</dd></div>
            <div><dt>Establecimiento</dt><dd>{{ $caseData['establishment'] ?: 'Sin información' }}</dd></div>
            <div><dt>Empleador</dt><dd>{{ $caseData['employer'] ?: 'Sin información' }}</dd></div>
            <div><dt>Organismo administrador</dt><dd>{{ $caseData['administrator'] ?: 'Sin información' }}</dd></div>
        </dl>
    </section>

    <section class="card p-6 shadow-sm sm:p-8">
        <h3 class="border-b border-slate-200 pb-3 text-lg font-black text-slate-950">2. Antecedentes y relato</h3>
        <h4 class="mt-5 text-sm font-black text-slate-800">Relato de la investigación</h4>
        <p class="mt-2 whitespace-pre-line text-sm leading-7 text-slate-700">{{ $finalStory ?: 'No se ha registrado un relato para este expediente.' }}</p>
    </section>

    @if($reportInterviews->isNotEmpty())
        <section class="card p-6 shadow-sm sm:p-8">
            <h3 class="border-b border-slate-200 pb-3 text-lg font-black text-slate-950">3. Entrevistas registradas</h3>
            <div class="mt-4 divide-y divide-slate-200">
                @foreach($reportInterviews as $interview)
                    <div class="py-4 first:pt-0 last:pb-0">
                        <div class="flex flex-wrap justify-between gap-2"><strong>{{ $interview->interviewee_name ?: 'Participante sin nombre' }}</strong><span class="text-xs text-slate-500">{{ $interview->witness_type ?: $interview->interviewee_role ?: 'Entrevista' }}{{ $interview->interview_date ? ' · '.$interview->interview_date->format('d-m-Y') : '' }}</span></div>
                        @if($interview->answers->isNotEmpty())
                            <dl class="mt-3 grid gap-3 sm:grid-cols-2">
                                @foreach($interview->answers as $answer)
                                    <div><dt>{{ $answer->question?->texto_pregunta ?? $answer->question?->texto ?? $answer->question_key ?? 'Respuesta' }}</dt><dd class="whitespace-pre-line">{{ is_array($answer->value) ? implode(', ', $answer->value) : ($answer->value ?: 'Sin respuesta') }}</dd></div>
                                @endforeach
                            </dl>
                        @endif
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    @if($case?->evidences?->isNotEmpty())
        <section class="card p-6 shadow-sm sm:p-8">
            <h3 class="border-b border-slate-200 pb-3 text-lg font-black text-slate-950">4. Evidencias adjuntas</h3>
            <ul class="mt-4 grid gap-2 sm:grid-cols-2">
                @foreach($case->evidences as $evidence)
                    <li class="rounded-xl border border-slate-200 p-3 text-sm"><strong>{{ $evidence->original_name ?? $evidence->Nombre_Archivo ?? 'Archivo adjunto' }}</strong><span class="mt-1 block text-xs text-slate-500">{{ $evidence->mime_type ?? $evidence->Tipo_MIME ?? 'Evidencia del caso' }}</span></li>
                @endforeach
            </ul>
        </section>
    @endif

    @if($reportFacts->isNotEmpty() || $reportNodes->isNotEmpty())
        <section class="card p-6 shadow-sm sm:p-8">
            <h3 class="border-b border-slate-200 pb-3 text-lg font-black text-slate-950">5. Hechos y análisis causal</h3>
            @if($reportFacts->isNotEmpty())
                <h4 class="mt-5 text-sm font-black text-slate-800">Hechos registrados</h4>
                <ol class="mt-2 list-inside list-decimal space-y-2 text-sm text-slate-700">@foreach($reportFacts as $fact)<li>{{ $fact->description }}</li>@endforeach</ol>
            @endif
            @if($reportNodes->isNotEmpty())
                <h4 class="mt-5 text-sm font-black text-slate-800">Elementos del análisis causal registrados</h4>
                <ol class="mt-2 list-inside list-decimal space-y-2 text-sm text-slate-700">@foreach($reportNodes as $node)<li><span class="font-semibold">{{ $node->node_type }}:</span> {{ $node->description }}</li>@endforeach</ol>
            @endif
            @if($reportFactors->isNotEmpty())
                <h4 class="mt-5 text-sm font-black text-slate-800">Factores asociados</h4>
                <ul class="mt-2 list-inside list-disc space-y-2 text-sm text-slate-700">@foreach($reportFactors as $factor)<li>{{ $factor->codigo_efectivo ?? $factor->codigo ?? '' }} {{ $factor->glosa ?? $factor->name ?? '' }}</li>@endforeach</ul>
            @endif
        </section>
    @endif

    @if($reportMeasures->isNotEmpty())
        <section class="card p-6 shadow-sm sm:p-8">
            <h3 class="border-b border-slate-200 pb-3 text-lg font-black text-slate-950">6. Medidas y seguimiento</h3>
            <div class="mt-4 overflow-x-auto"><table class="data-table min-w-full"><thead><tr><th>Medida</th><th>Responsable</th><th>Plazo</th><th>Estado</th></tr></thead><tbody>
                @foreach($reportMeasures as $measure)
                    <tr><td>{{ $measure->description }}</td><td>{{ $measure->responsible ?: 'Sin asignar' }}</td><td>{{ $measure->due_date?->format('d-m-Y') ?? 'Sin plazo' }}</td><td>{{ $measure->status ?: 'Pendiente' }}</td></tr>
                @endforeach
            </tbody></table></div>
        </section>
    @endif

    <section class="card p-6 shadow-sm sm:p-8">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 pb-3">
            <div><h3 class="text-lg font-black text-slate-950">Observaciones de la investigación</h3><p class="mt-1 text-sm text-slate-600">Comentarios generales sobre cualquier aspecto del expediente.</p></div>
            <span class="text-xs font-semibold text-slate-500">{{ $reportObservations->count() }} registradas por Jefatura / Alta Dirección</span>
        </div>
        @forelse($reportObservations as $observation)
            <article class="mt-5 rounded-xl border border-slate-200 p-4">
                <div class="flex flex-wrap justify-between gap-2"><strong>{{ $observation->author_name }} · {{ $observation->author_role }}</strong><span class="text-xs text-slate-500">{{ \Illuminate\Support\Carbon::parse($observation->created_at)->format('d-m-Y H:i') }} · {{ ucfirst($observation->status) }}</span></div>
                <p class="mt-3 whitespace-pre-line text-sm leading-6 text-slate-700">{{ $observation->body }}</p>
                @foreach($observation->replies as $reply)
                    <div class="ml-4 mt-4 border-l-2 border-sky-300 pl-4">
                        <p class="text-xs font-bold text-sky-900">Respuesta de {{ $reply->author_name }} · {{ $reply->author_role }} <span class="font-normal text-slate-500">{{ \Illuminate\Support\Carbon::parse($reply->created_at)->format('d-m-Y H:i') }}</span></p>
                        <p class="mt-2 whitespace-pre-line text-sm text-slate-700">{{ $reply->body }}</p>
                    </div>
                @endforeach
            </article>
        @empty
            <p class="mt-4 text-sm text-slate-500">Aún no hay observaciones en esta investigación.</p>
        @endforelse
        @if($reportInvestigation && in_array($reportInvestigation->status, ['En desarrollo', 'En validación'], true))
            <form method="POST" action="{{ route('modules.investigations.observations.store', ['case' => $caseData['folio']]) }}" class="mt-6 space-y-3 border-t border-slate-200 pt-5">
                @csrf
                <label><span class="label">Nueva observación</span><textarea class="input" name="body" rows="4" maxlength="5000" required placeholder="Escriba una observación sobre cualquier aspecto de la investigación."></textarea></label>
                <button class="btn-primary" type="submit">Enviar observación al equipo investigador</button>
            </form>
        @endif
    </section>

    @if($reportInterviews->isEmpty() && $reportFacts->isEmpty() && $reportNodes->isEmpty() && $reportMeasures->isEmpty() && ! ($case?->evidences?->isNotEmpty()))
        <p class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">El expediente aún no contiene entrevistas, evidencias o análisis guardados para mostrar.</p>
    @endif
</article>
