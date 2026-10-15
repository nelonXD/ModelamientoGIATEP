<?php

namespace App\Support;

use App\Models\OccupationalCase;
use Illuminate\Support\Collection;

class DemoWorkspace
{
    public const SESSION_KEY = 'giatep_demo_changes';

    /** @return array<string, mixed> */
    public function data(): array
    {
        $storedCases = OccupationalCase::query()
            ->with(['establishment', 'investigation.owner', 'investigation.measures', 'investigation.facts', 'investigation.causalNodes', 'evidences'])
            ->orderByDesc('event_date')
            ->get();

        if ($storedCases->isNotEmpty()) {
            $cases = $storedCases->map(fn (OccupationalCase $case): array => [
                'id' => $case->folio,
                'type' => $case->case_type,
                'worker' => $case->worker_name,
                'rut' => $case->worker_rut,
                'job_title' => $case->job_title ?? 'Funcionario/a de salud',
                'phone' => $case->worker_phone ?? '+56 9 8000 0000',
                'establishment' => $case->establishment?->name ?? $case->workplace,
                'workplace' => $case->workplace ?? $case->establishment?->name,
                'employer_name' => $case->employer_name ?? 'Dirección Comunal de Salud de Los Ángeles',
                'employer_rut' => $case->employer_rut ?? '69.170.102-6',
                'employer_address' => $case->employer_address ?? 'Calle Valdivia #150, Los Ángeles',
                'administrator' => $case->administrator ?? 'ACHS',
                'economic_activity' => $case->economic_activity ?? 'Servicios de Salud Pública Municipal (APS)',
                'date' => $case->event_date->toDateString(),
                'time' => $case->event_time ? substr((string) $case->event_time, 0, 5) : '09:00',
                'location' => $case->event_location ?? 'Dependencias del establecimiento',
                'status' => $case->status,
                'qualification' => $case->status === 'Cerrado' ? 'Laboral (Cerrado)' : ($case->case_type === 'Enfermedad profesional' ? 'En estudio ACHS' : 'Pendiente'),
                'injury' => $case->details['affected_body_part'] ?? $case->details['injury_type'] ?? 'Por confirmar',
                'body_part' => $case->details['affected_body_part'] ?? 'Por confirmar',
                'story' => $case->narrative,
                'witnesses' => $case->witnesses ?? 'Sin testigos registrados.',
                'investigation_id' => $case->investigation?->folio,
                'evidence_count' => $case->evidences->count(),
                'form_type' => $case->case_type === 'Enfermedad profesional' ? 'DIEP' : 'DIAT',
            ]);

            $investigations = $storedCases->pluck('investigation')->filter()->map(fn ($investigation): array => [
                'id' => $investigation->folio,
                'case_id' => $investigation->occupationalCase->folio,
                'case_type' => $investigation->occupationalCase->case_type,
                'worker' => $investigation->occupationalCase->worker_name,
                'establishment' => $investigation->occupationalCase->establishment?->name ?? 'Establecimiento de salud',
                'status' => $investigation->status,
                'progress' => match ($investigation->status) {
                    'Concluida', 'Cerrado' => 100,
                    'Con observaciones' => 85,
                    'En desarrollo' => 65,
                    default => 30,
                },
                'owner' => $investigation->owner->name,
                'updated' => $investigation->updated_at->toDateString(),
                'kind' => 'Árbol de causas',
                'final_story' => $investigation->final_story,
                'has_facts' => $investigation->facts->isNotEmpty() || $investigation->causalNodes->isNotEmpty(),
                'pending' => match ($investigation->status) {
                    'Concluida', 'Cerrado' => 'Sin pendientes (Expediente regularizado)',
                    'Con observaciones' => 'Responder observaciones de Jefatura / Alta Dirección',
                    'En desarrollo' => 'Completar árbol de causas y validar medidas',
                    default => 'Recopilar entrevistas y evidencias',
                },
            ])->values();

            $measures = $storedCases->pluck('investigation')->filter()->flatMap(fn ($investigation) => $investigation->measures->map(fn ($measure): array => [
                'id' => 'MED-'.str_pad((string) $measure->id, 3, '0', STR_PAD_LEFT),
                'case_id' => $investigation->occupationalCase->folio,
                'investigation_id' => $investigation->folio,
                'cause' => 'Causa técnica asociada a '.$investigation->folio,
                'description' => $measure->description,
                'category' => $measure->category ?? 'Administrativa',
                'owner' => $measure->responsible,
                'due' => $measure->due_date?->toDateString() ?? 'Sin plazo fijado',
                'status' => $measure->status,
                'progress' => $measure->progress,
            ]))->values();

            $observations = collect([
                [
                    'id' => 'OBS-001',
                    'case_id' => 'GIATEP-2026-003',
                    'investigation_id' => 'INV-2026-003',
                    'section' => 'Investigación / Hechos',
                    'description' => 'Verificar si el protocolo de descarte cortopunzante en el Box 3 se encontraba visible y si existía pinza de transferencia al momento del procedimiento.',
                    'author' => 'Niza Gallo Ibáñez',
                    'role' => 'Jefatura Directa',
                    'date' => '2026-09-10',
                    'status' => 'Pendiente',
                    'responses' => 0,
                ],
                [
                    'id' => 'OBS-002',
                    'case_id' => 'GIATEP-2026-003',
                    'investigation_id' => 'INV-2026-003',
                    'section' => 'Medidas de control',
                    'description' => 'Acelerar la dotación de pinzas de transferencia seguras para toda la red de odontología comunal. Indicar impacto presupuestario.',
                    'author' => 'Dirección Comunal de Salud',
                    'role' => 'Alta Dirección',
                    'date' => '2026-09-12',
                    'status' => 'Pendiente',
                    'responses' => 0,
                ],
                [
                    'id' => 'OBS-003',
                    'case_id' => 'GIATEP-2026-001',
                    'investigation_id' => 'INV-2026-001',
                    'section' => 'Medidas de prevención',
                    'description' => 'Asegurar que la señalización de piso húmedo incluya conos de alta visibilidad con pictograma normalizado y texto en español.',
                    'author' => 'Prevencionista de Riesgos (UPRSO)',
                    'role' => 'Prevencionista',
                    'date' => '2026-10-04',
                    'status' => 'Respondida',
                    'responses' => 1,
                ],
                [
                    'id' => 'OBS-004',
                    'case_id' => 'GIATEP-2026-004',
                    'investigation_id' => 'INV-2026-004',
                    'section' => 'Plan de acción',
                    'description' => 'Coordinar con la jefatura de Sector Amarillo el calendario de turnos para no afectar la atención mientras se realiza la capacitación ergonómica.',
                    'author' => 'Jefatura de Sector',
                    'role' => 'Jefatura Directa',
                    'date' => '2026-09-18',
                    'status' => 'Resuelta',
                    'responses' => 2,
                ],
            ]);

            $establishmentsList = [
                'CESFAM Nororiente',
                'CESFAM Los Aromos',
                'CESFAM Nuevo Horizonte',
                'CESFAM 2 de Septiembre',
                'SAR Central',
                'Dirección Comunal de Salud',
            ];

            return compact('cases', 'investigations', 'measures', 'observations') + [
                'establishments' => $establishmentsList,
                'activity' => [
                    ['time' => 'Hoy, 09:15', 'text' => 'GIATEP-2026-001 registró avance en medidas de señalización preventiva.'],
                    ['time' => 'Ayer, 16:30', 'text' => 'OBS-002 recibida desde Alta Dirección en caso Claudia Rubilar (GIATEP-2026-003).'],
                    ['time' => 'Ayer, 11:00', 'text' => 'GIATEP-2026-002 (Accidente de trayecto SAR Central) registrado con DIAT.'],
                    ['time' => '12 oct, 14:20', 'text' => 'GIATEP-2026-006 concluido y cerrado exitosamente por Comité Paritario.'],
                    ['time' => '10 oct, 10:45', 'text' => 'GIATEP-2026-005 en estudio DIEP por organismo administrador (ACHS).'],
                ],
            ];
        }

        $cases = collect([
            ['id' => 'GIATEP-2026-001', 'type' => 'Accidente de trabajo', 'worker' => 'Daniela Araya Soto', 'rut' => '17.456.321-8', 'job_title' => 'Técnica en enfermería (TENS)', 'phone' => '+56 9 8123 4567', 'establishment' => 'CESFAM Nororiente', 'workplace' => 'Bodega clínica', 'employer_name' => 'Dirección Comunal de Salud de Los Ángeles', 'employer_rut' => '69.170.102-6', 'employer_address' => 'Calle Valdivia #150, Los Ángeles', 'administrator' => 'ACHS', 'economic_activity' => 'Salud Pública APS', 'date' => '2026-10-02', 'time' => '09:15', 'location' => 'Pasillo de acceso a bodega', 'status' => 'En investigación', 'qualification' => 'Pendiente', 'injury' => 'Rodilla derecha', 'body_part' => 'Rodilla derecha', 'story' => 'Durante el traslado de insumos, la trabajadora resbaló sobre una zona húmeda del pasillo y cayó apoyando la rodilla derecha.', 'witnesses' => 'Marcelo Fuentes Rojas', 'investigation_id' => 'INV-2026-001', 'evidence_count' => 1, 'form_type' => 'DIAT'],
            ['id' => 'GIATEP-2026-002', 'type' => 'Accidente de trayecto', 'worker' => 'Luis Morales Vera', 'rut' => '18.765.432-1', 'job_title' => 'Administrativo de Admisión', 'phone' => '+56 9 9345 6789', 'establishment' => 'SAR Central', 'workplace' => 'SAR Central', 'employer_name' => 'Dirección Comunal de Salud de Los Ángeles', 'employer_rut' => '69.170.102-6', 'employer_address' => 'Calle Valdivia #150, Los Ángeles', 'administrator' => 'ACHS', 'economic_activity' => 'Salud Pública APS', 'date' => '2026-10-08', 'time' => '08:05', 'location' => 'Vía pública - trayecto a SAR', 'status' => 'Registrado', 'qualification' => 'Pendiente', 'injury' => 'Tobillo izquierdo', 'body_part' => 'Tobillo izquierdo', 'story' => 'El trabajador informó una caída durante su trayecto habitual hacia el establecimiento.', 'witnesses' => 'Sin testigos', 'investigation_id' => null, 'evidence_count' => 0, 'form_type' => 'DIAT'],
            ['id' => 'GIATEP-2026-003', 'type' => 'Accidente de trabajo', 'worker' => 'Claudia Nicole Rubilar Heredia', 'rut' => '17.592.581-3', 'job_title' => 'Odontóloga', 'phone' => '+56 9 7654 3210', 'establishment' => 'CESFAM Nororiente', 'workplace' => 'Box Dental 3', 'employer_name' => 'Dirección Comunal de Salud de Los Ángeles', 'employer_rut' => '69.170.102-6', 'employer_address' => 'Calle Valdivia #150, Los Ángeles', 'administrator' => 'ACHS', 'economic_activity' => 'Salud Pública APS', 'date' => '2026-09-09', 'time' => '13:15', 'location' => 'Box Dental 3', 'status' => 'Con observaciones', 'qualification' => 'Laboral', 'injury' => 'Dedo índice izquierdo', 'body_part' => 'Dedo índice izquierdo', 'story' => 'Contacto accidental con instrumental cortopunzante durante la clasificación post-atención.', 'witnesses' => 'Niza Gallo Ibáñez (Jefatura Directa)', 'investigation_id' => 'INV-2026-003', 'evidence_count' => 2, 'form_type' => 'DIAT'],
            ['id' => 'GIATEP-2026-004', 'type' => 'Accidente de trabajo', 'worker' => 'Marcela Illanes Castillo', 'rut' => '15.342.119-4', 'job_title' => 'Técnica en enfermería (TENS)', 'phone' => '+56 9 6543 2109', 'establishment' => 'CESFAM Los Aromos', 'workplace' => 'Sector Amarillo', 'employer_name' => 'Dirección Comunal de Salud de Los Ángeles', 'employer_rut' => '69.170.102-6', 'employer_address' => 'Calle Valdivia #150, Los Ángeles', 'administrator' => 'ACHS', 'economic_activity' => 'Salud Pública APS', 'date' => '2026-09-15', 'time' => '11:30', 'location' => 'Sala de procedimientos', 'status' => 'Seguimiento de medidas', 'qualification' => 'Laboral', 'injury' => 'Zona lumbar', 'body_part' => 'Zona lumbar', 'story' => 'Sobreesfuerzo físico en transferencia manual de usuario hacia silla de ruedas.', 'witnesses' => 'Compañera TENS de turno', 'investigation_id' => 'INV-2026-004', 'evidence_count' => 1, 'form_type' => 'DIAT'],
            ['id' => 'GIATEP-2026-005', 'type' => 'Enfermedad profesional', 'worker' => 'Fernanda López Mena', 'rut' => '16.890.123-5', 'job_title' => 'Auxiliar de Farmacia', 'phone' => '+56 9 5432 1098', 'establishment' => 'CESFAM Nuevo Horizonte', 'workplace' => 'Central de Esterilización', 'employer_name' => 'Dirección Comunal de Salud de Los Ángeles', 'employer_rut' => '69.170.102-6', 'employer_address' => 'Calle Valdivia #150, Los Ángeles', 'administrator' => 'ACHS', 'economic_activity' => 'Salud Pública APS', 'date' => '2026-09-20', 'time' => '10:00', 'location' => 'Central de Esterilización', 'status' => 'En investigación', 'qualification' => 'En estudio ACHS', 'injury' => 'Manos y muñecas', 'body_part' => 'Manos y muñecas', 'story' => 'Dermatitis de contacto por manipulación continua de desinfectantes de alto nivel.', 'witnesses' => 'Jefatura de Farmacia', 'investigation_id' => 'INV-2026-005', 'evidence_count' => 1, 'form_type' => 'DIEP'],
            ['id' => 'GIATEP-2026-006', 'type' => 'Accidente de trabajo', 'worker' => 'Carlos Vega Sepúlveda', 'rut' => '12.876.543-9', 'job_title' => 'Conductor de Ambulancia', 'phone' => '+56 9 4321 0987', 'establishment' => 'SAR Central', 'workplace' => 'Base Ambulancias', 'employer_name' => 'Dirección Comunal de Salud de Los Ángeles', 'employer_rut' => '69.170.102-6', 'employer_address' => 'Calle Valdivia #150, Los Ángeles', 'administrator' => 'ACHS', 'economic_activity' => 'Salud Pública APS', 'date' => '2026-08-10', 'time' => '16:40', 'location' => 'Patio de maniobras vehiculares', 'status' => 'Cerrado', 'qualification' => 'Laboral (Cerrado)', 'injury' => 'Tobillo derecho', 'body_part' => 'Tobillo derecho', 'story' => 'Pisada en bache de pavimento al descender del móvil.', 'witnesses' => 'Paramédico acompañante', 'investigation_id' => 'INV-2026-006', 'evidence_count' => 1, 'form_type' => 'DIAT'],
            ['id' => 'CAS-2026-017', 'type' => 'Accidente de trabajo', 'worker' => 'Diego Morales Vera', 'rut' => '22.222.222-2', 'job_title' => 'TENS', 'phone' => '+56 9 8000 0000', 'establishment' => 'SAR Central', 'workplace' => 'SAR Central', 'employer_name' => 'Dirección Comunal de Salud de Los Ángeles', 'employer_rut' => '69.170.102-6', 'employer_address' => 'Calle Valdivia #150, Los Ángeles', 'administrator' => 'ACHS', 'economic_activity' => 'Salud Pública APS', 'date' => '2026-09-22', 'time' => '10:30', 'location' => 'SAR Central', 'status' => 'En investigación', 'qualification' => 'Laboral', 'injury' => 'Contusión mano izquierda', 'body_part' => 'Mano izquierda', 'story' => 'Golpe con puerta de acceso mientras trasladaba equipamiento clínico.', 'witnesses' => 'Marcelo Fuentes Rojas', 'investigation_id' => 'INV-2026-011', 'evidence_count' => 1, 'form_type' => 'DIAT'],
            ['id' => 'GIATEP-2026-007', 'type' => 'Accidente de trabajo', 'worker' => 'Camila Paz Valenzuela Morales', 'rut' => '19.345.678-2', 'job_title' => 'Técnica en enfermería (TENS) de Vacunatorio', 'phone' => '+56 9 7890 1234', 'establishment' => 'CESFAM 2 de Septiembre', 'workplace' => 'Box de Vacunatorio', 'employer_name' => 'Dirección Comunal de Salud de Los Ángeles', 'employer_rut' => '69.170.102-6', 'employer_address' => 'Calle Valdivia #150, Los Ángeles', 'administrator' => 'ACHS', 'economic_activity' => 'Salud Pública APS', 'date' => '2026-10-14', 'time' => '10:15', 'location' => 'Box de Inmunizaciones / Vacunatorio, Sector A', 'status' => 'Registrado', 'qualification' => 'Pendiente', 'injury' => 'Dedo índice derecho', 'body_part' => 'Dedo índice derecho', 'story' => 'Durante la jornada de vacunación escolar en el CESFAM 2 de Septiembre, la funcionaria procedió al descarte de una jeringa en el contenedor de residuos cortopunzantes. Al encontrarse el receptáculo con sobrellenado (más de 3/4 de su capacidad reglamentaria), sufrió una punción accidental en la falange distal del dedo índice de su mano derecha con una aguja expuesta en la parte superior.', 'witnesses' => 'Romina Cárdenas Silva (Enfermera Coordinadora de Vacunatorio)', 'investigation_id' => null, 'evidence_count' => 0, 'form_type' => 'DIAT'],
        ]);

        $investigations = collect([
            ['id' => 'INV-2026-001', 'case_id' => 'GIATEP-2026-001', 'case_type' => 'Accidente de trabajo', 'worker' => 'Daniela Araya Soto', 'establishment' => 'CESFAM Nororiente', 'status' => 'En desarrollo', 'progress' => 65, 'owner' => 'Paula Contreras', 'updated' => '2026-10-03', 'kind' => 'Árbol de causas', 'final_story' => 'A las 09:15, durante el traslado de insumos hacia la bodega clínica, la trabajadora ingresó al pasillo de acceso. El piso se encontraba húmedo tras el lavado matinal y sin señalización preventiva. Al pisar la zona perdió estabilidad y cayó apoyando su rodilla derecha contra el suelo.', 'has_facts' => true, 'pending' => 'Completar árbol de causas y validar medidas'],
            ['id' => 'INV-2026-003', 'case_id' => 'GIATEP-2026-003', 'case_type' => 'Accidente de trabajo', 'worker' => 'Claudia Nicole Rubilar Heredia', 'establishment' => 'CESFAM Nororiente', 'status' => 'Con observaciones', 'progress' => 85, 'owner' => 'Paula Contreras', 'updated' => '2026-10-01', 'kind' => 'Árbol de causas', 'final_story' => 'A las 13:15 en Box 3, al finalizar procedimiento de profilaxis, la profesional procedió a clasificar el instrumental sucio. Durante el descarte, la sonda tuvo contacto accidental con guante de látex provocando punción superficial en dedo índice.', 'has_facts' => true, 'pending' => 'Responder observaciones de Jefatura y Alta Dirección'],
            ['id' => 'INV-2026-004', 'case_id' => 'GIATEP-2026-004', 'case_type' => 'Accidente de trabajo', 'worker' => 'Marcela Illanes Castillo', 'establishment' => 'CESFAM Los Aromos', 'status' => 'Concluida', 'progress' => 100, 'owner' => 'Andrés Muñoz', 'updated' => '2026-09-18', 'kind' => 'Árbol de causas', 'final_story' => 'Durante la transferencia asistida de un usuario de 85 kg, la técnica debió realizar un giro con carga sin asistencia mecánica, superando el límite ergonómico de manejo manual de pacientes.', 'has_facts' => true, 'pending' => 'Seguimiento de 2 medidas activas'],
            ['id' => 'INV-2026-005', 'case_id' => 'GIATEP-2026-005', 'case_type' => 'Enfermedad profesional', 'worker' => 'Fernanda López Mena', 'establishment' => 'CESFAM Nuevo Horizonte', 'status' => 'En desarrollo', 'progress' => 60, 'owner' => 'Paula Contreras', 'updated' => '2026-09-25', 'kind' => 'Análisis de exposición DIEP', 'final_story' => 'Evaluación de puesto de trabajo en Central de Esterilización. Se constata manipulación diaria de soluciones desinfectantes concentradas con guantes estándar sin resistencia química específica requerida por ficha de seguridad.', 'has_facts' => true, 'pending' => 'Esperar calificación definitiva de ACHS'],
            ['id' => 'INV-2026-006', 'case_id' => 'GIATEP-2026-006', 'case_type' => 'Accidente de trabajo', 'worker' => 'Carlos Vega Sepúlveda', 'establishment' => 'SAR Central', 'status' => 'Concluida', 'progress' => 100, 'owner' => 'Andrés Muñoz', 'updated' => '2026-08-30', 'kind' => 'Árbol de causas', 'final_story' => 'Caso regularizado. Bache reparado mediante pavimentación en frío del patio de maniobras. Conductor recuperado y reintegrado a funciones con alta médica ACHS.', 'has_facts' => true, 'pending' => 'Sin pendientes (Caso cerrado por CPHS)'],
            ['id' => 'INV-2026-011', 'case_id' => 'CAS-2026-017', 'case_type' => 'Accidente de trabajo', 'worker' => 'Diego Morales Vera', 'establishment' => 'SAR Central', 'status' => 'En desarrollo', 'progress' => 54, 'owner' => 'Paula Contreras', 'updated' => '2026-10-02', 'kind' => 'Árbol de causas', 'final_story' => 'El trabajador trasladaba equipamiento clínico hacia el box de reanimación del SAR. Al empujar la puerta batiente de vaivén, esta rebotó inesperadamente aprisionando su mano izquierda contra el marco metálico.', 'has_facts' => false, 'pending' => 'Completar hechos y participantes'],
        ]);

        $measures = collect([
            ['id' => 'MED-001', 'case_id' => 'GIATEP-2026-001', 'investigation_id' => 'INV-2026-001', 'cause' => 'Piso húmedo por lavado sin señalizar', 'description' => 'Instalar señalización inmediata tipo cono/caballete ante labores de limpieza o derrames.', 'category' => 'Administrativa', 'owner' => 'Jefatura de Servicios Generales', 'due' => '2026-10-20', 'status' => 'En ejecución', 'progress' => 60],
            ['id' => 'MED-002', 'case_id' => 'GIATEP-2026-001', 'investigation_id' => 'INV-2026-001', 'cause' => 'Falta de protocolo de secado rápido', 'description' => 'Revisar y actualizar el procedimiento de secado rápido y tránsito seguro de pasillos.', 'category' => 'Administrativa', 'owner' => 'Unidad de Prevención de Riesgos', 'due' => '2026-10-25', 'status' => 'Pendiente', 'progress' => 0],
            ['id' => 'MED-003', 'case_id' => 'GIATEP-2026-003', 'investigation_id' => 'INV-2026-003', 'cause' => 'Manipulación directa de instrumental crítico', 'description' => 'Incorporar pinzas de transferencia seguras en todos los boxes dentales del establecimiento.', 'category' => 'Ingeniería', 'owner' => 'Jefatura de Odontología / Abastecimiento', 'due' => '2026-10-30', 'status' => 'En ejecución', 'progress' => 45],
            ['id' => 'MED-004', 'case_id' => 'GIATEP-2026-003', 'investigation_id' => 'INV-2026-003', 'cause' => 'Disposición de bandejas en mesón clínico', 'description' => 'Capacitar al estamento odontológico en protocolo de descarte cortopunzante.', 'category' => 'Administrativa', 'owner' => 'Prevención de Riesgos', 'due' => '2026-11-05', 'status' => 'Pendiente', 'progress' => 10],
            ['id' => 'MED-005', 'case_id' => 'GIATEP-2026-004', 'investigation_id' => 'INV-2026-004', 'cause' => 'Transferencia manual de paciente sin ayuda mecánica', 'description' => 'Adquisición de cinturones de transferencia ergonómica para salas de procedimientos.', 'category' => 'Ingeniería', 'owner' => 'Dirección Comunal / Abastecimiento', 'due' => '2026-10-15', 'status' => 'En ejecución', 'progress' => 80],
            ['id' => 'MED-006', 'case_id' => 'GIATEP-2026-004', 'investigation_id' => 'INV-2026-004', 'cause' => 'Técnica de giro con carga no estandarizada', 'description' => 'Taller de ergonomía y técnica segura de movilización de pacientes (Protocolo MMC).', 'category' => 'Administrativa', 'owner' => 'Comité Paritario y Prevencionista', 'due' => '2026-10-22', 'status' => 'Implementada', 'progress' => 100],
            ['id' => 'MED-007', 'case_id' => 'GIATEP-2026-005', 'investigation_id' => 'INV-2026-005', 'cause' => 'Exposición a detergentes enzimáticos sin EPP adecuado', 'description' => 'Suministro de guantes de nitrilo de caña larga certificados contra agentes químicos.', 'category' => 'Equipos de protección personal', 'owner' => 'Prevención de Riesgos / Bodega', 'due' => '2026-10-18', 'status' => 'En ejecución', 'progress' => 70],
            ['id' => 'MED-008', 'case_id' => 'GIATEP-2026-006', 'investigation_id' => 'INV-2026-006', 'cause' => 'Bache en pavimento de estacionamiento', 'description' => 'Reparación de desniveles y baches en el patio de maniobras de ambulancias.', 'category' => 'Ingeniería', 'owner' => 'Mantención Municipal', 'due' => '2026-08-30', 'status' => 'Implementada', 'progress' => 100],
        ]);

        $observations = collect([
            [
                'id' => 'OBS-001',
                'case_id' => 'GIATEP-2026-003',
                'investigation_id' => 'INV-2026-003',
                'section' => 'Investigación / Hechos',
                'description' => 'Verificar si el protocolo de descarte cortopunzante en el Box 3 se encontraba visible y si existía pinza de transferencia al momento del procedimiento.',
                'author' => 'Niza Gallo Ibáñez',
                'role' => 'Jefatura Directa',
                'date' => '2026-09-10',
                'status' => 'Pendiente',
                'responses' => 0,
            ],
            [
                'id' => 'OBS-002',
                'case_id' => 'GIATEP-2026-003',
                'investigation_id' => 'INV-2026-003',
                'section' => 'Medidas de control',
                'description' => 'Acelerar la dotación de pinzas de transferencia seguras para toda la red de odontología comunal. Indicar impacto presupuestario.',
                'author' => 'Dirección Comunal de Salud',
                'role' => 'Alta Dirección',
                'date' => '2026-09-12',
                'status' => 'Pendiente',
                'responses' => 0,
            ],
            [
                'id' => 'OBS-003',
                'case_id' => 'GIATEP-2026-001',
                'investigation_id' => 'INV-2026-001',
                'section' => 'Medidas de prevención',
                'description' => 'Asegurar que la señalización de piso húmedo incluya conos de alta visibilidad con pictograma normalizado y texto en español.',
                'author' => 'Prevencionista de Riesgos (UPRSO)',
                'role' => 'Prevencionista',
                'date' => '2026-10-04',
                'status' => 'Respondida',
                'responses' => 1,
            ],
            [
                'id' => 'OBS-004',
                'case_id' => 'GIATEP-2026-004',
                'investigation_id' => 'INV-2026-004',
                'section' => 'Plan de acción',
                'description' => 'Coordinar con la jefatura de Sector Amarillo el calendario de turnos para no afectar la atención mientras se realiza la capacitación ergonómica.',
                'author' => 'Jefatura de Sector',
                'role' => 'Jefatura Directa',
                'date' => '2026-09-18',
                'status' => 'Resuelta',
                'responses' => 2,
            ],
        ]);

        return compact('cases', 'investigations', 'measures', 'observations') + [
            'establishments' => [
                'CESFAM Nororiente',
                'CESFAM Los Aromos',
                'CESFAM Nuevo Horizonte',
                'CESFAM 2 de Septiembre',
                'SAR Central',
                'Dirección Comunal de Salud',
            ],
            'activity' => [
                ['time' => 'Hoy, 09:15', 'text' => 'GIATEP-2026-001 registró avance en medidas de señalización preventiva.'],
                ['time' => 'Ayer, 16:30', 'text' => 'OBS-002 recibida desde Alta Dirección en caso Claudia Rubilar (GIATEP-2026-003).'],
                ['time' => 'Ayer, 11:00', 'text' => 'GIATEP-2026-002 (Accidente de trayecto SAR Central) registrado con DIAT.'],
                ['time' => '12 oct, 14:20', 'text' => 'GIATEP-2026-006 concluido y cerrado exitosamente por Comité Paritario.'],
                ['time' => '10 oct, 10:45', 'text' => 'GIATEP-2026-005 en estudio DIEP por organismo administrador (ACHS).'],
            ],
        ];
    }

