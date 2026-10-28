<?php

namespace Database\Seeders;

use App\Models\CauseFactor;
use App\Models\QuestionnaireForm;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class InvestigationCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedCauseFactors();
        $this->seedQuestionnaires();
        $this->seedPrompt();
    }

    private function seedCauseFactors(): void
    {
        $path = database_path('data/matriz_factores_causas.csv');
        $contents = file_get_contents($path);
        if ($contents === false) {
            throw new RuntimeException("No se pudo leer {$path}.");
        }

        $utf8 = mb_convert_encoding($contents, 'UTF-8', 'CP850');
        $lines = preg_split('/\R/', trim($utf8));
        $headers = str_getcsv((string) array_shift($lines), ';');

        foreach ($lines as $line) {
            $values = str_getcsv($line, ';');
            if (count($headers) !== count($values)) {
                continue;
            }
            $row = array_combine($headers, $values);
            if ($row === false) {
                continue;
            }

            $codigo = trim($row['Código']);
            $subcodigo = trim($row['Subcódigo']);
            $glosa = trim($row['Glosa']);
            if ($codigo === '1104' && $subcodigo === 'I s L') {
                $subcodigo = '';
                $glosa = 'Inexistencia de procedimientos de evaluación y auditoría en el Sistema de Gestión de Riesgos Laborales.';
            }
            $codigoEfectivo = $subcodigo !== '' ? $subcodigo : $codigo;

            CauseFactor::updateOrCreate(['codigo_efectivo' => $codigoEfectivo], [
                'categoria' => trim($row['Categoría']), 'subcategoria' => trim($row['Subcategoría']),
                'codigo' => $codigo !== '' ? $codigo : null, 'subcodigo' => $subcodigo !== '' ? $subcodigo : null,
                'glosa' => $glosa, 'explicacion_glosa' => trim($row['Explicación de la glosa']),
                'pagina_pdf' => is_numeric($row['Página PDF']) ? (int) $row['Página PDF'] : null, 'activo' => true,
            ]);
        }
    }

    private function seedQuestionnaires(): void
    {
        $common = [
            'interviewee_name' => 'Nombre de la persona entrevistada', 'interviewee_rut' => 'RUT de la persona entrevistada',
            'witness_type' => 'Tipo de testigo', 'interviewee_role' => 'Cargo o función',
            'interviewee_company' => 'Empresa o establecimiento', 'interview_date' => 'Fecha de entrevista',
            'interview_start_time' => 'Hora de inicio', 'interview_location' => 'Lugar o modalidad de la entrevista',
            'statement' => 'Relato libre o información adicional', 'interview_observations' => 'Observaciones de la entrevista',
        ];
        $adapted = [
            'chronology_detail' => 'Detalle cronológico', 'chronology_not_applicable' => 'Hecho cronológico no aplicable',
            'task_own_job' => 'La tarea correspondía a su trabajo', 'task_habitual' => 'La tarea era habitual',
            'task_frequency' => 'Frecuencia de la tarea', 'task_same_way' => 'La tarea se realizaba de la forma habitual',
            'task_change_reasons' => 'Razones del cambio en la tarea', 'task_change_other' => 'Otra razón del cambio',
            'task_usual_accident_possible' => 'El accidente podía ocurrir ejecutando la tarea habitual',
            'instructions_received' => 'Recibió instrucciones', 'instruction_type' => 'Tipo de instrucción',
            'instruction_source' => 'Origen de la instrucción', 'followed_instructions' => 'Siguió las instrucciones',
            'ppe_habitual' => 'Uso habitual de elementos de protección personal', 'ppe_description' => 'Descripción de EPP',
            'usual_place' => 'Lugar habitual de la tarea', 'different_place_reason' => 'Motivo de cambio de lugar',
            'usual_time' => 'Horario habitual de la tarea', 'different_time_reason' => 'Motivo de cambio de horario',
            'equipment_used' => 'Uso de equipos o herramientas', 'equipment_description' => 'Descripción de equipos',
            'equipment_conditions' => 'Condiciones de equipos', 'equipment_other' => 'Otra condición del equipo',
            'substance_involved' => 'Participación de sustancias o materiales', 'substance_types' => 'Tipos de sustancias',
            'substance_habitual' => 'Uso habitual de la sustancia', 'substance_reason' => 'Motivo del uso no habitual',
            'material_conditions' => 'Condiciones de materiales', 'facility_conditions' => 'Condiciones de instalaciones',
            'time_conditions' => 'Condiciones temporales',
        ];
        $legacy = [
            'initial_story' => 'Relato inicial', 'environment_conditions' => 'Condiciones del entorno',
            'other_environment_condition' => 'Otra condición del entorno', 'ppe_required' => 'La tarea requería EPP',
            'ppe_used' => 'EPP utilizados', 'other_ppe' => 'Otro EPP',
            'instruction_received' => 'Recibió instrucción para realizar la tarea', 'instruction_types' => 'Tipos de instrucción recibida',
        ];

        $this->createForm('Cuestionario adaptado de investigación', 'adapted', [...$common, ...$adapted]);
        $this->createForm('Cuestionario anterior de investigación', 'legacy', [...$common, ...$legacy]);
    }

    /** @param array<string, string> $questions */
    private function createForm(string $nombre, string $tipo, array $questions): void
    {
        $form = QuestionnaireForm::updateOrCreate(['tipo' => $tipo, 'version' => 1], ['nombre' => $nombre, 'estado' => 'activo']);
        $identificationVariables = ['interviewee_name', 'interviewee_rut', 'witness_type', 'interviewee_role', 'interviewee_company', 'interview_date', 'interview_start_time', 'interview_location'];
        $multipleVariables = ['chronology_detail', 'chronology_not_applicable', 'task_change_reasons', 'instruction_type', 'instruction_source', 'equipment_conditions', 'substance_types', 'material_conditions', 'facility_conditions', 'time_conditions', 'environment_conditions', 'ppe_used', 'instruction_types'];
        foreach ($questions as $variable => $label) {
            $form->questions()->updateOrCreate(['variable' => $variable], [
                'pregunta' => $label, 'tipo_respuesta' => in_array($variable, $multipleVariables, true) ? 'multiple' : 'text',
                'seccion' => in_array($variable, $identificationVariables, true) ? 'identificacion' : 'cuestionario',
                'orden' => array_search($variable, array_keys($questions), true) + 1, 'obligatoria' => in_array($variable, ['interviewee_name', 'witness_type'], true),
                'activa' => true,
            ]);
        }
    }

    private function seedPrompt(): void
    {
        DB::table('ai_prompts')->updateOrInsert(['nombre' => 'analisis-investigacion'], [
            'version' => 3, 'modelo' => 'qwen2.5-7b-instruct', 'activo' => true,
            'prompt_base' => 'Aplica la metodología del Árbol de Causas con trazabilidad por fuente. Distingue eventos puntuales, condiciones permanentes, declaraciones, hechos corroborados y datos pendientes; declarado no significa verificado, y una cita no equivale por sí sola a corroboración. Produce por separado hechos cronológicos, vacíos de información y árbol retrospectivo desde la lesión. No incluyas falta de recuerdo o de inspección como hechos: regístralas como verificaciones pendientes. El árbol usa solo hechos revisados, vincula antecedente→consecuencia y clasifica cadena, conjunción (antecedentes independientes conjuntamente necesarios) o disyunción; valida cada vínculo con la prueba contrafactual. No confundas cronología con causalidad, no fuerces conectividad ni inventes hechos, evidencia, relaciones, diagnósticos, normas infringidas ni responsabilidades. Usa exclusivamente códigos de la matriz local entregada y solo si están respaldados por hechos. Propón medidas concretas y verificables ligadas a nodos causales, priorizando controles de mayor jerarquía. Explicita fuentes, contradicciones y vacíos; la salida es una propuesta editable que requiere revisión humana.',
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }
}
