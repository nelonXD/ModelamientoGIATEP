@php
$titles = [
    'cases' => ['Casos (DIAT / DIEP)', 'Registro y seguimiento de accidentes del trabajo y enfermedades profesionales de la Red APS Los Ángeles.'],
    'investigations' => ['Investigaciones Técnicas', 'Expedientes, entrevistas a testigos, evidencias, hechos constatados y Árbol de Causas.'],
    'reviews' => ['Revisiones y Validaciones', 'Bandeja técnica de validación de expedientes para Prevención y Alta Dirección.'],
    'observations' => ['Observaciones y Trazabilidad', 'Correcciones y observaciones a la investigación, medidas de control y plan de acción.'],
    'measures' => ['Medidas y Planes de Acción', 'Seguimiento de jerarquía de control, responsables institucionales y porcentaje de avance.'],
    'statistics' => ['Dashboards y Estadísticas', 'Indicadores descriptivos y accidentabilidad por CESFAM, SAR y tipo de evento.'],
    'reports' => ['Informes y Reportes Oficiales', 'Catálogo, vista previa de formularios oficiales DIAT/DIEP e Informes de Árbol de Causas.'],
    'profile' => ['Mi Perfil de Usuario', 'Datos del funcionario, roles activos y centros de salud autorizados.'],
    'users' => ['Gestión de Usuarios', 'Administración de cuentas y perfiles autorizados de la Dirección Comunal de Salud.'],
    'roles' => ['Roles y Permisos RBAC', 'Matriz de control de acceso según la normativa institucional.'],
    'establishments' => ['Catálogo de Establecimientos', 'Red de Atención Primaria: CESFAM, SAR, CECOSF y postas rurales.'],
    'settings' => ['Parámetros Institucionales', 'Catálogos normativos, matriz de 222 factores y jerarquías de prevención.'],
];
[$pageTitle, $pageDescription] = $titles[$module];
$changes = session(\App\Support\DemoWorkspace::SESSION_KEY, []);
$statusClass = fn ($status) => match ($status) {
    'Vencida', 'Con observaciones' => 'status-rejected',
    'Implementada', 'Concluida', 'Cerrado', 'Resuelta', 'Laboral (Cerrado)' => 'status-approved',
    'Seguimiento de medidas', 'En ejecución' => 'bg-blue-100 text-blue-800',
    default => 'status-pending'
};
@endphp
<x-layouts.app :title="$pageTitle.' · GIATEP Los Ángeles'">
<div class="mx-auto max-w-[1600px] space-y-6" data-demo-workspace data-demo-module="{{ $module }}" data-case-folio="{{ $module === 'investigations' && $selected ? $selected['case_id'] : '' }}" data-interview-store-url="{{ route('modules.investigations.interviews.store') }}" @if($module === 'investigations' && $selected) data-investigation-story="{{ $selected['final_story'] ?? ($case['story'] ?? '') }}" data-external-prompt-url="{{ route('modules.investigations.external.prompt') }}" data-external-import-url="{{ route('modules.investigations.external.import') }}" data-analysis-store-url="{{ route('modules.investigations.ai.store') }}" data-models-url="{{ route('modules.investigations.ai.models') }}" @endif>
    <!-- Encabezado del módulo -->
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-3xl font-black tracking-tight text-slate-950 sm:text-4xl">{{ $pageTitle }}</h1>
            <p class="mt-1 max-w-3xl text-sm leading-relaxed text-slate-600">{{ $pageDescription }}</p>
        </div>
        @if(in_array($module, ['cases','users','establishments']))
            <div class="flex flex-wrap gap-2.5">
                <a class="btn-primary inline-flex gap-2 shadow-sm" href="{{ route('demo.show', ['module' => $module, 'mode' => 'create']) }}">
                    <span class="text-base font-bold">+</span>
                    {{ $module === 'cases' ? 'Registrar Caso (DIAT / DIEP)' : ($module === 'users' ? 'Nuevo Usuario' : 'Nuevo Centro de Salud') }}
                </a>
            </div>
        @endif
    </div>

    @if($changes)
        <details class="card p-4 text-xs">
            <summary class="cursor-pointer font-bold text-slate-700">Historial de acciones registradas en esta sesión ({{ count($changes) }} registros)</summary>
            <ol class="mt-3 space-y-1.5 border-t pt-2 text-slate-600">
                @foreach(array_reverse($changes) as $change)
                    <li><strong>{{ $change['at'] }}</strong>: {{ $change['action'] }} <span class="text-slate-400">por {{ $change['user'] }}</span></li>
                @endforeach
            </ol>
        </details>
    @endif

    <!-- ==================== MÓDULO: CASOS ==================== -->
    @if($module === 'cases')
        @if($mode === 'create' || $mode === 'edit')
            <form class="card overflow-hidden shadow-md" method="POST" action="{{ route('demo.action', 'cases') }}" data-demo-form data-unsaved-form>
                @csrf
                <input type="hidden" name="action" value="{{ $mode === 'edit' ? 'Caso editado' : 'Caso registrado' }}">
                <input type="hidden" name="return_to" value="{{ route('modules.cases.index') }}">
                
                <div class="border-b border-slate-200 bg-slate-50/80 p-5">
                    <div class="flex flex-wrap gap-2" data-step-nav>
                        @foreach(['1. Trabajador / Afectado', '2. Establecimiento APS', '3. Evento (DIAT / DIEP)', '4. Relato Circunstanciado', '5. Lesión o Diagnóstico', '6. Carátula y Resumen'] as $index => $step)
                            <button type="button" class="step-pill {{ $index === 0 ? 'step-pill-active' : '' }}" data-step-target="{{ $index }}">
                                <span>{{ $index + 1 }}</span>{{ $step }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <div class="p-6 sm:p-8">
                    <!-- Paso 1: Trabajador -->
                    <section data-step="0" class="space-y-6">
                        <div class="rounded-xl border border-sky-200 bg-sky-50 p-4 text-xs text-sky-900">
                            <strong>Paso 1:</strong> Ingrese el RUT del funcionario. El sistema normaliza el formato con puntos y dígito verificador.
                        </div>
                        <div class="grid gap-5 md:grid-cols-2">
                            <div>
                                <label class="label">RUT del trabajador o funcionario</label>
                                <input class="input" value="{{ $selected['rut'] ?? '' }}" required placeholder="12.345.678-9">
                            </div>
                            <div>
                                <label class="label">Nombre completo</label>
                                <input class="input" value="{{ $selected['worker'] ?? '' }}" required placeholder="Nombres y Apellidos">
                            </div>
                            <div>
                                <label class="label">Cargo u oficio habitual</label>
                                <input class="input" value="{{ $selected['job_title'] ?? '' }}" placeholder="Ej. TENS, Odontólogo, Chofer">
                            </div>
                            <div>
                                <label class="label">Teléfono de contacto</label>
                                <input class="input" value="{{ $selected['phone'] ?? '' }}" placeholder="+56 9 ...">
                            </div>
                        </div>
                    </section>

                    <!-- Paso 2: Establecimiento -->
                    <section data-step="1" class="hidden space-y-6">
                        <div class="grid gap-5 md:grid-cols-2">
                            <div>
                                <label class="label">Establecimiento de salud comunal</label>
                                <select class="input">
                                    @foreach($demo['establishments'] as $item)
                                        <option {{ ($selected['establishment'] ?? '') === $item ? 'selected' : '' }}>{{ $item }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="label">Tipo de vínculo contractual</label>
                                <select class="input">
                                    <option>Estatuto de Atención Primaria (Ley 19.378 - Planta)</option>
                                    <option>Estatuto de Atención Primaria (Ley 19.378 - Contrata)</option>
                                    <option>Código del Trabajo</option>
                                    <option>Honorarios</option>
                                    <option>Empresa Externa / Proveedor</option>
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <label class="label">Lugar específico o dependencia interna</label>
                                <input class="input" value="{{ $selected['location'] ?? '' }}" placeholder="Ej. Box dental N° 3, Central de esterilización, Pasillo">
                            </div>
                        </div>
                    </section>

                    <!-- Paso 3: Evento DIAT vs DIEP -->
                    <section data-step="2" class="hidden space-y-6">
                        <div class="grid gap-5 md:grid-cols-2">
                            <div>
                                <label class="label">Tipo de caso (Formulario)</label>
                                <select class="input font-bold text-blue-900" data-case-type>
                                    <option value="Accidente del trabajo">Accidente del trabajo (Formulario DIAT)</option>
                                    <option value="Accidente de trayecto">Accidente de trayecto (Formulario DIAT)</option>
                                    <option value="Enfermedad profesional">Enfermedad profesional (Formulario DIEP)</option>
                                </select>
                            </div>
                            <div>
                                <label class="label">Fecha del evento o inicio de síntomas</label>
                                <input type="date" class="input" value="{{ $selected['date'] ?? date('Y-m-d') }}">
                            </div>
                            <div data-accident-field>
                                <label class="label">Hora del accidente</label>
                                <input type="time" class="input" value="{{ $selected['time'] ?? date('H:i') }}">
                            </div>
                            <div data-accident-field>
                                <label class="label">¿La tarea realizada era habitual?</label>
                                <select class="input">
                                    <option>Sí, labor habitual del cargo</option>
                                    <option>No, labor extraordinaria o de apoyo</option>
                                </select>
                            </div>
                            <div data-disease-field class="hidden">
                                <label class="label">Agente de riesgo sospechoso (DIEP)</label>
                                <select class="input">
                                    <option>Químico (Detergentes enzimáticos, antisépticos, desinfectantes)</option>
                                    <option>Ergonómico (Manejo manual de pacientes, sobreesfuerzo, posturas forzadas)</option>
                                    <option>Biológico (Exposición a patógenos o fluidos biológicos)</option>
                                    <option>Físico (Ruido, radiaciones, temperaturas extremas)</option>
                                    <option>Psicosocial laboral</option>
                                </select>
                            </div>
                            <div data-disease-field class="hidden">
                                <label class="label">Tiempo de exposición en el puesto</label>
                                <input class="input" placeholder="Ej. 1 año y 6 meses">
                            </div>
                        </div>
                    </section>

                    <!-- Paso 4: Relato -->
                    <section data-step="3" class="hidden space-y-4">
                        <label class="label">Relato circunstanciado inicial del evento o sintomatología</label>
                        <textarea class="input text-slate-800 leading-relaxed" rows="8" placeholder="Describa detalladamente las circunstancias, lugar, actividades y personas presentes en el momento del suceso.">{{ $selected['story'] ?? '' }}</textarea>
                        <p class="hint">Describa qué hacía la persona, cómo ocurrió el hecho y quiénes presenciaron el evento. Este relato servirá de base para la posterior investigación formal.</p>
                    </section>

                    <!-- Paso 5: Lesión -->
                    <section data-step="4" class="hidden space-y-6">
                        <div class="grid gap-5 md:grid-cols-2">
                            <div>
                                <label class="label">Parte del cuerpo afectada</label>
                                <input class="input" value="{{ $selected['body_part'] ?? '' }}" placeholder="Ej. Rodilla derecha, Dedo índice, Zona lumbar">
                            </div>
                            <div>
                                <label class="label">Gravedad preliminar (según criterio ACHS)</label>
                                <select class="input">
                                    <option>Leve (sin riesgo vital ni secuela previsible)</option>
                                    <option>Grave (requiere maniobras de reanimación o rescate)</option>
                                    <option>Fatal</option>
                                </select>
                            </div>
                            <div>
                                <label class="label">Calificación previsional inicial</label>
                                <select class="input">
                                    <option>Pendiente de calificación</option>
                                    <option>Laboral</option>
                                    <option>Común / No laboral</option>
                                </select>
                            </div>
                            <div>
                                <label class="label">Documento o certificado de atención (local)</label>
                                <input type="file" class="input" data-local-file>
                                <p class="hint">Comprobante médico o documento de respaldo del suceso.</p>
                            </div>
                        </div>
                    </section>

                    <!-- Paso 6: Resumen -->
                    <section data-step="5" class="hidden space-y-6">
                        <div class="rounded-2xl border border-sky-300 bg-sky-50/70 p-6">
                            <div class="flex items-center justify-between border-b border-sky-200 pb-3">
                                <h3 class="font-black text-blue-950">Carátula de Envío a la Dirección Comunal de Salud</h3>
                                <span class="rounded bg-sky-600 px-2 py-1 text-xs font-bold text-white">Formulario DIAT / DIEP</span>
                            </div>
                            <dl class="mt-4 grid gap-4 text-xs sm:grid-cols-3">
                                <div><dt>Empleador:</dt><dd>Dirección Comunal de Salud (RUT: 69.170.102-6)</dd></div>
                                <div><dt>Organismo Administrador:</dt><dd>Asociación Chilena de Seguridad (ACHS)</dd></div>
                                <div><dt>Funcionario/a:</dt><dd>{{ $selected['worker'] ?? '—' }}</dd></div>
                                <div><dt>Establecimiento:</dt><dd>{{ $selected['establishment'] ?? '—' }}</dd></div>
                                <div><dt>Fecha del caso:</dt><dd>{{ $selected['date'] ?? date('Y-m-d') }}</dd></div>
                                <div><dt>Estado al registrar:</dt><dd><span class="status status-pending">Registrado</span></dd></div>
                            </dl>
                        </div>
                    </section>
                </div>

                <div class="flex flex-wrap justify-between gap-3 border-t border-slate-200 bg-slate-50/90 p-5">
                    <button type="button" class="btn-secondary" data-step-prev>← Anterior</button>
                    <div class="flex gap-2">
                        <button class="btn-secondary" name="action" value="Borrador de caso guardado">Guardar borrador</button>
                        <button type="button" class="btn-primary" data-step-next>Siguiente →</button>
                        <button class="btn-primary hidden" data-step-submit>Registrar caso formal</button>
                    </div>
                </div>
            </form>

        @elseif($selected)
            <!-- Vista de Detalle de Caso Seleccionado -->
            @php $relatedInvestigation = $selected['investigation_id'] ? $demo['investigations']->firstWhere('id', $selected['investigation_id']) : null; @endphp
            <div class="grid gap-6 xl:grid-cols-[1fr_24rem]">
                <section class="space-y-6">
                    <!-- Ficha Encabezado -->
                    <div class="card p-6 shadow-sm">
                        <div class="flex flex-wrap items-start justify-between gap-4 border-b border-slate-100 pb-5">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-black text-blue-900">
                                        {{ $selected['type'] === 'Enfermedad profesional' ? 'DIEP' : 'DIAT' }}
                                    </span>
                                    <span class="font-mono text-sm font-bold text-slate-500">{{ $selected['id'] }}</span>
                                </div>
                                <h2 class="mt-2 text-2xl font-black text-slate-950">{{ $selected['worker'] }}</h2>
                                <p class="text-xs font-semibold text-slate-500">{{ $selected['rut'] }} · {{ $selected['job_title'] }}</p>
                            </div>
                            <div class="text-right">
                                <span class="status {{ $statusClass($selected['status']) }} text-sm">{{ $selected['status'] }}</span>
                                <p class="mt-2 text-xs font-bold text-slate-500">{{ $selected['establishment'] }}</p>
                            </div>
                        </div>

                        <dl class="mt-5 grid gap-4 text-xs sm:grid-cols-2 lg:grid-cols-4">
                            <div><dt>Tipo de Caso</dt><dd class="text-sm font-bold text-slate-900">{{ $selected['type'] }}</dd></div>
                            <div><dt>Fecha y Hora</dt><dd class="text-sm font-bold text-slate-900">{{ $selected['date'] }} · {{ $selected['time'] }}</dd></div>
                            <div><dt>Lugar Específico</dt><dd class="text-sm font-bold text-slate-900">{{ $selected['location'] }}</dd></div>
                            <div><dt>Calificación</dt><dd class="text-sm font-bold text-slate-900">{{ $selected['qualification'] }}</dd></div>
                        </dl>
                    </div>

                    <!-- Pestañas de Detalle -->
                    <div class="card p-6 shadow-sm">
                        <div class="tab-list" data-tabs>
                            @foreach(['Formulario Oficial DIAT/DIEP', 'Historial y Trazabilidad', 'Investigación Asociada', 'Medidas Preventivas'] as $i => $tab)
                                <button class="tab-button {{ $i === 0 ? 'tab-button-active' : '' }}" data-tab-target="case-{{ $i }}">{{ $tab }}</button>
                            @endforeach
                        </div>

                        <!-- Tab 0: Ficha Oficial -->
                        <div class="mt-6 space-y-6" data-tab-panel="case-0">
                            <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-6">
                                <div class="flex items-center justify-between border-b border-slate-200 pb-3">
                                    <div class="flex items-center gap-2">
                                        <span class="size-3 rounded-full bg-blue-800"></span>
                                        <h3 class="font-black text-blue-950">Denuncia Oficial ({{ $selected['type'] === 'Enfermedad profesional' ? 'DIEP' : 'DIAT' }})</h3>
                                    </div>
                                    <span class="text-xs font-bold text-slate-500">ACHS · Folio {{ $selected['id'] }}</span>
                                </div>
                                <div class="mt-4 grid gap-5 sm:grid-cols-2 text-xs">
                                    <div><dt>Razón Social:</dt><dd class="font-bold text-slate-900">{{ $selected['employer_name'] }}</dd></div>
                                    <div><dt>RUT Empleador:</dt><dd class="font-bold text-slate-900">{{ $selected['employer_rut'] }}</dd></div>
                                    <div><dt>Dirección Empleador:</dt><dd class="font-bold text-slate-900">{{ $selected['employer_address'] }}</dd></div>
                                    <div><dt>Actividad Económica:</dt><dd class="font-bold text-slate-900">{{ $selected['economic_activity'] }}</dd></div>
                                </div>
                            </div>

                            <div class="space-y-3">
                                <h4 class="font-black text-slate-900">Relato del Evento</h4>
                                <div class="rounded-xl border border-slate-200 bg-white p-4 text-sm leading-relaxed text-slate-700">
                                    {{ $selected['story'] }}
                                </div>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="rounded-xl border border-slate-200 p-4 text-xs">
                                    <dt>Lesión o Síntoma:</dt>
                                    <dd class="mt-1 text-sm font-bold text-slate-900">{{ $selected['injury'] }}</dd>
                                </div>
                                <div class="rounded-xl border border-slate-200 p-4 text-xs">
                                    <dt>Parte Afectada:</dt>
                                    <dd class="mt-1 text-sm font-bold text-slate-900">{{ $selected['body_part'] }}</dd>
                                </div>
                            </div>
                        </div>

                        <!-- Tab 1: Historial -->
                        <div class="mt-6 hidden" data-tab-panel="case-1">
                            <ul class="timeline">
                                <li><strong>Caso registrado en plataforma</strong><span>{{ $selected['date'] }} · Ingreso de antecedentes iniciales</span></li>
                                <li><strong>Notificación a Comité Paritario y Prevención</strong><span>{{ $selected['date'] }} · Envío automático de ficha</span></li>
                                @if($relatedInvestigation)
                                    <li><strong>Investigación formal aperturada</strong><span>{{ $relatedInvestigation['updated'] }} · Folio {{ $relatedInvestigation['id'] }}</span></li>
                                @endif
                                @if($selected['status'] === 'Cerrado')
                                    <li><strong>Caso Cerrado</strong><span>Medidas implementadas al 100% y funcionario reintegrado</span></li>
                                @endif
                            </ul>
                        </div>

                        <!-- Tab 2: Investigación -->
                        <div class="mt-6 hidden" data-tab-panel="case-2">
                            @if($relatedInvestigation)
                                <div class="rounded-2xl border border-sky-200 bg-sky-50 p-6">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <span class="rounded bg-sky-700 px-2 py-0.5 text-xs font-bold text-white">Investigación {{ $relatedInvestigation['id'] }}</span>
                                            <h4 class="mt-2 text-lg font-black text-slate-950">{{ $relatedInvestigation['kind'] }}</h4>
                                            <p class="mt-1 text-xs text-slate-600">Responsable: {{ $relatedInvestigation['owner'] }} · Estado: {{ $relatedInvestigation['status'] }}</p>
                                        </div>
                                        <a href="{{ route('demo.show', ['module'=>'investigations','id'=>$relatedInvestigation['id']]) }}" class="btn-primary">
                                            Abrir Árbol de Causas →
                                        </a>
                                    </div>
                                </div>
                            @elseif(in_array($selected['type'] ?? '', ['Accidente o enfermedad común', 'Accidente común', 'Enfermedad común']))
                                <div class="empty-state">
                                    <p class="font-bold text-slate-800">Investigación no aplica para este tipo de caso.</p>
                                    <p class="mt-1 text-xs text-slate-500">Los casos de patología o accidente común se derivan al sistema de salud previsional común (FONASA/ISAPRE) bajo la Ley 16.744.</p>
                                    <button type="button" disabled class="btn-secondary mt-4 inline-flex cursor-not-allowed opacity-60">Investigación no aplica</button>
                                </div>
                            @else
                                <div class="empty-state">
                                    <p class="font-bold text-slate-800">Aún no existe una investigación para este caso.</p>
                                    <p class="mt-1 text-xs text-slate-500">Los roles autorizados (Prevención, CPHS, Delegado) pueden iniciar la investigación en cualquier momento.</p>
                                    <a href="{{ route('modules.investigations.index', ['case' => $selected['id']]) }}" class="btn-primary mt-4 inline-flex">
                                        Iniciar Investigación Ahora →
                                    </a>
                                </div>
                            @endif
                        </div>

                        <!-- Tab 3: Medidas -->
                        <div class="mt-6 hidden" data-tab-panel="case-3">
                            <ul class="space-y-3">
                                @forelse($demo['measures']->where('case_id', $selected['id']) as $measure)
                                    <li class="rounded-xl border border-slate-200 p-4">
                                        <div class="flex items-start justify-between">
                                            <div>
                                                <span class="rounded bg-slate-100 px-2 py-0.5 text-[11px] font-bold text-slate-700">{{ $measure['category'] }}</span>
                                                <p class="mt-2 font-bold text-slate-900">{{ $measure['description'] }}</p>
                                                <p class="mt-1 text-xs text-slate-500">Responsable: {{ $measure['owner'] }} · Plazo: {{ $measure['due'] }}</p>
                                            </div>
                                            <span class="status {{ $statusClass($measure['status']) }}">{{ $measure['status'] }} ({{ $measure['progress'] }}%)</span>
                                        </div>
                                    </li>
                                @empty
                                    <li class="empty-state">No hay medidas asociadas a este caso todavía.</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </section>

                <!-- Panel Lateral de Acciones -->
                <aside class="space-y-4">
                    <div class="card p-5 space-y-3">
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Acciones del Caso</p>
                        
                        <a class="btn-primary flex justify-center text-center w-full" href="{{ route('demo.show', ['module'=>'cases','id'=>$selected['id'],'mode'=>'edit']) }}">
                            ✎ Editar Antecedentes
                        </a>

                        @if($relatedInvestigation)
                            <a class="btn-secondary flex justify-center text-center w-full" href="{{ route('demo.show', ['module'=>'investigations','id'=>$selected['investigation_id']]) }}">
                                🔍 Ver Investigación ({{ $selected['investigation_id'] }})
                            </a>
                        @elseif(in_array($selected['type'] ?? '', ['Accidente o enfermedad común', 'Accidente común', 'Enfermedad común']))
                            <button type="button" disabled class="btn-secondary flex justify-center text-center w-full cursor-not-allowed opacity-60" title="No aplica investigación laboral">
                                Investigación no aplica
                            </button>
                        @else
                            <a class="btn-primary flex justify-center text-center w-full bg-emerald-700 hover:bg-emerald-800" href="{{ route('modules.investigations.index', ['case'=>$selected['id']]) }}">
                                + Iniciar Investigación
                            </a>
                        @endif

                        @if(auth()->user()->hasPermission('observations.read'))
                            <a class="btn-secondary flex justify-center text-center w-full" href="{{ route('modules.observations.index', ['case'=>$selected['id']]) }}">
                                💬 Ver / Dejar Observaciones
                            </a>
                        @endif

                        <!-- Botón Cerrar Caso (Autorizado para Comité, Delegado y Prevención) -->
                        @if(auth()->user()->hasRole('cphs') || auth()->user()->hasRole('delegado') || auth()->user()->hasRole('prevencion'))
                            @if($selected['status'] !== 'Cerrado')
                                <button type="button" class="btn-secondary flex justify-center text-center w-full border-emerald-300 bg-emerald-50 text-emerald-900 hover:bg-emerald-100" data-open-demo-modal data-record="Cerrar Caso {{ $selected['id'] }}">
                                    ✓ Cerrar Caso Formalmente
                                </button>
                            @else
                                <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-center text-xs font-bold text-emerald-900">
                                    Caso Cerrado y Regularizado
                                </div>
                            @endif
                        @endif
                    </div>

                    <div class="card p-5 text-xs text-slate-600 space-y-2">
                        <strong class="font-bold text-slate-900">Normativa Institucional:</strong>
                        <p class="leading-relaxed">
                            Según el manual de la Dirección Comunal de Salud de Los Ángeles, el Comité Paritario (CPHS), Delegados de Seguridad y Prevención tienen la potestad de realizar la investigación y cerrar el caso una vez verificadas las medidas correctivas.
                        </p>
                    </div>
                </aside>
            </div>

        @else
            <!-- Listado Principal de Casos -->
            <div class="filter-bar" data-filter-scope>
                <input class="input" placeholder="Buscar por trabajador, RUT o folio" data-table-search>
                <select class="input" data-table-filter="type">
                    <option value="">Todos los tipos (DIAT / DIEP)</option>
                    <option value="Accidente de trabajo">Accidente de trabajo</option>
                    <option value="Accidente de trayecto">Accidente de trayecto</option>
                    <option value="Enfermedad profesional">Enfermedad profesional</option>
                </select>
                <select class="input" data-table-filter="status">
                    <option value="">Todos los estados</option>
                    @foreach($demo['cases']->pluck('status')->unique() as $state)
                        <option>{{ $state }}</option>
                    @endforeach
                </select>
                <select class="input">
                    <option value="">Todos los establecimientos de Los Ángeles</option>
                    @foreach($demo['establishments'] as $item)
                        <option>{{ $item }}</option>
                    @endforeach
                </select>
                <input type="date" class="input">
            </div>

            <div class="card table-card shadow-sm">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Folio / Formulario</th>
                            <th>Trabajador Afectado</th>
                            <th>Tipo de Evento</th>
                            <th>Centro de Salud</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($demo['cases'] as $case)
                            <tr data-table-row data-type="{{ $case['type'] }}" data-status="{{ $case['status'] }}">
                                <td>
                                    <span class="font-black text-blue-950">{{ $case['id'] }}</span>
                                    <span class="block text-[10px] font-extrabold uppercase tracking-wider text-slate-400">
                                        {{ $case['type'] === 'Enfermedad profesional' ? 'DIEP' : 'DIAT' }}
                                    </span>
                                </td>
                                <td>
                                    <strong class="text-slate-900">{{ $case['worker'] }}</strong>
                                    <small>{{ $case['rut'] }} · {{ $case['job_title'] }}</small>
                                </td>
                                <td>
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold {{ $case['type'] === 'Enfermedad profesional' ? 'bg-amber-100 text-amber-900' : 'bg-blue-50 text-blue-900' }}">
                                        {{ $case['type'] }}
                                    </span>
                                </td>
                                <td>
                                    <span class="font-semibold text-slate-800">{{ $case['establishment'] }}</span>
                                    <small>{{ $case['location'] }}</small>
                                </td>
                                <td>
                                    <span class="font-semibold text-slate-800">{{ $case['date'] }}</span>
                                    <small>{{ $case['time'] }}</small>
                                </td>
                                <td>
                                    <span class="status {{ $statusClass($case['status']) }}">{{ $case['status'] }}</span>
                                </td>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <a class="text-link" href="{{ route('demo.show', ['module'=>'cases','id'=>$case['id']]) }}">Ver Ficha</a>
                                        @if($case['investigation_id'])
                                            <span class="text-slate-300">|</span>
                                            <a class="text-xs font-bold text-emerald-700 hover:underline" href="{{ route('demo.show', ['module'=>'investigations','id'=>$case['investigation_id']]) }}">Investigación</a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-8 text-center text-slate-500">
                                    <p class="font-bold text-slate-700">No hay casos registrados en el sistema.</p>
                                    <p class="mt-1 text-xs text-slate-400">Utilice el botón "Registrar Caso" para crear el primer formulario formal DIAT o DIEP.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="empty-state m-5 hidden" data-filter-empty>No hay casos registrados para los filtros seleccionados.</div>
            </div>
        @endif

    <!-- ==================== MÓDULO: INVESTIGACIONES ==================== -->
    @elseif($module === 'investigations')
        @if($selected)
            @if($leadershipReport)
                @include('modules.investigations.partials.executive-report', ['case' => $reportCase, 'investigation' => $reportInvestigation, 'selected' => $selected])
            @else
            @php $case = $demo['cases']->firstWhere('id', $selected['case_id']); @endphp

            <div class="space-y-5">
                <nav class="card overflow-x-auto p-3 shadow-sm" aria-label="Progreso de la investigación" data-investigation-progress>
                    <ol class="flex w-full min-w-[1120px] flex-nowrap gap-2">
                        @foreach([
                            ['Antecedentes', 'Identificación y datos del suceso'],
                            ['Entrevistas', 'Declaraciones de participantes'],
                            ['Evidencias', 'Documentos y registros del caso'],
                            ['Relato cronológico', 'Secuencia objetiva de los hechos'],
                            ['Hechos constatados', 'Hechos y fuentes de respaldo'],
                            ['Árbol de causas', 'Relaciones causales del accidente'],
                            ['Medidas', 'Acciones, responsables y plazos'],
                            ['Participantes y firmas', 'Equipo investigador y validación'],
                            ['Dictamen y cierre', 'Revisión técnica e informe final'],
                        ] as $i => [$step, $detail])
                            <li class="min-w-0 flex-1">
                                <button type="button" class="flex aspect-square w-full flex-col items-center justify-center gap-2 rounded-xl border border-transparent p-2 text-center transition hover:bg-slate-50 {{ $i === 0 ? 'border-blue-200 bg-blue-50' : '' }} {{ in_array($i, [4, 5, 6]) && empty($selected['has_facts']) ? 'cursor-not-allowed opacity-45' : '' }}" data-section-target="inv-{{ $i }}" aria-current="{{ $i === 0 ? 'step' : 'false' }}" @if(in_array($i, [4,5,6])) data-ai-derived-nav @endif @if(in_array($i, [4,5,6]) && empty($selected['has_facts'])) disabled title="Disponible después de analizar los hechos" @endif>
                                    <span class="flex size-9 shrink-0 items-center justify-center rounded-lg {{ $i === 0 ? 'bg-blue-950 text-white' : 'bg-slate-200 text-slate-600' }} text-xs font-black" data-progress-number>{{ $i + 1 }}</span>
                                    <span class="min-w-0">
                                        <strong class="block text-xs font-bold leading-tight text-slate-900">{{ $step }}</strong>
                                        <small class="mt-1 block text-[10px] leading-tight text-slate-500">{{ $detail }}</small>
                                    </span>
                                </button>
                            </li>
                        @endforeach
                    </ol>
                </nav>
                <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-sky-100 bg-sky-50 px-4 py-3 text-xs">
                    <div><span class="font-black text-sky-950">Expediente {{ $selected['id'] }}</span><span class="ml-2 text-slate-600">Caso {{ $selected['case_id'] }}</span></div>
                    <div class="flex gap-2"><button type="button" data-section-target="inv-0" class="btn-secondary px-3 py-1.5 text-xs">Editar información</button><a href="{{ route('modules.observations.index', ['case' => $selected['case_id']]) }}" class="btn-secondary px-3 py-1.5 text-xs">Agregar observación</a></div>
                </div>
                <!-- Contenido de la Investigación -->
                <form method="POST" action="{{ route('modules.investigations.workflow', ['case' => $selected['case_id']]) }}" class="card min-w-0 shadow-sm" data-unsaved-form data-investigation-form data-investigation-readonly="{{ $selected['status'] !== 'En desarrollo' ? 'true' : 'false' }}" data-server-draft="{{ json_encode($investigationDraft ?? [], JSON_HEX_APOS | JSON_HEX_QUOT) }}">
                    @csrf
                    <input type="hidden" name="workflow_action" value="save" data-investigation-workflow-action>
                    <input type="hidden" name="return_to" value="{{ url()->current() }}">

                    <header class="border-b border-slate-200 bg-slate-50/70 p-6">
                        <div class="flex flex-wrap items-center justify-between gap-4">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="rounded bg-sky-700 px-2 py-0.5 text-xs font-bold text-white">{{ $selected['kind'] }}</span>
                                    <span class="text-xs font-bold text-slate-500">Caso: {{ $selected['case_id'] }}</span>
                                </div>
                                <h2 class="mt-2 text-2xl font-black text-slate-950">{{ $selected['id'] }} · {{ $case['worker'] ?? 'Funcionario' }}</h2>
                                <p class="text-xs text-slate-500">Centro: {{ $case['establishment'] ?? 'Establecimiento APS' }} · Responsable: {{ $selected['owner'] }}</p>
                            </div>
                            <div class="text-right">
                                <span class="status {{ $statusClass($selected['status']) }} text-sm">{{ $selected['status'] }}</span>
                                <div class="mt-2 flex items-center gap-2">
                                    <div class="h-2 w-24 rounded-full bg-slate-200 overflow-hidden">
                                        <div class="h-full bg-sky-600 rounded-full" style="width: {{ $selected['progress'] }}%"></div>
                                    </div>
                                    <span class="text-xs font-bold text-slate-600">{{ $selected['progress'] }}%</span>
                                </div>
                            </div>
                        </div>
                    </header>

                    <div class="p-6 sm:p-8">
                        <!-- Sección 0: Antecedentes Generales -->
                        <section data-section="inv-0" class="space-y-6">
                            <h3 class="section-title">1. Antecedentes Generales de la Investigación</h3>
                            <div class="source-banner">Información recuperada del caso {{ $case['id'] }}. Puede corregirse o completarse para la investigación.</div>

                            <fieldset class="rounded-2xl border border-slate-200 p-5">
                                <legend class="px-2 font-black text-blue-950">1.1 Datos del Empleador</legend>
                                <div class="mt-3 grid gap-4 md:grid-cols-2 text-xs">
                                    <label><span class="label">Razón Social</span><input class="input" name="employer_name" value="{{ $case['employer_name'] ?? 'Dirección Comunal de Salud de Los Ángeles' }}"></label>
                                    <label><span class="label">RUT Institucional</span><input class="input" name="employer_rut" value="{{ $case['employer_rut'] ?? '69.170.102-6' }}"></label>
                                    <label><span class="label">Organismo Administrador</span><input class="input" name="administrator" value="{{ $case['administrator'] ?? 'ACHS' }}"></label>
                                    <label><span class="label">Actividad Económica</span><input class="input" name="economic_activity" value="{{ $case['economic_activity'] ?? 'Salud Pública Municipal APS' }}"></label>
                                    <label class="md:col-span-2"><span class="label">Dirección</span><input class="input" name="employer_address" value="{{ $case['employer_address'] ?? 'Calle Valdivia #150, Los Ángeles' }}"></label>
                                </div>
                            </fieldset>

                            <fieldset class="rounded-2xl border border-slate-200 p-5">
                                <legend class="px-2 font-black text-blue-950">1.2 Datos del Funcionario Afectado</legend>
                                <div class="mt-3 grid gap-4 md:grid-cols-2 text-xs">
                                    <label><span class="label">Nombre Completo</span><input class="input" name="worker_name" value="{{ $case['worker'] ?? '' }}"></label>
                                    <label><span class="label">RUT</span><input class="input" name="worker_rut" value="{{ $case['rut'] ?? '' }}"></label>
                                    <label><span class="label">Cargo u Oficio</span><input class="input" name="job_title" value="{{ $case['job_title'] ?? '' }}"></label>
                                    <label><span class="label">Teléfono</span><input class="input" name="worker_phone" value="{{ $case['phone'] ?? '' }}"></label>
                                </div>
                            </fieldset>

                            <fieldset class="rounded-2xl border border-slate-200 p-5">
                                <legend class="px-2 font-black text-blue-950">1.3 Antecedentes del Suceso</legend>
                                <div class="mt-3 grid gap-4 md:grid-cols-2 text-xs">
                                    <label><span class="label">Fecha</span><input type="date" class="input" name="event_date" value="{{ $case['date'] ?? '' }}"></label>
                                    <label><span class="label">Hora</span><input type="time" class="input" name="event_time" value="{{ $case['time'] ?? '09:15' }}"></label>
                                    <label><span class="label">Lugar Específico</span><input class="input" name="event_location" value="{{ $case['location'] ?? '' }}"></label>
                                    <label><span class="label">Tipo de evento</span><input class="input" name="event_type" value="{{ $case['type'] }}"></label>
                                    <label class="md:col-span-2"><span class="label">Relato Inicial Recuperado</span><textarea class="input" name="initial_story" rows="4">{{ $case['story'] ?? '' }}</textarea></label>
                                </div>
                            </fieldset>
                        </section>

                        <!-- Sección 1: Entrevistas a Testigos -->
                        @php
                            $caseInterviews = collect();
                            $caseEvidenceFiles = collect();
                            $currentInvFolio = $selected['id'] ?? null;
                            $currentCaseFolio = $selected['case_id'] ?? ($case['id'] ?? null);

                            if (\App\Support\OriginalGiatepSchema::isActive() && $currentCaseFolio) {
                                $caseModel = \App\Models\OccupationalCase::where('folio', $currentCaseFolio)->with('evidences')->first();
                                $caseInterviews = $caseModel?->investigation?->interviews ?? collect();
                                $caseEvidenceFiles = $caseModel?->evidences ?? collect();
                            } elseif (! \App\Support\OriginalGiatepSchema::isActive() && $currentInvFolio && $currentInvFolio !== 'INV-NUEVA') {
                                $invModel = \App\Models\Investigation::where('folio', $currentInvFolio)->with('interviews.answers')->first();
                                if ($invModel) {
                                    $caseInterviews = $invModel->interviews;
                                }
                                $caseModel = \App\Models\OccupationalCase::where('folio', $currentCaseFolio)->with('evidences')->first();
                                $caseEvidenceFiles = $caseModel?->evidences ?? collect();
                            } elseif (! \App\Support\OriginalGiatepSchema::isActive() && $currentCaseFolio) {
                                $caseModel = \App\Models\OccupationalCase::where('folio', $currentCaseFolio)->with(['investigation.interviews.answers', 'evidences'])->first();
                                if ($caseModel && $caseModel->investigation) {
                                    $caseInterviews = $caseModel->investigation->interviews;
                                }
                                $caseEvidenceFiles = $caseModel?->evidences ?? collect();
                            }
                        @endphp
                        <section data-section="inv-1" class="hidden space-y-6" data-interviews-workspace>
                            <div data-interview-list>
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between border-b pb-4">
                                    <div>
                                        <h3 class="section-title mb-1">Entrevistas</h3>
                                        <p class="text-xs text-slate-600">Declaraciones oficiales según la normativa de investigación de la Municipalidad de Los Ángeles.</p>
                                    </div>
                                    <div class="flex flex-wrap gap-2">
                                        <button type="button" class="btn-secondary" data-create-legacy-interview>+ Crear entrevista anterior</button>
                                        <button type="button" class="btn-primary" data-create-interview>+ Crear entrevista</button>
                                    </div>
                                </div>
                                <div class="card table-card mt-5 shadow-none">
                                    <table class="data-table">
                                        <thead>
                                            <tr>
                                                <th>Entrevistado</th>
                                                <th>Tipología de Testigo</th>
                                                <th>Cargo</th>
                                                <th>Fecha</th>
                                                <th>Estado</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody data-interview-rows>
                                            @forelse($caseInterviews as $interview)
                                                <tr>
                                                    <td class="font-bold">{{ $interview->interviewee_name }}</td>
                                                    <td>
                                                        <span class="rounded bg-sky-50 px-2 py-0.5 text-xs font-bold text-sky-800">
                                                            {{ match($interview->witness_type) {
                                                                'direct' => 'Testigo presencial directo',
                                                                'affected' => 'Trabajador/a afectado/a',
                                                                'indirect' => 'Testigo indirecto o referencial',
                                                                'documentary' => 'Testigo documental',
                                                                'context' => 'Testigo circunstancial',
                                                                'supervisor' => 'Jefatura directa / Supervisor',
                                                                'technical' => 'Testigo técnico o especialista',
                                                                'institutional' => 'Testigo institucional',
                                                                'emergency' => 'Testigo de respuesta a la emergencia',
                                                                default => $interview->witness_type ?? 'Testigo presencial',
                                                            } }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $interview->interviewee_role ?? 'Sin cargo informado' }}</td>
                                                    <td>{{ $interview->interview_date ? $interview->interview_date->format('Y-m-d') : ($interview->created_at ? $interview->created_at->format('Y-m-d') : now()->toDateString()) }}</td>
                                                    <td><span class="status status-approved">{{ $interview->status ?? 'Completada' }}</span></td>
                                                    <td><button type="button" class="text-link" data-create-interview>Ver / Editar</button></td>
                                                </tr>
                                            @empty
                                                <tr data-empty-interview-row>
                                                    <td colspan="6" class="py-8 text-center text-xs text-slate-500">
                                                        <div class="flex flex-col items-center justify-center gap-1.5">
                                                            <span class="rounded-full bg-slate-100 p-2 text-slate-400">
                                                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                                                </svg>
                                                            </span>
                                                            <p class="font-bold text-slate-700">Sin entrevistas registradas</p>
                                                            <p class="text-slate-500">Aún no se han levantado declaraciones de testigos para esta investigación. Utilice <strong>"+ Crear entrevista"</strong> para registrar una.</p>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Editor de Entrevista -->
                            <div class="hidden" data-interview-editor data-interview-form>
                                <div class="mb-5 flex items-center justify-between border-b pb-4">
                                    <div>
                                        <p class="eyebrow">Formulario de entrevista</p>
                                        <h4 class="text-xl font-black text-slate-900" data-interview-form-title>Formulario de entrevista</h4>
                                    </div>
                                    <button type="button" class="btn-secondary" data-cancel-interview>← Volver a la Lista</button>
                                </div>
                                <div class="mb-5 hidden rounded-xl border border-red-200 bg-red-50 p-4 text-sm font-semibold text-red-800" data-interview-validation role="alert">Complete las preguntas obligatorias marcadas con * para guardar la entrevista.</div>
                                <fieldset disabled class="mt-6 hidden space-y-5" aria-hidden="true" data-legacy-questionnaire>
                                    <fieldset class="rounded-xl border border-slate-200 p-4 sm:p-5" data-question-group="chronology">
                                        <legend class="px-2 font-black text-blue-950">4. Reconstrucción cronológica de los hechos</legend>
                                        <div class="mt-3 grid gap-4">
                                            @foreach(['¿Qué trabajo o actividad se estaba realizando?', '¿Cuál era el objetivo de la tarea?'] as $question)
                                                <div data-development-question>
                                                    <div class="flex items-start justify-between gap-3">
                                                        <label class="text-sm font-semibold text-slate-800">{{ $question }}</label>
                                                        <label class="flex shrink-0 cursor-pointer items-center gap-2 rounded-lg border border-slate-300 px-3 py-2 text-xs font-bold">
                                                            <input type="checkbox" name="chronology_not_applicable[]" value="{{ $loop->index }}" data-development-na>No aplica
                                                        </label>
                                                    </div>
                                                    <textarea class="input mt-2" rows="2" name="chronology_detail[]"></textarea>
                                                </div>
                                            @endforeach
                                        </div>
                                    </fieldset>

                                    <fieldset class="rounded-xl border border-slate-200 p-4 sm:p-5" data-question-group="protection">
                                        <legend class="px-2 font-black text-blue-950">7. Equipos de protección personal</legend>
                                        <div data-yes-options>
                                            <div class="grid gap-3 lg:grid-cols-[minmax(0,1fr)_18rem] lg:items-center">
                                                <p class="text-sm font-semibold text-slate-800">¿La tarea requería elementos de protección personal?</p>
                                                <div class="grid grid-cols-3 gap-2">
                                                    @foreach(['yes' => 'Sí', 'no' => 'No', 'na' => 'No aplica'] as $value => $answer)
                                                        <label class="flex cursor-pointer items-center justify-center gap-2 rounded-lg border border-slate-300 px-2 py-3 text-xs font-bold">
                                                            <input type="radio" name="ppe_required" value="{{ $value }}">{{ $answer }}
                                                        </label>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </fieldset>

                                    <fieldset class="rounded-xl border border-slate-200 p-4 sm:p-5" data-question-group="training">
                                        <legend class="px-2 font-black text-blue-950">8. Información y capacitación</legend>
                                        <div data-yes-options>
                                            <div class="grid gap-3 lg:grid-cols-[minmax(0,1fr)_18rem] lg:items-center">
                                                <p class="text-sm font-semibold text-slate-800">¿Recibió instrucción para realizar esta tarea?</p>
                                                <div class="grid grid-cols-3 gap-2">
                                                    @foreach(['yes' => 'Sí', 'no' => 'No', 'na' => 'No aplica'] as $value => $answer)
                                                        <label class="flex cursor-pointer items-center justify-center gap-2 rounded-lg border border-slate-300 px-2 py-3 text-xs font-bold">
                                                            <input type="radio" name="instruction_received" value="{{ $value }}">{{ $answer }}
                                                        </label>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </fieldset>
                                </fieldset>
                                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                                    <label>
                                        <span class="label">Tipología de Testigo Oficial</span>
                                        <select class="input" name="witness_type" data-witness-type>
                                            <option value="direct">Testigo presencial directo</option>
                                            <option value="affected">Trabajador o trabajadora accidentada</option>
                                            <option value="indirect">Testigo presencial indirecto</option>
                                            <option value="documentary">Testigo documental</option>
                                            <option value="context">Testigo de contexto o circunstancial</option>
                                            <option value="supervisor">Supervisor o jefatura directa</option>
                                            <option value="technical">Testigo técnico o especialista</option>
                                            <option value="institutional">Testigo institucional</option>
                                            <option value="emergency">Testigo de respuesta a la emergencia</option>
                                        </select>
                                    </label>
                                    <label><span class="label">Fecha de la Entrevista</span><input type="date" class="input" name="interview_date" value="{{ now()->toDateString() }}"></label>
                                    <label><span class="label">Nombre Completo</span><input class="input" name="interviewee_name" value="" placeholder="Ej. Nombre completo del testigo o entrevistado"></label>
                                    <label><span class="label">RUT</span><input class="input" name="interviewee_rut" required placeholder="12.345.678-9"><span class="hint">Necesario para vincular la declaración con el testigo en la base de datos.</span></label>
                                    <label><span class="label">Cargo u Oficio</span><input class="input" name="interviewee_role" value="" placeholder="Ej. TENS / Auxiliar / Enfermera"></label>
                                    <label><span class="label">Lugar de la Entrevista</span><input class="input" name="interview_location" value="{{ $case['establishment'] ?? 'Establecimiento de salud' }}" placeholder="Ej. Box / Oficina de Prevención"></label>
                                </div>
                                @include('modules.investigations.partials.interview-questionnaire')
                                <div class="mt-6 flex justify-end gap-3 border-t pt-5">
                                    <button type="button" class="btn-secondary" data-cancel-interview>Cancelar</button>
                                    <button type="button" class="btn-primary" data-save-interview>Guardar Declaración</button>
                                </div>
                            </div>
                        </section>

                        <!-- Sección 2: Evidencias -->
                        <section data-section="inv-2" class="hidden space-y-6">
                            <h3 class="section-title">3. Evidencias y Documentos Recopilados</h3>
                            <div class="source-banner"><strong>Criterio del manual:</strong> la recolección debe cubrir información testimonial, física o del entorno y documental. Cada antecedente debe indicar origen, fecha y custodio para mantener trazabilidad.</div>
                            <div class="grid gap-3 md:grid-cols-3">
                                @foreach([
                                    ['Testimonial', 'Entrevistas y declaraciones', ! empty($case['witnesses']) && $case['witnesses'] !== 'Sin testigos'],
                                    ['Física y entorno', 'Fotografías, croquis, equipos y condiciones', ($case['evidence_count'] ?? 0) > 0],
                                    ['Documental', 'Procedimientos, capacitación, matrices y registros', ($case['evidence_count'] ?? 0) > 1],
                                ] as [$type, $description, $available])
                                    <div class="rounded-xl border {{ $available ? 'border-emerald-200 bg-emerald-50/60' : 'border-amber-200 bg-amber-50/60' }} p-4">
                                        <div class="flex items-center justify-between gap-2"><strong class="text-sm text-slate-950">{{ $type }}</strong><span class="status {{ $available ? 'status-approved' : 'status-pending' }}">{{ $available ? 'Disponible' : 'Por recopilar' }}</span></div>
                                        <p class="mt-2 text-xs text-slate-600">{{ $description }}</p>
                                    </div>
                                @endforeach
                            </div>
                            <div class="rounded-xl border border-slate-200 bg-white p-4">
                                <h4 class="text-sm font-bold text-slate-900">Archivos de evidencia asociados al caso</h4>
                                @forelse($caseEvidenceFiles as $evidence)
                                    <div class="mt-3 flex flex-wrap items-center justify-between gap-2 rounded-lg bg-slate-50 px-3 py-2">
                                        <span class="text-sm text-slate-800">{{ $evidence->original_name }}</span>
                                        <a class="text-link" href="{{ route('modules.cases.evidence.download', ['case' => $currentCaseFolio, 'evidence' => $evidence->id]) }}">Descargar evidencia</a>
                                    </div>
                                @empty
                                    <p class="mt-2 text-xs text-slate-500">Este caso aún no tiene archivos de evidencia guardados.</p>
                                @endforelse
                            </div>
                            <div class="grid gap-4 md:grid-cols-3">
                                <label><span class="label">Tipo de Evidencia</span><select class="input"><option>Fotografía del lugar</option><option>Procedimiento de Trabajo Seguro (PTS)</option><option>Registro de Capacitación / Inducción</option><option>Certificado de Atención Médica ACHS</option><option>Hoja de Datos de Seguridad (HDS)</option></select></label>
                                <label><span class="label">Origen / Custodio</span><input class="input" placeholder="Ej. Prevencionista / Jefatura"></label>
                                <label><span class="label">Fecha de Recopilación</span><input type="date" class="input" value="2026-10-03"></label>
                            </div>
                            <label class="upload-zone">
                                <input type="file" multiple data-local-file class="sr-only">
                                <strong>Adjuntar archivos de evidencia fotográfica o documental</strong>
                                <span>Formatos JPG, PNG, PDF para verificación en el informe técnico.</span>
                            </label>
                            <ul class="space-y-2" data-file-list></ul>
                        </section>

                        @php $linkedCaseStory = collect($demo['cases'])->firstWhere('id', $selected['case_id'])['story'] ?? ($selected['final_story'] ?? ''); @endphp
                        <!-- Relato cronológico y flujo de análisis externo con revisión local -->
                        <section data-section="inv-3" class="hidden space-y-6" aria-label="Relato cronológico" data-external-analysis>
                            <div>
                                <h3 class="section-title mb-1">4. Análisis de la investigación</h3>
                                <p class="text-sm text-slate-600">Genera un prompt para una IA externa y luego entrega su respuesta a LM Studio para contrastarla con las fuentes y preparar los hechos, el árbol causal y las medidas.</p>
                            </div>
                            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-950">
                                El prompt incluye el relato, las entrevistas guardadas y los antecedentes textuales disponibles. Los archivos adjuntos se identifican, pero su contenido no se lee automáticamente. La respuesta externa se considera una propuesta y la IA local puede descartar datos sin respaldo.
                            </div>
                            <label class="block">
                                <span class="label">Relato técnico para analizar</span>
                                <textarea class="input" rows="7" maxlength="30000" data-analysis-story>{{ $linkedCaseStory }}</textarea>
                            </label>
                            <div class="flex flex-wrap items-end gap-3">
                                <label class="block"><span class="label">Cómo generar el prompt</span><select class="input" data-prompt-provider><option value="template">Plantilla del sistema</option><option value="groq">Redactar con modelo de API (Groq)</option></select></label>
                                <button type="button" class="btn-primary" data-create-external-prompt>Generar prompt para IA externa</button>
                                <span class="text-sm text-slate-600" data-analysis-status role="status" aria-live="polite">El prompt todavía no se ha generado.</span>
                            </div>
                            <label class="block">
                                <span class="label">Prompt generado</span>
                                <textarea class="input font-mono text-xs" rows="12" readonly data-external-prompt placeholder="Aquí aparecerá el prompt listo para copiar y pegar en la IA externa."></textarea>
                            </label>
                            <div class="flex flex-wrap gap-3">
                                <button type="button" class="btn-secondary" data-copy-external-prompt disabled>Copiar prompt</button>
                            </div>
                            <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_20rem]">
                                <label class="block">
                                    <span class="label">Respuesta de la IA externa</span>
                                    <textarea class="input font-mono text-xs" rows="12" maxlength="30000" data-external-response placeholder="Pega aquí la respuesta JSON de la IA externa."></textarea>
                                </label>
                                <div class="space-y-3">
                                    <label class="block"><span class="label">Modelo local para revisar</span><select class="input" data-local-model><option value="">Modelo configurado en LM Studio</option></select></label>
                                    <button type="button" class="btn-primary w-full" data-import-external>Revisar con IA local y completar la página</button>
                                    <button type="button" class="btn-secondary w-full" data-fill-without-ai>Completar campos sin IA</button>
                                    <p class="text-xs text-slate-500">El botón superior contrasta la respuesta con LM Studio. El inferior carga el JSON como borrador local, sin consultar modelos. Revisa los datos y pulsa “Guardar cambios” al final del formulario para guardar también los hechos, el árbol y las medidas.</p>
                                </div>
                            </div>
                            <div class="hidden space-y-3 rounded-xl border border-sky-200 bg-sky-50 p-4" data-analysis-result aria-live="polite">
                                <h4 class="font-bold text-slate-900">Borrador del análisis</h4>
                                <div class="grid gap-3 sm:grid-cols-3 text-sm">
                                    <p><strong>Hechos:</strong> <span data-result-fact-count>0</span></p>
                                    <p><strong>Nodos del árbol:</strong> <span data-result-node-count>0</span></p>
                                    <p><strong>Medidas:</strong> <span data-result-measure-count>0</span></p>
                                </div>
                                <ul class="list-disc space-y-1 pl-5 text-sm text-slate-700" data-analysis-warnings></ul>
                                <div class="hidden rounded-lg border border-indigo-200 bg-white p-3" data-questionnaire-proposals>
                                    <h5 class="text-sm font-bold text-indigo-950">Sugerencias de selección para entrevistas</h5>
                                    <p class="mt-1 text-xs text-slate-600">Son propuestas respaldadas por las fuentes; no modifican ni reemplazan ninguna declaración.</p>
                                    <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-slate-700" data-questionnaire-selections></ul>
                                </div>
                                <button type="button" class="btn-primary" data-save-external-analysis disabled>Guardar hechos, árbol y medidas en el expediente</button>
                                <p class="text-xs text-slate-600">Revisa cada sección antes de guardar. Esta acción reemplaza el análisis estructurado actualmente guardado para este expediente.</p>
                            </div>
                        </section>

                        <!-- Sección 4: Hechos Constatados -->
                        @php
                            $dbFacts = collect();
                            if (!empty($selected['id']) && $selected['id'] !== 'INV-NUEVA') {
                                $invFactsModel = \App\Models\Investigation::where(\App\Support\OriginalGiatepSchema::isActive() ? 'Folio' : 'folio', $selected['id'])->with('facts')->first();
                                if ($invFactsModel) {
                                    $dbFacts = $invFactsModel->facts;
                                }
                            } elseif (!empty($selected['case_id'])) {
                                $caseFactsModel = \App\Models\OccupationalCase::where(\App\Support\OriginalGiatepSchema::isActive() ? 'Folio' : 'folio', $selected['case_id'])->with('investigation.facts')->first();
                                if ($caseFactsModel && $caseFactsModel->investigation) {
                                    $dbFacts = $caseFactsModel->investigation->facts;
                                }
                            }
                        @endphp
                        <section data-section="inv-4" class="hidden space-y-4">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <h3 class="section-title">5. Hechos Constatados (Cronología Verificada)</h3>
                                <div class="flex flex-wrap items-center gap-2">
                                    <button type="button" class="btn-secondary" data-facts-edit>Editar hechos</button>
                                    <button type="button" class="btn-secondary hidden" data-fact-add>Agregar hecho</button>
                                    <button type="button" class="btn-primary hidden" data-facts-save>Guardar hechos</button>
                                    <button type="button" class="btn-secondary hidden" data-facts-cancel>Cancelar</button>
                                </div>
                            </div>
                            <p class="hidden text-sm text-slate-600" data-facts-status aria-live="polite"></p>
                            <div class="space-y-3" data-derived-facts>
                                @forelse($dbFacts as $fact)
                                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 font-semibold text-slate-800">
                                        <span class="rounded bg-sky-700 px-2 py-0.5 text-xs text-white mr-2">H-{{ str_pad((string)$fact->position, 2, '0', STR_PAD_LEFT) }}</span>
                                        {{ $fact->description }}
                                    </div>
                                @empty
                                    <div class="empty-state text-center py-6">
                                        <p class="font-bold text-slate-700">Aún no se han constatado hechos para esta investigación.</p>
                                        <p class="mt-1 text-xs text-slate-500">Analice el relato cronológico con IA o agregue los hechos verificados de la investigación.</p>
                                    </div>
                                @endforelse
                            </div>
                        </section>

                        <!-- Sección 5: Árbol de Causas Visual -->
                        @php
                            $dbNodes = collect();
                            if (!empty($selected['id']) && $selected['id'] !== 'INV-NUEVA') {
                                $invTreeModel = \App\Models\Investigation::where(\App\Support\OriginalGiatepSchema::isActive() ? 'Folio' : 'folio', $selected['id'])->with('causalNodes')->first();
                                if ($invTreeModel) {
                                    $dbNodes = $invTreeModel->causalNodes;
                                }
                            } elseif (!empty($selected['case_id'])) {
                                $caseTreeModel = \App\Models\OccupationalCase::where(\App\Support\OriginalGiatepSchema::isActive() ? 'Folio' : 'folio', $selected['case_id'])->with('investigation.causalNodes')->first();
                                if ($caseTreeModel && $caseTreeModel->investigation) {
                                    $dbNodes = $caseTreeModel->investigation->causalNodes;
                                }
                            }
                            $storedGraphNode = $dbNodes->first(fn ($node) => ($node->node_type ?? $node->type ?? '') === 'graph');
                            $storedTreeGraph = $storedGraphNode ? json_decode($storedGraphNode->description, true) : null;
                        @endphp
                        <section data-section="inv-5" class="hidden space-y-6">
                            <div class="flex items-center justify-between border-b pb-4">
                                <div>
                                    <h3 class="section-title mb-1">6. Árbol de Causas Técnico</h3>
                                    <p class="text-xs text-slate-600">Diagrama causal retrospectivo desde el hecho final (lesión) hacia las causas inmediatas y de gestión.</p>
                                </div>
                                <div class="flex items-center gap-3"><button type="button" class="btn-secondary" data-open-results-editor="tree">Editar árbol</button><span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-900">Método Oficial DCS</span></div>
                            </div>

                            <!-- Gráfico Visual del Árbol de Causas -->
                            <div class="rounded-3xl border border-slate-200 bg-slate-50 p-6 sm:p-10 space-y-8" data-derived-tree>
                                @if(is_array($storedTreeGraph))
                                    <div class="causal-graph-viewport" data-tree-graph-json="{{ json_encode($storedTreeGraph, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG) }}"></div>
                                @elseif($dbNodes->isNotEmpty())
                                    @php $legacyTreeNodes = $dbNodes->reject(fn ($node) => ($node->node_type ?? $node->type ?? '') === 'graph'); @endphp
                                    @php $dbNodes = $legacyTreeNodes; @endphp
                                    @php $finalNodes = $dbNodes->where('type', 'final'); @endphp
                                    @if($finalNodes->isNotEmpty())
                                        <div class="text-center">
                                            <span class="text-[10px] font-black uppercase tracking-widest text-red-700">Hecho Final (Lesión / Suceso)</span>
                                            @foreach($finalNodes as $fNode)
                                                <div class="cause-node cause-final mt-2 shadow-md">
                                                    <p class="text-sm font-black text-red-950">{{ $fNode->description }}</p>
                                                    <span class="mt-1 block text-[10px] text-red-700 font-semibold">Hecho probado verificado</span>
                                                </div>
                                            @endforeach
                                            <div class="cause-connector">↑</div>
                                        </div>
                                    @endif

                                    @php $causeNodes = $dbNodes->where('type', 'cause'); @endphp
                                    @if($causeNodes->isNotEmpty())
                                        <div>
                                            <p class="text-center text-[10px] font-black uppercase tracking-widest text-sky-800 mb-3">Causas Inmediatas (Condiciones y Acciones)</p>
                                            <div class="grid gap-4 sm:grid-cols-2 max-w-2xl mx-auto">
                                                @foreach($causeNodes as $cNode)
                                                    <div class="cause-node">
                                                        <span class="rounded bg-sky-100 px-2 py-0.5 text-[10px] font-bold text-sky-900">Causa Identificada</span>
                                                        <p class="mt-2 text-xs font-bold text-slate-900">{{ $cNode->description }}</p>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <div class="cause-connector text-center">↑</div>
                                        </div>
                                    @endif

                                    @php $originNodes = $dbNodes->where('type', 'origin'); @endphp
                                    @if($originNodes->isNotEmpty())
                                        <div>
                                            <p class="text-center text-[10px] font-black uppercase tracking-widest text-slate-600 mb-3">Causa Básica / Gestión Preventiva (Origen)</p>
                                            <div class="max-w-md mx-auto">
                                                @foreach($originNodes as $oNode)
                                                    <div class="cause-node border-slate-300 bg-white">
                                                        <span class="rounded bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-700">Falla de Organización</span>
                                                        <p class="mt-2 text-xs font-bold text-slate-900">{{ $oNode->description }}</p>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                @else
                                    <div class="empty-state text-center py-8">
                                        <p class="font-bold text-slate-700">Aún no se ha estructurado el árbol causal para esta investigación.</p>
                                        <p class="mt-1 text-xs text-slate-500">El árbol de causas se construirá al ejecutar el análisis causal o validar las causas técnicas.</p>
                                    </div>
                                @endif
                            </div>
                        </section>

                        <!-- Sección 6: Matriz y Medidas -->
                        <section data-section="inv-6" class="hidden space-y-6">
                            <h3 class="section-title">7. Matriz de Medidas de Control y Plan de Acción</h3>
                            <button type="button" class="btn-secondary" data-open-results-editor="measures">Editar medidas</button>
                            <div class="card table-card shadow-none">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Medida Propuesta</th>
                                            <th>Jerarquía de Control</th>
                                            <th>Responsable Institucional</th>
                                            <th>Plazo</th>
                                            <th>Hecho relacionado</th>
                                            <th>Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody data-derived-measures>
                                        @php
                                            $dbMeasures = collect();
                                            if (!empty($selected['id']) && $selected['id'] !== 'INV-NUEVA') {
                                                $measureInvestigation = \App\Models\Investigation::where(\App\Support\OriginalGiatepSchema::isActive() ? 'Folio' : 'folio', $selected['id'])->with('measures')->first();
                                                if ($measureInvestigation) $dbMeasures = $measureInvestigation->measures;
                                            }
                                            if ($dbMeasures->isEmpty() && !empty($selected['case_id'])) {
                                                $measureCase = \App\Models\OccupationalCase::where(\App\Support\OriginalGiatepSchema::isActive() ? 'Folio' : 'folio', $selected['case_id'])->with('investigation.measures')->first();
                                                if ($measureCase?->investigation) $dbMeasures = $measureCase->investigation->measures;
                                            }
                                            $displayMeasures = $dbMeasures->isNotEmpty()
                                                ? $dbMeasures
                                                : $demo['measures']->where('case_id', $selected['case_id']);
                                        @endphp
                                        @forelse($displayMeasures as $m)
                                            <tr>
                                                <td class="font-bold text-slate-900">{{ data_get($m, 'description') }}</td>
                                                <td><span class="rounded bg-sky-50 px-2 py-0.5 text-xs font-bold text-sky-800">{{ data_get($m, 'category') }}</span></td>
                                                <td>{{ data_get($m, 'responsible', data_get($m, 'owner', '')) }}</td>
                                                <td>{{ data_get($m, 'due_date') ? \Illuminate\Support\Carbon::parse(data_get($m, 'due_date'))->format('d-m-Y') : data_get($m, 'due', '') }}</td>
                                                <td>Ver detalle de medida</td>
                                                <td><span class="status {{ $statusClass(data_get($m, 'status', 'Pendiente')) }}">{{ data_get($m, 'status', 'Pendiente') }}</span></td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="6" class="p-6 text-center text-slate-500">Aún no hay medidas registradas para esta investigación.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </section>

                        <div class="hidden rounded-2xl border border-sky-200 bg-white p-5 shadow-lg sm:p-7" data-results-editor aria-label="Editar resultados">
                            <div class="flex items-center justify-between gap-4"><h3 class="text-xl font-black" data-results-editor-title>Editar resultados</h3><button type="button" class="btn-secondary" data-close-results-editor>Cerrar edición</button></div>
                            <p class="my-3 text-sm text-slate-600">Corrige los datos o agrega y quita elementos de esta sección. Guarda para aplicar los cambios al expediente.</p>
                            <div class="space-y-6" data-results-editor-body></div>
                            <div class="mt-6 flex flex-wrap items-center justify-between gap-3 border-t pt-4"><p class="text-sm text-slate-600" data-results-editor-status></p><button type="button" class="btn-primary" data-save-results-editor>Guardar esta sección</button></div>
                        </div>

                        <!-- Sección 7: Participantes -->
                        <section data-section="inv-7" class="hidden space-y-6">
                            <h3 class="section-title">8. Participantes y Validación de la Comisión</h3>
                            <div class="card table-card shadow-none">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Integrante</th>
                                            <th>Rol en la Investigación</th>
                                            <th>Establecimiento</th>
                                            <th>Estado de Firma</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="font-bold">{{ auth()->user()->name }}</td>
                                            <td>{{ auth()->user()->roles->pluck('name')->first() ?? 'Investigador / Prevención' }}</td>
                                            <td>{{ $case['establishment'] ?? 'Dirección Comunal de Salud' }}</td>
                                            <td><span class="status status-approved">Activo</span></td>
                                        </tr>
                                        <tr>
                                            <td class="font-bold">Representante CPHS</td>
                                            <td>Comité Paritario de Higiene y Seguridad</td>
                                            <td>{{ $case['establishment'] ?? 'Establecimiento APS' }}</td>
                                            <td><span class="status status-pending">Pendiente de Firma</span></td>
                                        </tr>
                                        <tr>
                                            <td class="font-bold">Delegado de Seguridad</td>
                                            <td>Delegado de Seguridad del Centro</td>
                                            <td>{{ $case['establishment'] ?? 'Establecimiento APS' }}</td>
                                            <td><span class="status status-pending">Pendiente de Firma</span></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </section>

                        <!-- Sección 8: Dictamen y Cierre -->
                        <section data-section="inv-8" class="hidden space-y-6">
                            <h3 class="section-title">9. Dictamen Final y Cierre de Expediente</h3>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-6 space-y-4">
                                <div class="grid gap-4 sm:grid-cols-3 text-xs">
                                    <div><dt>Estado del Expediente:</dt><dd class="text-sm font-bold">{{ $selected['status'] }}</dd></div>
                                    <div><dt>Avance General:</dt><dd class="text-sm font-bold text-sky-700">{{ $selected['progress'] }}%</dd></div>
                                    <div><dt>Pendiente:</dt><dd class="text-sm font-bold text-slate-800">{{ $selected['pending'] }}</dd></div>
                                </div>
                                <div class="border-t pt-4">
                                    <label class="label">Dictamen de Conclusión Técnica</label>
                                    <textarea class="input text-xs leading-relaxed" rows="4" placeholder="Ingrese el dictamen de conclusión técnica y recomendaciones de la comisión investigadora..."></textarea>
                                </div>
                                @if(auth()->user()->hasRole('cphs') || auth()->user()->hasRole('delegado') || auth()->user()->hasRole('prevencion'))
                                    <div class="flex justify-end pt-3">
                                        <button type="submit" name="action" value="Investigación concluida formalmente" class="btn-primary bg-emerald-700 hover:bg-emerald-800">
                                             Concluir y Cerrar Expediente de Investigación
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </section>
                    </div>

                    <footer class="flex flex-wrap justify-between gap-3 border-t bg-slate-50/90 p-5">
                        @if($selected['status'] === 'En desarrollo')
                        <div class="flex flex-wrap items-center gap-3">
                            <button class="btn-secondary" type="submit" formnovalidate data-save-workflow onclick="this.form.querySelector('[data-investigation-workflow-action]').value='save'">Guardar cambios</button>
                            <p class="text-sm font-semibold text-slate-700" data-workflow-save-status role="status" aria-live="polite"></p>
                        </div>
                        <div class="flex gap-2">
                            <button name="workflow_action" value="submit" class="btn-primary" onclick="return confirm('La investigación pasará a revisión de Prevención. ¿Desea continuar?')">Pasar a revisión</button>
                        </div>
                        @elseif($selected['status'] === 'En validación')
                            <div></div>
                            <div class="flex gap-2">
                                <button name="workflow_action" value="close" class="btn-primary" onclick="return confirm('Está a punto de cerrar la investigación. Al completarla quedará bloqueada para edición. ¿Desea continuar?')">Cerrar investigación</button>
                            </div>
                        @else
                            <p class="text-sm font-bold text-emerald-800">Investigación completada · Solo lectura</p>
                        @endif
                    </footer>
                </form>
                <section class="card space-y-4 p-6 shadow-sm" aria-label="Observaciones recibidas">
                    <div><h3 class="text-lg font-black text-slate-950">Observaciones de la investigación</h3><p class="mt-1 text-sm text-slate-600">Responde cada observación en su mismo hilo.</p></div>
                    @if(auth()->user()->hasRole('prevencion') && in_array($selected['status'], ['En desarrollo', 'En validación'], true))
                        <form method="POST" action="{{ route('modules.investigations.observations.store', ['case' => $selected['case_id']]) }}" class="rounded-xl border border-sky-200 bg-sky-50 p-4 space-y-3">
                            @csrf
                            <label class="label" for="investigation-observation">Registrar observación para el equipo investigador</label>
                            <textarea id="investigation-observation" class="input" name="body" rows="3" minlength="5" maxlength="5000" required placeholder="Escribe cualquier observación relacionada con esta investigación"></textarea>
                            <button class="btn-secondary" type="submit">Enviar observación</button>
                        </form>
                    @endif
                    @forelse($reportObservations as $observation)
                        <article class="rounded-xl border border-slate-200 p-4">
                            <div class="flex flex-wrap justify-between gap-2"><strong>{{ $observation->author_name }} · {{ $observation->author_role }}</strong><span class="text-xs text-slate-500">{{ $observation->created_at }} · {{ ucfirst($observation->status) }}</span></div>
                            <p class="mt-3 whitespace-pre-line text-sm text-slate-700">{{ $observation->body }}</p>
                            @foreach($observation->replies as $reply)
                                <div class="ml-4 mt-3 border-l-2 border-sky-300 pl-4"><p class="text-xs font-bold">Respuesta de {{ $reply->author_name }} · {{ $reply->author_role }}</p><p class="mt-2 whitespace-pre-line text-sm">{{ $reply->body }}</p></div>
                            @endforeach
                            @if($canReplyObservation && in_array($selected['status'], ['En desarrollo', 'En validación'], true))
                                <form method="POST" action="{{ route('modules.investigations.observations.reply', ['observation' => $observation->id]) }}" class="mt-4 space-y-2">
                                    @csrf
                                    <textarea class="input" name="body" rows="3" maxlength="5000" required placeholder="Escribe la respuesta del equipo investigador"></textarea>
                                    <button class="btn-secondary" type="submit">Responder observación</button>
                                </form>
                            @endif
                        </article>
                    @empty
                        <p class="text-sm text-slate-500">No hay observaciones registradas.</p>
                    @endforelse
                </section>
            </div>
            @endif
        @else
            <!-- Listado de Investigaciones -->
            <div class="filter-bar">
                <input class="input" placeholder="Buscar por código o trabajador" data-table-search>
                <select class="input" data-table-filter="status">
                    <option value="">Todos los estados</option>
                    @foreach(['En proceso', 'Pendiente', 'Realizada', 'No aplica'] as $st)
                        <option>{{ $st }}</option>
                    @endforeach
                </select>
                <select class="input">
                    <option value="">Todos los centros de salud</option>
                    @foreach($demo['establishments'] as $est)
                        <option>{{ $est }}</option>
                    @endforeach
                </select>
            </div>

            <div class="card table-card shadow-sm">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Expediente</th>
                            <th>Caso Vinculado</th>
                            <th>Trabajador Afectado</th>
                            <th>Centro de Salud</th>
                            <th>Responsable Técnico</th>
                            <th>Avance</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($demo['investigations'] as $inv)
                            <tr data-table-row data-status="{{ $inv['status'] }}">
                                <td class="font-bold text-blue-950">{{ $inv['id'] }}</td>
                                <td><span class="rounded bg-slate-100 px-2 py-0.5 text-xs font-bold">{{ $inv['case_id'] }}</span></td>
                                <td>{{ $inv['worker'] ?? 'Funcionario/a' }}</td>
                                <td>{{ $inv['establishment'] ?? 'APS Los Ángeles' }}</td>
                                <td>{{ $inv['owner'] }}</td>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <div class="progress"><span style="width:{{ $inv['progress'] }}%"></span></div>
                                        <small class="font-bold">{{ $inv['progress'] }}%</small>
                                    </div>
                                </td>
                                <td><span class="status {{ $statusClass($inv['status']) }}">{{ $inv['status'] }}</span></td>
                                <td><a class="text-link" href="{{ route('demo.show', ['module'=>'investigations','id'=>$inv['id']]) }}">Abrir Expediente</a></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="p-8 text-center text-slate-500">
                                    <p class="font-bold text-slate-700">No hay investigaciones registradas en el sistema.</p>
                                    <p class="mt-1 text-xs text-slate-400">Las investigaciones aparecerán cuando se inicien desde un caso registrado.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif

    <!-- ==================== MÓDULO: REVISIONES Y VALIDACIONES ==================== -->
    @elseif($module === 'reviews')
        @php
            $pendingReviews = $demo['investigations']->whereIn('status', ['En desarrollo', 'Con observaciones']);
        @endphp
        @if($pendingReviews->isEmpty())
            <section class="rounded-3xl border border-dashed border-slate-300 bg-white px-6 py-12 text-center shadow-xs">
                <div class="mx-auto grid size-14 place-items-center rounded-2xl bg-sky-100 text-2xl font-black text-sky-800">0</div>
                <h2 class="mt-5 text-2xl font-black text-slate-950">No hay expedientes pendientes de validación</h2>
                <p class="mx-auto mt-2 max-w-2xl text-sm leading-relaxed text-slate-600">La bandeja técnica de revisión mostrará los expedientes enviados por el Comité Paritario, Delegados o Prevención cuando requieran validación.</p>
                @if(auth()->user()->hasPermission('investigations.read'))
                    <a href="{{ route('modules.investigations.index') }}" class="btn-primary mt-6">Ir a Investigaciones</a>
                @endif
            </section>
        @else
            <div class="card table-card shadow-sm">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Expediente</th>
                            <th>Caso Vinculado</th>
                            <th>Trabajador Afectado</th>
                            <th>Centro de Salud</th>
                            <th>Responsable</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingReviews as $rev)
                            <tr>
                                <td class="font-bold text-blue-950">{{ $rev['id'] }}</td>
                                <td>{{ $rev['case_id'] }}</td>
                                <td>{{ $rev['worker'] ?? 'Funcionario/a' }}</td>
                                <td>{{ $rev['establishment'] ?? 'APS Los Ángeles' }}</td>
                                <td>{{ $rev['owner'] }}</td>
                                <td><span class="status {{ $statusClass($rev['status']) }}">{{ $rev['status'] }}</span></td>
                                <td><a class="text-link" href="{{ route('demo.show', ['module'=>'investigations','id'=>$rev['id']]) }}">Revisar Expediente</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

    <!-- ==================== MÓDULO: OBSERVACIONES ==================== -->
    @elseif($module === 'observations')
        @php $observation = $selected ?: $demo['observations']->first(); @endphp
        @if($demo['observations']->isEmpty())
            <section class="rounded-3xl border border-dashed border-slate-300 bg-white px-6 py-12 text-center shadow-xs">
                <div class="mx-auto grid size-14 place-items-center rounded-2xl bg-sky-100 text-2xl font-black text-sky-800">0</div>
                <h2 class="mt-5 text-2xl font-black text-slate-950">No hay observaciones registradas</h2>
                <p class="mx-auto mt-2 max-w-2xl text-sm leading-relaxed text-slate-600">Las observaciones técnicas aparecerán cuando una investigación o medida sea enviada a revisión.</p>
                @if(auth()->user()->hasPermission('investigations.read'))
                    <a href="{{ route('modules.investigations.index') }}" class="btn-primary mt-6">Ir a Investigación</a>
                @endif
            </section>
        @else
        <div class="filter-bar">
            <input class="input" placeholder="Buscar caso o investigación" data-table-search>
            <select class="input">
                <option>Todas las secciones observadas</option>
                <option>Investigación / Hechos</option>
                <option>Medidas de control</option>
                <option>Plan de acción</option>
            </select>
            <select class="input">
                <option>Todos los estados</option>
                <option>Pendiente</option>
                <option>Respondida</option>
                <option>Resuelta</option>
            </select>
        </div>

        <div class="grid gap-6 xl:grid-cols-[1fr_26rem]">
            <div class="card table-card shadow-sm">
                <div class="border-b p-4 flex items-center justify-between">
                    <h3 class="font-black text-slate-900">Bandeja de Observaciones Técnicas</h3>
                    <span class="text-xs text-slate-500">Jefatura Directa, Alta Dirección y Prevención</span>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Folio</th>
                            <th>Caso / Investigación</th>
                            <th>Sección Observada</th>
                            <th>Autor y Rol</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($demo['observations'] as $obs)
                            <tr>
                                <td class="font-bold text-blue-950">{{ $obs['id'] }}</td>
                                <td>{{ $obs['case_id'] }}<small>{{ $obs['investigation_id'] }}</small></td>
                                <td><span class="rounded bg-slate-100 px-2 py-0.5 text-xs font-bold">{{ $obs['section'] }}</span></td>
                                <td><strong>{{ $obs['author'] }}</strong><small>{{ $obs['role'] }}</small></td>
                                <td>{{ $obs['date'] }}</td>
                                <td><span class="status {{ $statusClass($obs['status']) }}">{{ $obs['status'] }}</span></td>
                                <td><a class="text-link" href="{{ route('demo.show', ['module'=>'observations','id'=>$obs['id']]) }}">Ver Detalle</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <aside class="card p-6 shadow-sm space-y-4">
                <div class="border-b pb-4">
                    <span class="eyebrow">{{ $observation['id'] }} · {{ $observation['case_id'] }}</span>
                    <h3 class="mt-1 text-xl font-black text-slate-900">{{ $observation['section'] }}</h3>
                    <p class="mt-2 text-xs font-semibold text-slate-500">
                        Emitida por: <strong>{{ $observation['author'] }}</strong> ({{ $observation['role'] }}) el {{ $observation['date'] }}
                    </p>
                </div>

                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-xs leading-relaxed text-slate-800">
                    {{ $observation['description'] }}
                </div>

                <form method="POST" action="{{ route('demo.action', 'observations') }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="action" value="Respuesta a observación registrada">
                    <input type="hidden" name="return_to" value="{{ url()->current() }}">
                    <label>
                        <span class="label">Respuesta o Corrección Efectuada</span>
                        <textarea class="input text-xs" name="note" rows="5" placeholder="Indique las medidas adoptadas o justificación técnica para atender esta observación." required></textarea>
                    </label>
                    <button class="btn-primary w-full" type="submit">Enviar Respuesta a Observación</button>
                </form>
            </aside>
        </div>
        @endif

    <!-- ==================== MÓDULO: MEDIDAS Y PLANES DE ACCIÓN ==================== -->
    @elseif($module === 'measures')
        @php $measure = $selected ?: $demo['measures']->first(); @endphp
        @if($demo['measures']->isEmpty())
            <section class="rounded-3xl border border-dashed border-slate-300 bg-white px-6 py-12 text-center shadow-xs">
                <div class="mx-auto grid size-14 place-items-center rounded-2xl bg-sky-100 text-2xl font-black text-sky-800">0</div>
                <h2 class="mt-5 text-2xl font-black text-slate-950">No hay medidas registradas</h2>
                <p class="mx-auto mt-2 max-w-2xl text-sm leading-relaxed text-slate-600">Las medidas y sus planes de acción aparecerán cuando se identifiquen causas dentro de una investigación.</p>
                @if(auth()->user()->hasPermission('investigations.read'))
                    <a href="{{ route('modules.investigations.index') }}" class="btn-primary mt-6">Ir a Investigación</a>
                @endif
            </section>
        @else
        <div class="grid gap-4 sm:grid-cols-4">
            <div class="metric"><span class="text-xs font-bold uppercase text-slate-500">Total Medidas</span><strong class="text-3xl font-black text-slate-950">{{ $demo['measures']->count() }}</strong></div>
            <div class="metric"><span class="text-xs font-bold uppercase text-slate-500">En Ejecución</span><strong class="text-3xl font-black text-blue-700">{{ $demo['measures']->where('status','En ejecución')->count() }}</strong></div>
            <div class="metric"><span class="text-xs font-bold uppercase text-slate-500">Implementadas</span><strong class="text-3xl font-black text-emerald-700">{{ $demo['measures']->where('status','Implementada')->count() }}</strong></div>
            <div class="metric metric-danger"><span class="text-xs font-bold uppercase text-red-700">Vencidas</span><strong class="text-3xl font-black text-red-800">{{ $demo['measures']->where('status','Vencida')->count() }}</strong></div>
        </div>

        <div class="filter-bar">
            <input class="input" placeholder="Buscar por medida, caso o responsable" data-table-search>
            <select class="input">
                <option value="">Todas las categorías</option>
                <option>Ingeniería</option>
                <option>Administrativa</option>
                <option>Equipos de protección personal</option>
                <option>Sustitución</option>
            </select>
            <select class="input">
                <option value="">Todos los estados</option>
                <option>Pendiente</option>
                <option>En ejecución</option>
                <option>Implementada</option>
                <option>Vencida</option>
            </select>
        </div>

        <div class="grid gap-6 xl:grid-cols-[1fr_26rem]">
            <div class="card table-card shadow-sm">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Caso</th>
                            <th>Medida de Control</th>
                            <th>Responsable</th>
                            <th>Plazo</th>
                            <th>Avance</th>
                            <th>Estado</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($demo['measures'] as $m)
                            <tr>
                                <td class="font-bold text-blue-950">{{ $m['id'] }}</td>
                                <td><span class="rounded bg-slate-100 px-2 py-0.5 text-xs font-bold">{{ $m['case_id'] }}</span></td>
                                <td>
                                    <strong class="text-slate-900">{{ $m['description'] }}</strong>
                                    <small>{{ $m['category'] }}</small>
                                </td>
                                <td>{{ $m['owner'] }}</td>
                                <td>{{ $m['due'] }}</td>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <div class="progress"><span style="width:{{ $m['progress'] }}%"></span></div>
                                        <small class="font-bold">{{ $m['progress'] }}%</small>
                                    </div>
                                </td>
                                <td><span class="status {{ $statusClass($m['status']) }}">{{ $m['status'] }}</span></td>
                                <td><a class="text-link" href="{{ route('demo.show', ['module'=>'measures','id'=>$m['id']]) }}">Editar</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <aside class="card p-6 shadow-sm space-y-4">
                <div class="border-b pb-4">
                    <span class="eyebrow">{{ $measure['id'] }} · {{ $measure['case_id'] }}</span>
                    <h3 class="mt-1 text-lg font-black text-slate-900">{{ $measure['description'] }}</h3>
                    <p class="mt-1 text-xs text-slate-500">Causa: {{ $measure['cause'] }}</p>
                </div>

                <form method="POST" action="{{ route('demo.action', 'measures') }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="return_to" value="{{ url()->current() }}">
                    <label>
                        <span class="label">Porcentaje de Avance ({{ $measure['progress'] }}%)</span>
                        <input type="range" min="0" max="100" value="{{ $measure['progress'] }}" class="w-full accent-sky-600">
                    </label>
                    <label>
                        <span class="label">Estado de la Medida</span>
                        <select class="input" name="action">
                            <option>Avance de medida registrado</option>
                            <option>Medida marcada como implementada</option>
                            <option>Observación de seguimiento registrada</option>
                        </select>
                    </label>
                    <label>
                        <span class="label">Evidencia o Justificación</span>
                        <textarea class="input text-xs" name="note" rows="3" placeholder="Detalle las acciones realizadas para el cumplimiento de esta medida."></textarea>
                    </label>
                    <button class="btn-primary w-full" type="submit">Actualizar Seguimiento</button>
                </form>
            </aside>
        </div>
        @endif

    <!-- ==================== MÓDULO: ESTADÍSTICAS Y DASHBOARD ==================== -->
    @elseif($module === 'statistics')
        @if($demo['cases']->isEmpty())
            <section class="rounded-3xl border border-dashed border-slate-300 bg-white px-6 py-12 text-center shadow-xs">
                <div class="mx-auto grid size-14 place-items-center rounded-2xl bg-sky-100 text-2xl font-black text-sky-800">0</div>
                <h2 class="mt-5 text-2xl font-black text-slate-950">No hay información para mostrar</h2>
                <p class="mx-auto mt-2 max-w-2xl text-sm leading-relaxed text-slate-600">Los indicadores y gráficos se generarán automáticamente cuando existan casos registrados.</p>
                @if(auth()->user()->hasPermission('cases.create'))
                    <a href="{{ route('modules.cases.create') }}" class="btn-primary mt-6">Registrar el primer caso</a>
                @endif
            </section>
        @else
        <div class="filter-bar" data-stat-filters>
            <select class="input">
                <option>Todos los establecimientos APS Los Ángeles</option>
                @foreach($demo['establishments'] as $est)
                    <option>{{ $est }}</option>
                @endforeach
            </select>
            <select class="input">
                <option>Año 2026 completo</option>
                <option>Últimos 6 meses</option>
                <option>Últimos 90 días</option>
            </select>
            <button class="btn-primary" type="button" data-refresh-stats>Filtrar Indicadores</button>
        </div>

        @php
            $totalCases = $demo['cases']->count();
            $inProgressInv = $demo['investigations']->whereIn('status', ['En curso', 'En desarrollo'])->count();
            $implementedMeasures = $demo['measures']->where('status', 'Implementada')->count();
            $totalMeasures = $demo['measures']->count();
            $closedCases = $demo['cases']->where('status', 'Cerrado')->count();
            $closeRate = $totalCases > 0 ? round(($closedCases / $totalCases) * 100) : 0;
            $byEstablishment = $demo['cases']->groupBy('establishment')->map->count();
            $workAccidents = $demo['cases']->where('type', 'Accidente del trabajo')->count();
            $commuteAccidents = $demo['cases']->where('type', 'Accidente de trayecto')->count();
            $occupationalDiseases = $demo['cases']->where('type', 'Enfermedad profesional')->count();
        @endphp

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="metric"><span class="text-xs font-bold uppercase text-slate-500">Total Casos Registrados</span><strong class="text-3xl font-black text-slate-950" data-stat-number>{{ $totalCases }}</strong><small>Red APS Los Ángeles</small></div>
            <div class="metric"><span class="text-xs font-bold uppercase text-slate-500">Investigaciones en Curso</span><strong class="text-3xl font-black text-amber-600" data-stat-number>{{ $inProgressInv }}</strong><small>Con Árbol de Causas</small></div>
            <div class="metric"><span class="text-xs font-bold uppercase text-slate-500">Medidas Implementadas</span><strong class="text-3xl font-black text-emerald-600" data-stat-number>{{ $implementedMeasures }}</strong><small>De {{ $totalMeasures }} medidas totales</small></div>
            <div class="metric metric-danger"><span class="text-xs font-bold uppercase text-red-700">Tasa de Cierre</span><strong class="text-3xl font-black text-slate-950">{{ $closeRate }}%</strong><small class="text-slate-600">Casos regularizados</small></div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="card p-6 shadow-sm">
                <h3 class="font-black text-slate-950">Casos por Centro de Salud Comunal</h3>
                <div class="bar-chart mt-6">
                    @forelse($byEstablishment as $label => $val)
                        <div>
                            <span class="font-bold text-slate-700 text-xs">{{ $label }}</span>
                            <div><i style="width: {{ min(100, round(($val / max(1, $totalCases)) * 100)) }}%"></i></div>
                            <strong class="text-xs">{{ $val }}</strong>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400">Sin datos de distribución por establecimiento.</p>
                    @endforelse
                </div>
            </div>

            <div class="card p-6 shadow-sm">
                <h3 class="font-black text-slate-950">Distribución por Tipo de Denuncia</h3>
                <div class="donut-wrap mt-6">
                    <div class="donut"></div>
                    <ul class="text-xs space-y-2.5">
                        <li class="flex items-center gap-2"><i class="bg-sky-700 size-3 rounded-full"></i>Accidentes del Trabajo: <strong>{{ $workAccidents }} {{ $workAccidents === 1 ? 'caso' : 'casos' }}</strong></li>
                        <li class="flex items-center gap-2"><i class="bg-indigo-600 size-3 rounded-full"></i>Accidentes de Trayecto: <strong>{{ $commuteAccidents }} {{ $commuteAccidents === 1 ? 'caso' : 'casos' }}</strong></li>
                        <li class="flex items-center gap-2"><i class="bg-amber-500 size-3 rounded-full"></i>Enfermedades Profesionales: <strong>{{ $occupationalDiseases }} {{ $occupationalDiseases === 1 ? 'caso' : 'casos' }}</strong></li>
                    </ul>
                </div>
            </div>
        </div>
        @endif

    <!-- ==================== MÓDULO: REPORTES ==================== -->
    @elseif($module === 'reports')
        @if($demo['cases']->isEmpty())
            <section class="rounded-3xl border border-dashed border-slate-300 bg-white px-6 py-12 text-center shadow-xs">
                <div class="mx-auto grid size-14 place-items-center rounded-2xl bg-sky-100 text-2xl font-black text-sky-800">0</div>
                <h2 class="mt-5 text-2xl font-black text-slate-950">No hay reportes disponibles</h2>
                <p class="mx-auto mt-2 max-w-2xl text-sm leading-relaxed text-slate-600">Los informes DIAT, DIEP, Árbol de Causas y planes de acción estarán disponibles cuando existan expedientes registrados.</p>
                @if(auth()->user()->hasPermission('cases.create'))
                    <a href="{{ route('modules.cases.create') }}" class="btn-primary mt-6">Registrar el primer caso</a>
                @endif
            </section>
        @else
        @php
        $reports = [
            'Ficha Oficial DIAT / DIEP' => 'Denuncia individual estandarizada para organismo administrador (ACHS).',
            'Informe Técnico Árbol de Causas' => 'Expediente formal IMLA-DCS-F-IDA-1-24 con hechos, causas y medidas.',
            'Plan de Acción y Medidas Preventivas' => 'Consolidado de medidas de ingeniería, administrativas y EPP con responsables.',
            'Informe Ejecutivo Alta Dirección' => 'Resumen de tasa de accidentalidad, causas recurrentes y plazos vencidos.',
        ];
        @endphp
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            @foreach($reports as $name => $detail)
                <button type="button" class="report-card {{ $loop->first ? 'report-card-active' : '' }}" data-report-type="{{ $name }}">
                    <span class="text-[10px] font-black uppercase tracking-wider text-sky-700">Informe Técnico</span>
                    <strong class="text-base text-slate-950">{{ $name }}</strong>
                    <small class="text-slate-500">{{ $detail }}</small>
                </button>
            @endforeach
        </div>

        <div class="grid gap-6 lg:grid-cols-[20rem_1fr]">
            <aside class="card p-5 shadow-sm space-y-4">
                <h3 class="font-black text-slate-900">Parámetros del Informe</h3>
                <div><label class="label">Establecimiento</label><select class="input"><option>Todos los Centros APS</option>@foreach($demo['establishments'] as $e)<option>{{ $e }}</option>@endforeach</select></div>
                <div><label class="label">Fecha Inicio</label><input type="date" class="input" value="2026-01-01"></div>
                <div><label class="label">Fecha Término</label><input type="date" class="input" value="2026-10-14"></div>
                <button class="btn-primary w-full" type="button" data-report-preview>Actualizar Vista Previa</button>
                <button class="btn-secondary w-full" type="button" onclick="window.print()">Imprimir / Exportar PDF</button>
            </aside>

            <section class="card p-6 shadow-sm print-card" data-report-output>
                <div class="border-b border-slate-200 pb-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-[10px] font-black uppercase tracking-wider text-slate-400">Documento Oficial DCS Los Ángeles</span>
                            <h2 class="mt-1 text-2xl font-black text-slate-950" data-report-title>Ficha Oficial DIAT / DIEP</h2>
                        </div>
                        <span class="rounded bg-sky-100 px-3 py-1 text-xs font-bold text-sky-900">Convenio ACHS</span>
                    </div>
                </div>
                <div class="mt-6 space-y-4 text-xs">
                    <p class="text-slate-600">Este informe consolida los expedientes formalmente tramitados en los centros de salud APS de la comuna.</p>
                    <table class="data-table">
                        <thead><tr><th>Folio</th><th>Trabajador</th><th>Centro</th><th>Tipo</th><th>Estado</th></tr></thead>
                        <tbody>
                            @foreach($demo['cases'] as $c)
                                <tr>
                                    <td class="font-bold">{{ $c['id'] }}</td>
                                    <td>{{ $c['worker'] }}</td>
                                    <td>{{ $c['establishment'] }}</td>
                                    <td>{{ $c['type'] }}</td>
                                    <td><span class="status {{ $statusClass($c['status']) }}">{{ $c['status'] }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
        @endif

    <!-- ==================== MÓDULO: PERFIL ==================== -->
    @elseif($module === 'profile')
        <div class="grid gap-6 lg:grid-cols-[1fr_22rem]">
            <section class="card p-6 sm:p-8 shadow-sm">
                <h3 class="text-xl font-black text-slate-950 border-b pb-4">Antecedentes del Funcionario</h3>
                <dl class="mt-6 grid gap-6 sm:grid-cols-2 text-xs">
                    <div><dt>Nombre Completo</dt><dd class="text-sm font-bold text-slate-900">{{ auth()->user()->name }}</dd></div>
                    <div><dt>RUT</dt><dd class="text-sm font-bold text-slate-900">{{ auth()->user()->rut }}</dd></div>
                    <div><dt>Correo Institucional</dt><dd class="text-sm font-bold text-slate-900">{{ auth()->user()->email }}</dd></div>
                    <div><dt>Cargo Funcional</dt><dd class="text-sm font-bold text-slate-900">{{ auth()->user()->job_title ?: 'Funcionario de Salud' }}</dd></div>
                    <div><dt>Roles Activos en GIATEP</dt><dd class="text-sm font-bold text-sky-800">{{ auth()->user()->roles->pluck('name')->join(' · ') }}</dd></div>
                    <div><dt>Establecimientos Autorizados</dt><dd class="text-sm font-bold text-slate-900">{{ auth()->user()->establishments->pluck('name')->join(' · ') ?: 'Toda la Red Comunal APS' }}</dd></div>
                </dl>
            </section>

            <aside class="card p-6 shadow-sm space-y-4">
                <h3 class="font-black text-slate-900 border-b pb-3">Información del Perfil</h3>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Las credenciales operativas de Prevención, Jefatura y Alta Dirección son provistas automáticamente mediante la API institucional de la Dirección Comunal de Salud.
                </p>
                <div class="rounded-xl border border-sky-200 bg-sky-50 p-4 text-xs font-semibold text-sky-900">
                    Comité Paritario y Delegados de Seguridad pueden solicitar la actualización de su establecimiento comunicándose con la Unidad de Prevención de Riesgos (UPRSO).
                </div>
            </aside>
        </div>

    <!-- ==================== MÓDULOS DE ADMINISTRACIÓN ==================== -->
    @elseif(in_array($module, ['users','roles','establishments','settings']))
        @php
        $adminData = match($module) {
            'users' => \App\Models\User::with(['roles', 'establishments'])->get()->map(fn($u) => [
                $u->name,
                $u->roles->pluck('name')->join(', ') ?: 'Sin rol',
                $u->establishments->pluck('name')->join(', ') ?: 'Red Comunal APS',
                $u->status === 'active' ? 'Activo' : ($u->status ?? 'Activo'),
            ]),
            'roles' => \App\Models\Role::withCount('users')->get()->map(fn($r) => [
                $r->name,
                $r->slug,
                $r->users_count . ' usuario(s) asignado(s)',
                'Activo',
            ]),
            'establishments' => \App\Models\Establishment::all()->map(fn($e) => [
                $e->name,
                $e->type,
                $e->address ?? 'Comuna de Los Ángeles',
                $e->is_active ? 'Activo' : 'Inactivo',
            ]),
            'settings' => collect([
                ['Tipos de Denuncia', '2 formularios normativos', 'DIAT (Accidentes Ley 16.744) y DIEP (Enfermedades profesionales)', 'Activo'],
                ['Matriz de Factores Causales', '222 códigos normativos', 'Clasificación oficial ISP estandarizada', 'Activo'],
                ['Jerarquía de Control de Riesgos', '5 niveles preventivos', 'Eliminación, Sustitución, Ingeniería, Administrativa, EPP', 'Activo'],
            ]),
            default => collect(),
        };
        @endphp

        <div class="flex items-center justify-between border-b pb-4">
            <div>
                <h3 class="text-xl font-black text-slate-900">{{ $titles[$module][0] }}</h3>
                <p class="text-xs text-slate-500">Catálogo oficial de configuración de la plataforma GIATEP.</p>
            </div>
            <button class="btn-primary" type="button" data-open-demo-modal>+ Agregar Registro</button>
        </div>

        <div class="card table-card shadow-sm">
            <table class="data-table">
                <thead><tr><th>Nombre / Registro</th><th>Tipo / Detalle</th><th>Alcance Institucional</th><th>Estado</th><th>Acciones</th></tr></thead>
                <tbody>
                    @forelse($adminData as $row)
                        <tr>
                            <td class="font-bold text-slate-900">{{ $row[0] }}</td>
                            <td><span class="rounded bg-slate-100 px-2 py-0.5 text-xs font-bold">{{ $row[1] }}</span></td>
                            <td>{{ $row[2] }}</td>
                            <td><span class="status status-approved">{{ $row[3] }}</span></td>
                            <td>
                                <div class="flex flex-wrap items-center gap-3">
                                    <button class="text-link" type="button" data-open-demo-modal data-record="{{ $row[0] }}">Editar</button>
                                    @if($module === 'settings' && $row[0] === 'Matriz de Factores Causales')
                                        <a class="text-link" href="{{ route('modules.cause-factors.index') }}">Ver matriz</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="p-8 text-center text-slate-500">No hay registros configurados en este catálogo.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif
</div>
</x-layouts.app>
