<?php

use App\Models\CauseFactor;
use App\Models\Establishment;
use App\Models\Investigation;
use App\Models\OccupationalCase;
use App\Models\User;
use App\Services\LmStudioFinalStoryGenerator;
use App\Services\LmStudioInvestigationAnalyzer;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$owner = User::query()->where('email', 'prevencionista.demo@giatep.local')->firstOrFail();
$establishment = Establishment::query()->where('is_active', true)->orderBy('id')->firstOrFail();
$marker = 'PRUEBA IA GIATEP '.now()->format('Ymd');
$cases = [
    [
        'suffix' => 'A', 'rut' => '99.999.991-1', 'worker' => 'Camila Trabajadora Ficticia A', 'job' => 'Técnica de abastecimiento',
        'type' => 'Accidente de trabajo', 'date' => now()->subDay()->toDateString(), 'time' => '10:20', 'location' => 'Pasillo de abastecimiento',
        'employer' => 'Empresa Ficticia de Pruebas MAC A', 'activity' => 'Servicios de salud (demostración)',
        'body_part' => 'Muñeca izquierda',
        'narrative' => 'Caso ficticio de prueba. La trabajadora trasladaba una caja de insumos sobre un carro desde la bodega hacia una sala de atención. Durante el traslado, una rueda del carro se detuvo al pasar por una unión del piso. La trabajadora informó dolor en la muñeca izquierda y avisó a su supervisora aproximadamente a las 10:25. No se ha confirmado el peso de la caja ni el estado de la rueda.',
        'witnesses' => 'Entrevistas ficticias de prueba: trabajadora afectada y auxiliar que observó el traslado.',
        'interviews' => [
            ['name' => 'Camila Trabajadora Ficticia A', 'role' => 'Técnica de abastecimiento', 'type' => 'Trabajadora afectada', 'answers' => [
                'task_habitual' => 'Sí. Habitualmente traslada insumos entre la bodega y las salas de atención.',
                'event_sequence' => 'Aproximadamente a las 10:20 trasladaba una caja sobre un carro. Al pasar por una unión del piso, una rueda se detuvo. Sintió dolor en la muñeca izquierda y avisó a su supervisora cerca de las 10:25.',
                'environment_conditions' => 'El carro circulaba por el pasillo de abastecimiento. No recuerda si la unión del piso estaba desnivelada.',
                'equipment' => 'Carro de transporte con ruedas. No se verificó el estado de la rueda durante la entrevista.',
                'injury_or_effect' => 'Dolor en la muñeca izquierda durante el traslado. Diagnóstico no informado.',
            ]],
            ['name' => 'Matías Testigo Ficticio A', 'role' => 'Auxiliar de servicios', 'type' => 'Testigo presencial', 'answers' => [
                'event_sequence' => 'Observó a la trabajadora empujando un carro con una caja por el pasillo cerca de las 10:20. Vio que el carro se detuvo en una unión del piso. No observó cómo se produjo el dolor.',
                'environment_conditions' => 'El pasillo estaba habilitado para el tránsito de carros. No inspeccionó la unión del piso.',
                'injury_or_effect' => 'Después del traslado vio a la trabajadora sostenerse la muñeca izquierda.',
            ]],
        ],
    ],
    [
        'suffix' => 'B', 'rut' => '99.999.992-9', 'worker' => 'Diego Trabajador Ficticio B', 'job' => 'Auxiliar de farmacia',
        'type' => 'Accidente de trabajo', 'date' => now()->subDays(2)->toDateString(), 'time' => '15:40', 'location' => 'Acceso a bodega de farmacia',
        'employer' => 'Empresa Ficticia de Pruebas MAC B', 'activity' => 'Servicios de salud (demostración)',
        'body_part' => 'Rodilla derecha',
        'narrative' => 'Caso ficticio de prueba. El trabajador caminaba hacia la bodega de farmacia llevando una carpeta. En el acceso resbaló y cayó, golpeándose la rodilla derecha. El trabajador señaló que había agua en el piso. Una compañera indicó que vio humedad antes del evento, pero no pudo precisar su origen. No se ha confirmado mediante inspección el estado del piso ni la presencia de señalización.',
        'witnesses' => 'Entrevistas ficticias de prueba: trabajador afectado y compañera que transitó por el sector.',
        'interviews' => [
            ['name' => 'Diego Trabajador Ficticio B', 'role' => 'Auxiliar de farmacia', 'type' => 'Trabajador afectado', 'answers' => [
                'task_habitual' => 'Sí. Se dirigía hacia la bodega de farmacia con una carpeta.',
                'event_sequence' => 'Cerca de las 15:40 caminaba por el acceso a la bodega, resbaló y cayó. Al caer se golpeó la rodilla derecha.',
                'environment_conditions' => 'Observó agua o humedad en el piso después de caer. No sabe de dónde provenía.',
                'injury_or_effect' => 'Golpe en la rodilla derecha. Diagnóstico no informado.',
            ]],
            ['name' => 'Rocío Testigo Ficticia B', 'role' => 'Administrativa', 'type' => 'Testigo indirecto', 'answers' => [
                'event_sequence' => 'Transitó por el acceso unos minutos antes y observó una zona húmeda. No presenció la caída; escuchó el aviso después.',
                'environment_conditions' => 'Vio humedad en una parte del acceso. No identificó su origen ni verificó si había señalización.',
                'injury_or_effect' => 'Al llegar después del aviso vio al trabajador en el suelo y luego supo que se había golpeado la rodilla derecha.',
            ]],
        ],
    ],
];

