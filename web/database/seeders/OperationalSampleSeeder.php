<?php

namespace Database\Seeders;

use App\Models\Establishment;
use App\Models\Investigation;
use App\Models\OccupationalCase;
use App\Models\User;
use Illuminate\Database\Seeder;

class OperationalSampleSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::query()->whereHas('roles', fn ($query) => $query->where('slug', 'prevencion'))->first()
            ?? User::query()->firstOrFail();

        // Establecimientos reales de la Red de Salud Comunal de Los Ángeles
        $nororiente = Establishment::updateOrCreate(['name' => 'CESFAM Nororiente'], ['type' => 'CESFAM', 'is_active' => true]);
        $losAromos = Establishment::updateOrCreate(['name' => 'CESFAM Los Aromos'], ['type' => 'CESFAM', 'is_active' => true]);
        $nuevoHorizonte = Establishment::updateOrCreate(['name' => 'CESFAM Nuevo Horizonte'], ['type' => 'CESFAM', 'is_active' => true]);
        $dosSeptiembre = Establishment::updateOrCreate(['name' => 'CESFAM 2 de Septiembre'], ['type' => 'CESFAM', 'is_active' => true]);
        $sarCentral = Establishment::updateOrCreate(['name' => 'SAR Central'], ['type' => 'SAR', 'is_active' => true]);
        $dcsAdmin = Establishment::updateOrCreate(['name' => 'Dirección Comunal de Salud'], ['type' => 'Oficina administrativa', 'is_active' => true]);

        // CASO 1: Daniela Araya (En investigación)
        $case1 = OccupationalCase::updateOrCreate(['folio' => 'GIATEP-2026-001'], $this->caseData($owner, $nororiente, [
            'status' => 'En investigación',
            'case_type' => 'Accidente de trabajo',
            'worker_name' => 'Daniela Araya Soto',
            'worker_rut' => '17.456.321-8',
            'job_title' => 'Técnica en enfermería (TENS)',
            'workplace' => 'Bodega clínica - CESFAM Nororiente',
            'worker_phone' => '+56 9 8123 4567',
            'event_date' => '2026-10-02',
            'event_time' => '09:15',
            'event_location' => 'Pasillo de acceso a bodega clínica',
            'narrative' => 'Durante el traslado de insumos clínicos, la trabajadora resbaló sobre una zona húmeda del pasillo y cayó apoyando la rodilla derecha contra el suelo de linóleo.',
            'witnesses' => 'Testigo directo: Marcelo Fuentes Rojas (Auxiliar de servicio).',
            'details' => ['affected_body_part' => 'Rodilla derecha', 'accident_severity' => 'Leve', 'injury_type' => 'Contusión'],
        ]));

        $inv1 = Investigation::updateOrCreate(['occupational_case_id' => $case1->id], [
            'folio' => 'INV-2026-001',
            'owner_id' => $owner->id,
            'status' => 'En desarrollo',
            'final_story' => 'A las 09:15, durante el traslado de insumos hacia la bodega clínica, la trabajadora ingresó al pasillo de acceso. El piso se encontraba húmedo tras aseo matinal y sin señalización preventiva. Al pisar la zona perdió estabilidad y cayó sobre su rodilla derecha.',
        ]);

        $inv1->facts()->delete();
        foreach (['El piso del pasillo se encontraba húmedo tras el lavado matinal.', 'La zona no contaba con señalización preventiva de piso húmedo.', 'La trabajadora trasladaba insumos hacia la bodega con calzado clínico reglamentario.'] as $position => $fact) {
            $inv1->facts()->create(['position' => $position + 1, 'description' => $fact, 'ai_generated' => false]);
        }

        $inv1->causalNodes()->delete();
        foreach ([
            ['final', 'Caída a mismo nivel con contusión en rodilla derecha'],
            ['cause', 'Piso húmedo por aseo reciente'],
            ['cause', 'Ausencia de cono o señalética de advertencia'],
            ['origin', 'Falta de protocolo coordinado entre turnos de aseo y traslado de insumos']
        ] as $position => [$type, $description]) {
            $inv1->causalNodes()->create(['node_type' => $type, 'position' => $position + 1, 'description' => $description, 'ai_generated' => false]);
        }

        $inv1->measures()->delete();
        $inv1->measures()->createMany([
            ['description' => 'Instalar señalización inmediata tipo cono/caballete ante labores de limpieza o derrames.', 'category' => 'Administrativa', 'responsible' => 'Jefatura de Servicios Generales', 'due_date' => '2026-10-20', 'status' => 'En ejecución', 'progress' => 60],
            ['description' => 'Revisar y actualizar el procedimiento de secado rápido y tránsito seguro de pasillos.', 'category' => 'Administrativa', 'responsible' => 'Unidad de Prevención de Riesgos', 'due_date' => '2026-10-25', 'status' => 'Pendiente', 'progress' => 0],
        ]);

        $inv1->interviews()->delete();
        $interview1 = $inv1->interviews()->create([
            'created_by' => $owner->id,
            'questionnaire_type' => 'adapted',
            'interviewee_name' => 'Marcelo Fuentes Rojas',
            'interviewee_role' => 'Auxiliar de servicio',
            'witness_type' => 'direct',
            'interview_date' => '2026-10-03',
            'status' => 'Completada',
        ]);
        $interview1->answers()->createMany([
            ['question_key' => 'statement', 'answer_type' => 'text', 'value' => ['Observé el piso húmedo recién trapeado; no se había colocado aún el cono amarillo de advertencia.']],
            ['question_key' => 'environment_conditions', 'answer_type' => 'array', 'value' => ['Piso resbaladizo', 'Señalización insuficiente']],
        ]);

        // CASO 2: Luis Morales (Accidente de trayecto - Registrado)
        $case2 = OccupationalCase::updateOrCreate(['folio' => 'GIATEP-2026-002'], $this->caseData($owner, $sarCentral, [
            'status' => 'En investigación',
            'case_type' => 'Accidente de trayecto',
            'worker_name' => 'Luis Morales Vera',
            'worker_rut' => '18.765.432-1',
            'job_title' => 'Administrativo de Admisión',
            'workplace' => 'SAR Central',
            'worker_phone' => '+56 9 9345 6789',
            'event_date' => '2026-10-08',
            'event_time' => '08:05',
            'event_location' => 'Intersección Avenida Central con Los Carrera (vía pública)',
            'narrative' => 'El trabajador informó una caída durante su trayecto habitual directo desde su domicilio hacia el turno de mañana en el SAR Central.',
            'witnesses' => 'Sin testigos presenciales directos. Declaración jurada y parte policial de constatación.',
            'details' => ['affected_body_part' => 'Tobillo izquierdo', 'accident_severity' => 'Leve', 'injury_type' => 'Esguince'],
        ]));

        Investigation::updateOrCreate(['occupational_case_id' => $case2->id], [
            'folio' => 'INV-2026-002',
            'owner_id' => $owner->id,
            'status' => 'En desarrollo',
            'final_story' => 'El día 08 de octubre a las 08:05 horas, el funcionario se desplazaba en trayecto habitual directo desde su domicilio hacia su turno en el SAR Central. En la acera peatonal de Avenida Central con Los Carrera, pisó una baldosa suelta con desnivel, sufriendo una torsión forzada que le ocasionó un esguince en su tobillo izquierdo.',
        ]);

        // CASO 3: Claudia Rubilar (Caso Real Documentación - Con observaciones)
        $case3 = OccupationalCase::updateOrCreate(['folio' => 'GIATEP-2026-003'], $this->caseData($owner, $nororiente, [
            'status' => 'Con observaciones',
            'case_type' => 'Accidente de trabajo',
            'worker_name' => 'Claudia Nicole Rubilar Heredia',
            'worker_rut' => '17.592.581-3',
            'job_title' => 'Odontóloga',
            'workplace' => 'Box Dental 3 - CESFAM Nororiente',
            'worker_phone' => '+56 9 7654 3210',
            'event_date' => '2026-09-09',
            'event_time' => '13:15',
            'event_location' => 'Box Dental N° 3',
            'narrative' => 'Durante la atención odontológica programada, al retirar una sonda de exploración de la bandeja instrumental, se produjo un pinchazo accidental en el dedo índice de la mano izquierda.',
            'witnesses' => 'Jefatura Directa: Niza Gallo Ibáñez. Asistente dental presente en box.',
            'details' => ['affected_body_part' => 'Dedo índice izquierdo', 'accident_severity' => 'Leve', 'injury_type' => 'Punción cortopunzante'],
        ]));

        $inv3 = Investigation::updateOrCreate(['occupational_case_id' => $case3->id], [
            'folio' => 'INV-2026-003',
            'owner_id' => $owner->id,
            'status' => 'Con observaciones',
            'final_story' => 'A las 13:15 en Box 3, al finalizar procedimiento de profilaxis, la profesional procedió a clasificar el instrumental sucio. Durante el descarte, la sonda tuvo contacto accidental con guante de látex provocando punción superficial.',
        ]);

        $inv3->facts()->delete();
        foreach (['Manipulación de instrumental cortopunzante para esterilización.', 'Bandeja de descarte con espacio reducido.', 'Protocolo de manejo de material cortopunzante aplicado de inmediato.'] as $pos => $f) {
            $inv3->facts()->create(['position' => $pos + 1, 'description' => $f, 'ai_generated' => false]);
        }

        $inv3->causalNodes()->delete();
        foreach ([
            ['final', 'Punción accidental en índice izquierdo con instrumental dental'],
            ['cause', 'Manipulación directa de material sin pinza de transferencia'],
            ['cause', 'Disposición apretada de cajas de instrumental en mesón clínico'],
            ['origin', 'Procedimiento de Trabajo Seguro pendiente de actualización respecto a instrumental crítico']
        ] as $pos => [$type, $desc]) {
            $inv3->causalNodes()->create(['node_type' => $type, 'position' => $pos + 1, 'description' => $desc, 'ai_generated' => false]);
        }

        $inv3->measures()->delete();
        $inv3->measures()->createMany([
            ['description' => 'Incorporar pinzas de transferencia seguras en todos los boxes dentales del establecimiento.', 'category' => 'Ingeniería', 'responsible' => 'Jefatura de Odontología / Abastecimiento', 'due_date' => '2026-10-30', 'status' => 'En ejecución', 'progress' => 45],
            ['description' => 'Capacitar a todo el estamento odontológico en protocolo de descarte cortopunzante.', 'category' => 'Administrativa', 'responsible' => 'Prevención de Riesgos', 'due_date' => '2026-11-05', 'status' => 'Pendiente', 'progress' => 10],
        ]);

        // CASO 4: Marcela Illanes (Caso Real Documentación - Seguimiento de medidas)
        $case4 = OccupationalCase::updateOrCreate(['folio' => 'GIATEP-2026-004'], $this->caseData($owner, $losAromos, [
            'status' => 'Seguimiento de medidas',
            'case_type' => 'Accidente de trabajo',
            'worker_name' => 'Marcela Illanes Castillo',
            'worker_rut' => '15.342.119-4',
            'job_title' => 'Técnica en Enfermería (TENS)',
            'workplace' => 'Sector Amarillo - CESFAM Los Aromos',
            'worker_phone' => '+56 9 6543 2109',
            'event_date' => '2026-09-15',
            'event_time' => '11:30',
            'event_location' => 'Sala de procedimientos',
            'narrative' => 'Al movilizar un paciente de movilidad reducida desde la camilla a la silla de ruedas, sintió un dolor agudo y punzante en la zona lumbar baja.',
            'witnesses' => 'Compañera TENS de turno y familiar acompañante del usuario.',
            'details' => ['affected_body_part' => 'Zona lumbar', 'accident_severity' => 'Leve', 'injury_type' => 'Sobreesfuerzo muscular'],
        ]));

        $inv4 = Investigation::updateOrCreate(['occupational_case_id' => $case4->id], [
            'folio' => 'INV-2026-004',
            'owner_id' => $owner->id,
            'status' => 'Concluida',
            'final_story' => 'Durante la transferencia asistida de un usuario de 85 kg, la técnica debió realizar un giro con carga sin asistencia mecánica, superando el límite ergonómico de manejo manual de pacientes.',
        ]);

        $inv4->measures()->delete();
        $inv4->measures()->createMany([
            ['description' => 'Adquisición e instalación de cinturones de transferencia ergonómica para salas de procedimientos.', 'category' => 'Ingeniería', 'responsible' => 'Dirección Comunal de Salud / Abastecimiento', 'due_date' => '2026-10-15', 'status' => 'En ejecución', 'progress' => 80],
            ['description' => 'Taller de ergonomía y técnica segura de movilización de pacientes (Protocolo MMC).', 'category' => 'Administrativa', 'responsible' => 'Comité Paritario y Prevencionista', 'due_date' => '2026-10-22', 'status' => 'Implementada', 'progress' => 100],
        ]);

        // CASO 5: Fernanda López (Enfermedad Profesional - Formulario DIEP)
        $case5 = OccupationalCase::updateOrCreate(['folio' => 'GIATEP-2026-005'], $this->caseData($owner, $nuevoHorizonte, [
            'status' => 'En investigación',
            'case_type' => 'Enfermedad profesional',
            'worker_name' => 'Fernanda López Mena',
            'worker_rut' => '16.890.123-5',
            'job_title' => 'Auxiliar de Farmacia y Esterilización',
            'workplace' => 'Central de Esterilización - CESFAM Nuevo Horizonte',
            'worker_phone' => '+56 9 5432 1098',
            'event_date' => '2026-09-20',
            'event_time' => '10:00',
            'event_location' => 'Unidad de Esterilización',
            'narrative' => 'La funcionaria presenta lesiones descamativas, eritema y prurito intenso en ambas manos, compatible con dermatitis de contacto por exposición reiterada a detergentes enzimáticos y desinfectantes químicos de alto nivel.',
            'witnesses' => 'Jefatura de Farmacia y médico examinador de salud ocupacional.',
            'details' => [
                'affected_body_part' => 'Manos y muñecas',
                'suspected_agent' => 'Químico irritante (Detergentes enzimáticos / Glutaraldehído)',
                'symptom_duration' => '3 meses progresivos',
                'form_type' => 'DIEP'
            ],
        ]));

        $inv5 = Investigation::updateOrCreate(['occupational_case_id' => $case5->id], [
            'folio' => 'INV-2026-005',
            'owner_id' => $owner->id,
            'status' => 'En desarrollo',
            'final_story' => 'Evaluación de puesto de trabajo en Central de Esterilización. Se constata manipulación diaria de soluciones desinfectantes concentradas con guantes estándar sin resistencia química específica requerida por ficha de seguridad.',
        ]);

        $inv5->measures()->delete();
        $inv5->measures()->createMany([
            ['description' => 'Suministro inmediato de guantes de nitrilo de caña larga certificados contra agentes químicos.', 'category' => 'Equipos de protección personal', 'responsible' => 'Prevención de Riesgos / Bodega Central', 'due_date' => '2026-10-18', 'status' => 'En ejecución', 'progress' => 70],
            ['description' => 'Derivación formal a organismo administrador (ACHS) para calificación de enfermedad profesional.', 'category' => 'Administrativa', 'responsible' => 'Unidad de Prevención DCS', 'due_date' => '2026-10-25', 'status' => 'En ejecución', 'progress' => 50],
        ]);

        // CASO 6: Carlos Vega (Caso Cerrado por Comité Paritario / Prevención)
        $case6 = OccupationalCase::updateOrCreate(['folio' => 'GIATEP-2026-006'], $this->caseData($owner, $sarCentral, [
            'status' => 'Cerrado',
            'case_type' => 'Accidente de trabajo',
            'worker_name' => 'Carlos Vega Sepúlveda',
            'worker_rut' => '12.876.543-9',
            'job_title' => 'Conductor de Ambulancia de Urgencia',
            'workplace' => 'Base Ambulancias - SAR Central',
            'worker_phone' => '+56 9 4321 0987',
            'event_date' => '2026-08-10',
            'event_time' => '16:40',
            'event_location' => 'Patio de maniobras vehiculares',
            'narrative' => 'Al descender del móvil de emergencia tras acudir a un llamado de urgencia, pisó el borde de un bache en el pavimento asfáltico, sufriendo torsión leve en tobillo.',
            'witnesses' => 'Paramédico acompañante de la tripulación.',
            'details' => ['affected_body_part' => 'Tobillo derecho', 'accident_severity' => 'Leve', 'injury_type' => 'Esguince leve grado 1'],
        ]));

        $inv6 = Investigation::updateOrCreate(['occupational_case_id' => $case6->id], [
            'folio' => 'INV-2026-006',
            'owner_id' => $owner->id,
            'status' => 'Concluida',
            'final_story' => 'Caso regularizado. Bache reparado mediante pavimentación en frío del patio de maniobras. Conductor recuperado y reintegrado a funciones con alta médica ACHS.',
        ]);

        $inv6->measures()->delete();
        $inv6->measures()->createMany([
            ['description' => 'Reparación de desniveles y baches en el patio de estacionamiento de ambulancias.', 'category' => 'Ingeniería', 'responsible' => 'Mantención Municipal', 'due_date' => '2026-08-30', 'status' => 'Implementada', 'progress' => 100],
        ]);

        // CASO 7: Camila Valenzuela (Nuevo caso registrado - SIN iniciar investigación)
        $case7 = OccupationalCase::updateOrCreate(['folio' => 'GIATEP-2026-007'], $this->caseData($owner, $dosSeptiembre, [
            'status' => 'Registrado',
            'case_type' => 'Accidente de trabajo',
            'worker_name' => 'Camila Paz Valenzuela Morales',
            'worker_rut' => '19.345.678-2',
            'job_title' => 'Técnica en enfermería (TENS) de Vacunatorio',
            'workplace' => 'Box de Vacunatorio - CESFAM 2 de Septiembre',
            'worker_phone' => '+56 9 7890 1234',
            'event_date' => '2026-10-14',
            'event_time' => '10:15',
            'event_location' => 'Box de Inmunizaciones / Vacunatorio, Sector A',
            'narrative' => 'Durante la jornada de vacunación escolar en el CESFAM 2 de Septiembre, la funcionaria procedió al descarte de una jeringa en el contenedor de residuos cortopunzantes. Al encontrarse el receptáculo con sobrellenado (más de 3/4 de su capacidad reglamentaria), sufrió una punción accidental en la falange distal del dedo índice de su mano derecha con una aguja expuesta en la parte superior.',
            'witnesses' => 'Romina Cárdenas Silva (Enfermera Coordinadora de Vacunatorio).',
            'details' => [
                'affected_body_part' => 'Dedo índice derecho',
                'injury_type' => 'Herida cortopunzante con riesgo biológico',
                'accident_severity' => 'Moderado',
                'doing_usual_job' => 'Sí',
                'work_schedule' => '08:00 a 17:00 horas',
                'activity_before_event' => 'Administración de inmunizaciones y descarte de material clínico',
            ],
        ]));

        // Asegurar que no tenga investigación creada para mantener el estado "Registrado"
        Investigation::query()->where('occupational_case_id', $case7->id)->delete();
    }

    private function caseData(User $owner, Establishment $establishment, array $data): array
    {
        return $data + [
            'created_by' => $owner->id,
            'establishment_id' => $establishment->id,
            'employer_name' => 'Dirección Comunal de Salud de Los Ángeles',
            'employer_rut' => '69.170.102-6',
            'employer_address' => 'Calle Valdivia #150',
            'employer_commune' => 'Los Ángeles',
            'economic_activity' => 'Servicios de Salud Pública Municipal (Atención Primaria APS)',
            'company_type' => 'Principal',
            'administrator' => 'ACHS',
        ];
    }
}