    /** @return array<string, int> */
    public function metrics(): array
    {
        $data = $this->data();

        return [
            'cases' => $data['cases']->count(),
            'investigations_pending' => $data['investigations']->whereNotIn('status', ['Concluida', 'Cerrado'])->count(),
            'with_observations' => $data['investigations']->where('status', 'Con observaciones')->count(),
            'measures_due_soon' => $data['measures']->whereIn('status', ['Pendiente', 'En ejecución'])->count(),
            'measures_overdue' => $data['measures']->where('status', 'Vencida')->count(),
        ];
    }

    /** @return array<string, mixed>|null */
    public function find(string $collection, string $id): ?array
    {
        $items = $this->data()[$collection] ?? collect();

        $found = $items instanceof Collection ? $items->firstWhere('id', $id) : null;
        if ($found) {
            return $found;
        }

        return null;
    }

    /** @return array<string, string> */
    public function permissions(): array
    {
        return [
            'cases' => 'cases.read', 'investigations' => 'investigations.read', 'reviews' => 'reviews.read',
            'observations' => 'observations.read', 'measures' => 'measures.read', 'statistics' => 'statistics.read',
            'reports' => 'reports.export', 'profile' => 'profile.read', 'users' => 'users.manage',
            'roles' => 'roles.manage', 'establishments' => 'establishments.manage', 'settings' => 'settings.manage',
        ];
    }
}