$generator = app(LmStudioFinalStoryGenerator::class);
$analyzer = app(LmStudioInvestigationAnalyzer::class);
$results = [];

foreach ($cases as $definition) {
    $signature = $marker.' '.$definition['suffix'];
    $case = OccupationalCase::query()->where('narrative', 'like', '%'.$signature.'%')->first();
    if (! $case) {
        $case = DB::transaction(function () use ($owner, $establishment, $definition, $signature): OccupationalCase {
            $prefix = 'GIATEP-'.now()->year.'-';
            $last = OccupationalCase::query()->where('folio', 'like', $prefix.'%')->lockForUpdate()->max('folio');
            $sequence = $last ? ((int) substr($last, -3)) + 1 : 1;
            $case = OccupationalCase::create([
                'folio' => $prefix.str_pad((string) $sequence, 3, '0', STR_PAD_LEFT), 'created_by' => $owner->id,
                'establishment_id' => $establishment->id, 'status' => 'En investigación', 'case_type' => $definition['type'],
                'employer_name' => $definition['employer'], 'employer_rut' => '99.999.99'.($definition['suffix'] === 'A' ? '3-6' : '4-4'),
                'employer_address' => 'Dirección ficticia de demostración 100', 'employer_commune' => 'Los Ángeles',
                'economic_activity' => $definition['activity'], 'company_type' => 'Principal', 'administrator' => 'ACHS',
                'worker_name' => $definition['worker'], 'worker_rut' => $definition['rut'],
                'worker_address' => 'Domicilio ficticio de prueba', 'worker_commune' => 'Los Ángeles',
                'worker_sex' => 'No informado', 'nationality' => 'Chilena', 'job_title' => $definition['job'],
                'workplace' => $establishment->name, 'contract_type' => 'Indefinido', 'occupational_category' => 'Trabajador dependiente',
                'event_date' => $definition['date'], 'event_time' => $definition['time'], 'event_location' => $definition['location'],
                'narrative' => $signature.' · '.$definition['narrative'], 'witnesses' => $definition['witnesses'],
                'details' => ['affected_body_part' => $definition['body_part'], 'test_data' => true],
            ]);

            $investigation = $case->investigation()->create([
                'folio' => 'INV-'.now()->year.'-'.str_pad((string) ((int) Investigation::query()->max('id') + 1), 3, '0', STR_PAD_LEFT),
                'owner_id' => $owner->id, 'status' => 'En investigación', 'final_story' => null,
            ]);

            foreach ($definition['interviews'] as $interviewData) {
                $interview = $investigation->interviews()->create([
                    'created_by' => $owner->id, 'questionnaire_type' => 'adapted',
                    'interviewee_name' => $interviewData['name'], 'interviewee_role' => $interviewData['role'],
                    'witness_type' => $interviewData['type'], 'interview_date' => $definition['date'], 'status' => 'Completada',
                ]);
                foreach ($interviewData['answers'] as $key => $value) {
                    $interview->answers()->create(['question_key' => $key, 'answer_type' => 'text', 'value' => [$value]]);
                }
            }

            return $case;
        });
    }

    $case->load('investigation.interviews.answers.question');
    $entry = ['case' => $case->folio, 'worker' => $case->worker_name, 'investigation' => $case->investigation->folio, 'interviews' => $case->investigation->interviews->count()];

    try {
        $finalStory = trim((string) $case->investigation->final_story);
        if ($finalStory === '') {
            $draft = $generator->generate($case);
            $finalStory = $draft['story'];
            $case->investigation->update(['final_story' => $finalStory]);
        }
        $analysis = $analyzer->analyze($finalStory);
        $investigation = $case->investigation;
        DB::transaction(function () use ($analysis, $finalStory, $investigation, $owner): void {
            $investigation->update(['final_story' => $finalStory, 'ai_model' => $analysis['model'], 'ai_analyzed_at' => now()]);
            foreach ($analysis['facts'] as $position => $fact) {
                $investigation->facts()->create(['position' => $position + 1, 'description' => $fact['description'], 'ai_generated' => true]);
            }
            $graph = $analysis['tree'];
            $nodeById = collect($graph['nodes'])->keyBy('id');
            $targetIds = collect($graph['edges'])->pluck('to')->all();
            $roots = collect($graph['nodes'])->filter(fn (array $node): bool => ! in_array($node['id'], $targetIds, true));
            $final = $nodeById->get($graph['final_node_id']);
            $investigation->causalNodes()->create(['node_type' => 'final', 'position' => 1, 'description' => $final['description'], 'ai_generated' => true]);
            $investigation->causalNodes()->create(['node_type' => 'origin', 'position' => 1, 'description' => $roots->pluck('description')->implode('; '), 'ai_generated' => true]);
            foreach (collect($graph['nodes'])->reject(fn (array $node): bool => in_array($node['id'], [$graph['final_node_id'], ...$roots->pluck('id')->all()], true))->values() as $position => $node) {
                $investigation->causalNodes()->create(['node_type' => 'cause', 'position' => $position + 1, 'description' => $node['description'], 'ai_generated' => true]);
            }
            $investigation->causalNodes()->create(['node_type' => 'graph', 'position' => 1, 'description' => json_encode($graph, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR), 'ai_generated' => true]);

            $factorIds = CauseFactor::query()->whereIn('codigo_efectivo', $analysis['factor_codes'])->pluck('id');
            $investigation->causeFactors()->sync($factorIds->mapWithKeys(fn (int $id): array => [$id => ['origen' => 'ia', 'validated_by' => $owner->id, 'validated_at' => now()]])->all());
            foreach ($analysis['measures'] as $measure) {
                $description = $measure['description'];
                if ($measure['criterion'] !== '') {
                    $description .= "\nCriterio de cobertura y estabilidad: ".$measure['criterion'];
                }
                $related = collect($measure['related_node_ids'])->map(fn (string $id): ?string => $nodeById->get($id)['description'] ?? null)->filter();
                if ($related->isNotEmpty()) {
                    $description .= "\nCausas abordadas: ".$related->implode('; ');
                }
                $investigation->measures()->create([
                    'description' => $description, 'category' => $measure['category'], 'responsible' => $measure['responsible'],
                    'due_date' => $measure['due_date'] ?: null, 'ai_generated' => true,
                ]);
            }

            DB::table('ai_analysis_runs')->insert([
                'investigation_id' => $investigation->id, 'ai_prompt_id' => DB::table('ai_prompts')->where('nombre', 'analisis-investigacion')->where('activo', true)->value('id'),
                'requested_by' => $owner->id, 'modelo' => $analysis['model'], 'entrada' => $finalStory,
                'salida' => json_encode($analysis, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR), 'estado' => 'completado',
                'duracion_ms' => null, 'created_at' => now(), 'updated_at' => now(),
            ]);
        });

        $entry += [
            'status' => 'completada', 'model' => $analysis['model'], 'final_story' => $finalStory,
            'facts' => count($analysis['facts']), 'tree_nodes' => count($analysis['tree']['nodes']),
            'tree_edges' => count($analysis['tree']['edges']), 'factor_codes' => $analysis['factor_codes'],
            'measures' => count($analysis['measures']), 'guide_pages' => $analysis['guide_reference']['pages'],
        ];
    } catch (Throwable $exception) {
        $entry['status'] = 'falló generación/análisis: '.$exception->getMessage();
        DB::table('ai_analysis_runs')->insert([
            'investigation_id' => $case->investigation->id, 'ai_prompt_id' => DB::table('ai_prompts')->where('nombre', 'analisis-investigacion')->where('activo', true)->value('id'),
            'requested_by' => $owner->id, 'modelo' => (string) config('services.lm_studio.model', 'no configurado'),
            'entrada' => (string) ($case->investigation->fresh()->final_story ?? $case->narrative), 'salida' => null,
            'estado' => 'error', 'error' => mb_substr($exception->getMessage(), 0, 1000), 'duracion_ms' => null,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    $results[] = $entry;
}

echo json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR).PHP_EOL;
