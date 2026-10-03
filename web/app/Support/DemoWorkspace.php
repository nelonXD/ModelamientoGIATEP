<?php

namespace App\Support;

use Illuminate\Support\Collection;

class DemoWorkspace
{
    public const SESSION_KEY = 'giatep_demo_changes';

    /** @return array<string, mixed> */
    public function data(): array
    {
        $cases = collect([
            ['id' => 'CAS-2026-018', 'type' => 'Accidente del trabajo', 'worker' => 'Camila Soto Rojas', 'rut' => '11.111.111-1', 'establishment' => 'CESFAM Los Aromos', 'date' => '2026-09-28', 'status' => 'Registrado', 'qualification' => 'Pendiente', 'injury' => 'Esguince de tobillo', 'body_part' => 'Tobillo derecho', 'story' => 'Durante el traslado de insumos, la trabajadora perdió estabilidad en una superficie húmeda.', 'investigation_id' => null],
            ['id' => 'CAS-2026-017', 'type' => 'Accidente del trabajo', 'worker' => 'Diego Morales Vera', 'rut' => '22.222.222-2', 'establishment' => 'SAR Central', 'date' => '2026-09-22', 'status' => 'En investigación', 'qualification' => 'Laboral', 'injury' => 'Contusión', 'body_part' => 'Mano izquierda', 'story' => 'Golpe con puerta de acceso mientras trasladaba equipamiento clínico.', 'investigation_id' => 'INV-2026-011'],
            ['id' => 'CAS-2026-014', 'type' => 'Accidente del trabajo', 'worker' => 'Valentina Pérez Díaz', 'rut' => '33.333.333-3', 'establishment' => 'CESFAM Los Aromos', 'date' => '2026-09-12', 'status' => 'Con observaciones', 'qualification' => 'Laboral', 'injury' => 'Herida superficial', 'body_part' => 'Antebrazo', 'story' => 'Contacto con borde metálico expuesto en bodega.', 'investigation_id' => 'INV-2026-009'],
            ['id' => 'CAS-2026-009', 'type' => 'Accidente del trabajo', 'worker' => 'Tomás Silva Reyes', 'rut' => '44.444.444-4', 'establishment' => 'CECOSF Norte', 'date' => '2026-08-18', 'status' => 'Seguimiento de medidas', 'qualification' => 'Laboral', 'injury' => 'Lumbalgia', 'body_part' => 'Zona lumbar', 'story' => 'Sobreesfuerzo durante movilización manual de cajas.', 'investigation_id' => 'INV-2026-004'],
            ['id' => 'CAS-2026-005', 'type' => 'Enfermedad profesional', 'worker' => 'Fernanda López Mena', 'rut' => '55.555.555-5', 'establishment' => 'SAR Central', 'date' => '2026-07-06', 'status' => 'En seguimiento', 'qualification' => 'En estudio', 'injury' => 'Dermatitis de contacto', 'body_part' => 'Manos', 'story' => 'Antecedentes compatibles con exposición reiterada a agentes irritantes.', 'investigation_id' => 'INV-2026-002'],
            ['id' => 'CAS-2026-001', 'type' => 'Accidente del trabajo', 'worker' => 'Martín Rojas Peña', 'rut' => '66.666.666-6', 'establishment' => 'Posta Rural El Molino', 'date' => '2026-05-14', 'status' => 'Cerrado', 'qualification' => 'Laboral', 'injury' => 'Corte menor', 'body_part' => 'Índice derecho', 'story' => 'Corte al manipular embalaje de insumos.', 'investigation_id' => 'INV-2026-001'],
        ]);

        $investigations = collect([
            ['id' => 'INV-2026-011', 'case_id' => 'CAS-2026-017', 'status' => 'Borrador', 'progress' => 54, 'owner' => 'Paula Contreras', 'updated' => '2026-10-02', 'kind' => 'Árbol de causas', 'pending' => 'Completar hechos y participantes'],
            ['id' => 'INV-2026-009', 'case_id' => 'CAS-2026-014', 'status' => 'Con observaciones', 'progress' => 82, 'owner' => 'Paula Contreras', 'updated' => '2026-10-01', 'kind' => 'Árbol de causas', 'pending' => 'Responder 2 observaciones'],
            ['id' => 'INV-2026-004', 'case_id' => 'CAS-2026-009', 'status' => 'Concluida', 'progress' => 100, 'owner' => 'Andrés Muñoz', 'updated' => '2026-09-15', 'kind' => 'Árbol de causas', 'pending' => 'Seguimiento de 2 medidas'],
            ['id' => 'INV-2026-002', 'case_id' => 'CAS-2026-005', 'status' => 'En seguimiento', 'progress' => 70, 'owner' => 'Paula Contreras', 'updated' => '2026-09-29', 'kind' => 'Análisis de exposición', 'pending' => 'Esperar calificación del organismo'],
            ['id' => 'INV-2026-001', 'case_id' => 'CAS-2026-001', 'status' => 'Concluida', 'progress' => 100, 'owner' => 'Andrés Muñoz', 'updated' => '2026-06-03', 'kind' => 'Árbol de causas', 'pending' => 'Sin pendientes'],
        ]);

        $measures = collect([
            ['id' => 'MED-041', 'case_id' => 'CAS-2026-009', 'investigation_id' => 'INV-2026-004', 'cause' => 'Manipulación manual sin ayuda mecánica', 'description' => 'Incorporar carro de transporte para bodega', 'category' => 'Ingeniería', 'owner' => 'Jefatura CECOSF Norte', 'due' => '2026-10-07', 'status' => 'En ejecución', 'progress' => 65],
            ['id' => 'MED-038', 'case_id' => 'CAS-2026-014', 'investigation_id' => 'INV-2026-009', 'cause' => 'Borde metálico sin protección', 'description' => 'Instalar protección y señalización en estanterías', 'category' => 'Ingeniería', 'owner' => 'Mantención', 'due' => '2026-09-30', 'status' => 'Vencida', 'progress' => 30],
            ['id' => 'MED-032', 'case_id' => 'CAS-2026-005', 'investigation_id' => 'INV-2026-002', 'cause' => 'Exposición reiterada a irritantes', 'description' => 'Evaluar sustitución de producto de limpieza', 'category' => 'Sustitución', 'owner' => 'Abastecimiento', 'due' => '2026-10-18', 'status' => 'Pendiente', 'progress' => 10],
            ['id' => 'MED-027', 'case_id' => 'CAS-2026-009', 'investigation_id' => 'INV-2026-004', 'cause' => 'Técnica de manipulación no estandarizada', 'description' => 'Capacitar al equipo de bodega', 'category' => 'Administrativa', 'owner' => 'Prevención de riesgos', 'due' => '2026-09-20', 'status' => 'Implementada', 'progress' => 100],
            ['id' => 'MED-011', 'case_id' => 'CAS-2026-001', 'investigation_id' => 'INV-2026-001', 'cause' => 'Herramienta inadecuada', 'description' => 'Entregar cortadores de seguridad', 'category' => 'Ingeniería', 'owner' => 'Jefatura local', 'due' => '2026-06-20', 'status' => 'Implementada', 'progress' => 100],
        ]);

        $observations = collect([
            ['id' => 'OBS-019', 'case_id' => 'CAS-2026-014', 'investigation_id' => 'INV-2026-009', 'section' => 'Hechos constatados', 'description' => 'Precisar cómo se verificó la condición del borde metálico.', 'author' => 'María José Fuentes', 'role' => 'Prevencionista', 'date' => '2026-10-01', 'status' => 'Pendiente', 'responses' => 0],
            ['id' => 'OBS-018', 'case_id' => 'CAS-2026-014', 'investigation_id' => 'INV-2026-009', 'section' => 'Matriz de causas', 'description' => 'Vincular la medida propuesta con el hecho H-03.', 'author' => 'María José Fuentes', 'role' => 'Prevencionista', 'date' => '2026-10-01', 'status' => 'Respondida', 'responses' => 1],
            ['id' => 'OBS-012', 'case_id' => 'CAS-2026-009', 'investigation_id' => 'INV-2026-004', 'section' => 'Relato final', 'description' => 'Se incorporó la hora confirmada por entrevista.', 'author' => 'Andrés Muñoz', 'role' => 'Comité Paritario', 'date' => '2026-09-12', 'status' => 'Resuelta', 'responses' => 2],
        ]);

        return compact('cases', 'investigations', 'measures', 'observations') + [
            'establishments' => ['CESFAM Los Aromos', 'SAR Central', 'CECOSF Norte', 'Posta Rural El Molino'],
            'activity' => [
                ['time' => 'Hoy, 10:20', 'text' => 'Se respondió una observación en INV-2026-009.'],
                ['time' => 'Ayer, 16:45', 'text' => 'MED-041 registró 65% de avance.'],
                ['time' => 'Ayer, 09:10', 'text' => 'CAS-2026-018 quedó pendiente de calificación.'],
                ['time' => '30 sep, 14:30', 'text' => 'INV-2026-004 fue concluida; sus medidas siguen activas.'],
            ],
        ];
    }

    /** @return array<string, int> */
    public function metrics(): array
    {
        $data = $this->data();

        return [
            'cases' => $data['cases']->count(),
            'investigations_pending' => $data['investigations']->whereNotIn('status', ['Concluida'])->count(),
            'with_observations' => $data['investigations']->where('status', 'Con observaciones')->count(),
            'measures_due_soon' => $data['measures']->whereIn('status', ['Pendiente', 'En ejecución'])->count(),
            'measures_overdue' => $data['measures']->where('status', 'Vencida')->count(),
        ];
    }

    /** @return array<string, mixed>|null */
    public function find(string $collection, string $id): ?array
    {
        $items = $this->data()[$collection] ?? collect();

        return $items instanceof Collection ? $items->firstWhere('id', $id) : null;
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
