<?php

use App\Models\InterviewAnswer;
use App\Models\Investigation;
use App\Models\InvestigationInterview;
use App\Models\OccupationalCase;
use App\Services\LmStudioFinalStoryGenerator;
use App\Services\StagedLmStudioInvestigationAnalyzer;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Collection;

require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$options = [];
foreach (array_slice($argv, 1) as $argument) {
    if (str_starts_with($argument, '--') && str_contains($argument, '=')) {
        [$key, $value] = explode('=', substr($argument, 2), 2);
        $options[$key] = $value;
    }
}

$model = trim((string) ($options['model'] ?? config('services.lm_studio.model')));
$runs = filter_var($options['runs'] ?? '1', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 5]]);
$stageLimit = strtolower((string) ($options['stage'] ?? 'all'));
if ($model === '' || $runs === false || ! in_array($stageLimit, ['all', 'relato', 'hechos', 'arbol', 'medidas'], true)) {
    fwrite(STDERR, "Uso: php scripts/run_mac_ai_benchmark.php --model=ID_LM_STUDIO --runs=1 [--stage=all|relato|hechos|arbol|medidas]\n");
    exit(2);
}

config(['services.lm_studio.timeout' => max(300, (int) config('services.lm_studio.timeout', 300))]);

/**
 * Crea un expediente enteramente en memoria. El benchmark no registra ni modifica casos, entrevistas o investigaciones.
 */
function benchmarkCase(): OccupationalCase
{
    $case = new OccupationalCase([
        'folio' => 'BENCHMARK-FICTICIO-01',
        'case_type' => 'Accidente de trabajo',
        'worker_name' => 'Diego Trabajador Ficticio',
        'job_title' => 'Auxiliar de farmacia',
        'event_date' => '2026-10-22',
        'event_time' => '15:40',
        'event_location' => 'Acceso a bodega de farmacia',
        'narrative' => 'Caso ficticio de evaluación, no corresponde a un accidente real. El 22 de octubre de 2026, cerca de las 15:40, Diego Trabajador Ficticio, auxiliar de farmacia, caminaba hacia la bodega de farmacia llevando una carpeta. En el acceso resbaló y cayó, golpeándose la rodilla derecha. Diego declaró que observó agua en el piso después de caer y que desconoce su origen. La revisión posterior del sector no permitió confirmar la condición del piso al momento del evento ni la presencia de señalización.',
        'witnesses' => '',
        'details' => ['ai_evidence_sources' => [
            ['id' => 'EVD-CCTV', 'source' => 'Registro de cámara de seguridad (simulado)', 'recorded_at' => '22-10-2026 15:38 a 15:42', 'facts' => 'El registro muestra a una persona ingresando al acceso a las 15:39:51 y cayendo a las 15:40:06. La cámara no permite establecer el origen de la humedad ni confirmar si había señalización.', 'limitations' => 'La imagen es simulada para la prueba; no permite establecer diagnóstico médico ni el origen del líquido.'],
            ['id' => 'EVD-ATENCION', 'source' => 'Registro de primeros auxilios (simulado)', 'recorded_at' => '22-10-2026 15:48', 'facts' => 'Se registró dolor referido en la rodilla derecha y derivación para evaluación. No se incluye diagnóstico.', 'limitations' => 'No permite inferir un diagnóstico ni gravedad clínica.'],
            ['id' => 'EVD-INSPECCION', 'source' => 'Acta de inspección posterior (simulada)', 'recorded_at' => '22-10-2026 16:05', 'facts' => 'Durante la inspección posterior no se observó líquido visible en el acceso. No se pudo determinar si el piso estaba húmedo a las 15:40 ni si había señalización en ese momento.', 'limitations' => 'La inspección fue posterior al accidente y no acredita las condiciones exactas al momento del evento.'],
        ]],
    ]);

    $interviews = [];
    $answersByWitness = [
        ['Diego Trabajador Ficticio', 'Auxiliar de farmacia', 'Trabajador afectado', [
            'event_sequence' => 'Cerca de las 15:40 caminaba hacia la bodega con una carpeta. Resbaló y cayó, golpeándose la rodilla derecha.',
            'environment_conditions' => 'Después de caer observó agua en el piso. No sabe de dónde provenía.',
            'injury_or_effect' => 'Refiere un golpe en la rodilla derecha. No informó un diagnóstico.',
        ]],
        ['Rocío Testigo Ficticia', 'Administrativa', 'Testigo indirecto', [
            'before_event' => 'Pasó por el acceso unos minutos antes y vio humedad en una parte del piso. No sabe de dónde provenía.',
            'event_sequence' => 'No presenció la caída. Escuchó un aviso después y llegó al acceso.',
            'after_event' => 'Al llegar, Diego estaba en el suelo. No observó cómo se golpeó.',
        ]],
        ['Matías Testigo Ficticio', 'Auxiliar de servicios', 'Testigo posterior', [
            'event_sequence' => 'Escuchó un ruido desde el pasillo y llegó después. Vio a Diego sentado en el piso junto al acceso.',
            'injury_or_effect' => 'Diego le dijo que le dolía la rodilla derecha. Matías no vio la caída.',
            'environment_conditions' => 'No inspeccionó el piso ni sabe si había señalización.',
        ]],
    ];

    foreach ($answersByWitness as [$name, $role, $type, $answers]) {
        $answerModels = [];
        foreach ($answers as $key => $value) {
            $answer = new InterviewAnswer(['question_key' => $key, 'value' => [$value]]);
            $answer->setRelation('question', null);
            $answerModels[] = $answer;
        }

        $interview = new InvestigationInterview([
            'interviewee_name' => $name,
            'interviewee_role' => $role,
            'witness_type' => $type,
        ]);
        $interview->setRelation('answers', new Collection($answerModels));
        $interviews[] = $interview;
    }

    $investigation = new Investigation;
    $investigation->setRelation('interviews', new Collection($interviews));
    $case->setRelation('investigation', $investigation);

    return $case;
}

/** @return list<array<string, string>> */
function benchmarkReviewedFacts(): array
{
    return [
        ['description' => 'Rocío observó humedad en una parte del acceso unos minutos antes del evento.', 'classification' => 'hecho', 'evidence_status' => 'declarado',
            'evidence' => 'Pasó por el acceso unos minutos antes y vio humedad en una parte del piso.', 'source' => 'Rocío Testigo Ficticia'],
        ['description' => 'Diego resbaló y cayó mientras caminaba por el acceso a la bodega.', 'classification' => 'hecho', 'evidence_status' => 'declarado',
            'evidence' => 'Cerca de las 15:40 caminaba hacia la bodega con una carpeta. Resbaló y cayó, golpeándose la rodilla derecha.', 'source' => 'Diego Trabajador Ficticio'],
        ['description' => 'Diego refirió un golpe en la rodilla derecha después de la caída.', 'classification' => 'hecho', 'evidence_status' => 'declarado',
            'evidence' => 'Refiere un golpe en la rodilla derecha. No informó un diagnóstico.', 'source' => 'Diego Trabajador Ficticio'],
    ];
}

/** @return array<string, mixed> */
function benchmarkReviewedTree(): array
{
    return ['final_node_id' => 'H3', 'nodes' => [
        ['id' => 'H2', 'type' => 'hecho', 'description' => 'Diego resbaló y cayó mientras caminaba por el acceso a la bodega.'],
        ['id' => 'H3', 'type' => 'hecho', 'description' => 'Diego refirió un golpe en la rodilla derecha después de la caída.'],
    ], 'edges' => [[
        'from' => 'H2', 'to' => 'H3', 'link_type' => 'causal', 'logic' => 'cadena',
        'basis' => 'La caída precede al golpe referido; el relato no permite afirmar diagnóstico médico.',
        'evidence' => 'Resbaló y cayó, golpeándose la rodilla derecha.',
    ]]];
}

/** @return array{story: string, facts: list<array<string, string>>, tree: array<string, mixed>} */
function benchmarkMeasuresScenario(): array
{
    $facts = [
        ['description' => 'Un cable de alimentación cruzaba descubierto el pasillo de tránsito.', 'classification' => 'permanente', 'evidence_status' => 'verificado',
            'evidence' => 'La inspección y el registro de cámara muestran un cable de alimentación cruzando descubierto el pasillo.', 'source' => 'Inspección y cámara simuladas'],
        ['description' => 'Diego tropezó con el cable y cayó al piso.', 'classification' => 'hecho', 'evidence_status' => 'declarado',
            'evidence' => 'Diego declaró: “Tropecé con el cable que cruzaba el pasillo y caí al piso.”', 'source' => 'Diego, entrevista simulada'],
        ['description' => 'Diego refirió dolor en la rodilla derecha después de la caída.', 'classification' => 'hecho', 'evidence_status' => 'declarado',
            'evidence' => 'Diego refirió dolor en la rodilla derecha después de la caída.', 'source' => 'Diego, entrevista simulada'],
    ];
    $tree = ['final_node_id' => 'H3', 'nodes' => [
        ['id' => 'H1', 'type' => 'permanente', 'description' => $facts[0]['description']],
        ['id' => 'H2', 'type' => 'hecho', 'description' => $facts[1]['description']],
        ['id' => 'H3', 'type' => 'hecho', 'description' => $facts[2]['description']],
    ], 'edges' => [
        ['from' => 'H1', 'to' => 'H2', 'link_type' => 'causal', 'logic' => 'cadena',
            'basis' => 'El cable descubierto cruzaba el pasillo y Diego declaró que tropezó con ese cable.',
            'evidence' => 'La inspección y el registro de cámara muestran un cable de alimentación cruzando descubierto el pasillo.'],
        ['from' => 'H2', 'to' => 'H3', 'link_type' => 'causal', 'logic' => 'cadena',
            'basis' => 'El dolor fue referido después de la caída; no se afirma diagnóstico médico.',
            'evidence' => 'Diego refirió dolor en la rodilla derecha después de la caída.'],
    ]];

    return ['story' => 'Caso ficticio de prueba: una inspección y una cámara muestran que un cable de alimentación cruzaba descubierto el pasillo. Diego declaró que tropezó con ese cable y cayó al piso. Después refirió dolor en la rodilla derecha; no se informó diagnóstico.',
        'facts' => $facts, 'tree' => $tree];
}

$generator = app(LmStudioFinalStoryGenerator::class);
$analyzer = app(StagedLmStudioInvestigationAnalyzer::class);
$reports = [];
$exitCode = 0;
$currentStage = 'relato';
$progress = static function (array $event): void {
    echo json_encode($event, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR).PHP_EOL;
    flush();
};

for ($run = 1; $run <= $runs; $run++) {
    $startedAt = microtime(true);
    $report = ['run' => $run, 'model' => $model, 'case' => 'BENCHMARK-FICTICIO-01', 'status' => 'running', 'stages' => []];
    $currentStage = $stageLimit === 'all' ? 'relato' : $stageLimit;
    $progress(['run' => $run, 'stage' => $currentStage, 'status' => 'started']);

    try {
        $case = benchmarkCase();
        if ($stageLimit === 'hechos') {
            $currentStage = 'hechos';
            $facts = $analyzer->facts($case->narrative, $model);
            $factText = mb_strtolower(implode(' ', array_column($facts['facts'], 'description')));
            $checks = [
                'caida y zona corporal' => preg_match('/cay[oó]|resbal[oó]/u', $factText) === 1 && str_contains($factText, 'rodilla'),
                'humedad sin atribuir origen' => preg_match('/humedad|agua/u', $factText) === 1,
                'no confirmado como pendiente' => $facts['pending_verifications'] !== [],
            ];
            $failed = array_keys(array_filter($checks, fn (bool $passed): bool => ! $passed));
            $report['stages']['hechos'] = ['status' => $failed === [] ? 'passed' : 'failed', 'count' => count($facts['facts']),
                'facts' => array_column($facts['facts'], 'description'), 'pending_verifications' => $facts['pending_verifications'],
                'quality_warnings' => $facts['quality_warnings'], 'checks' => $checks];
            if ($failed !== []) {
                throw new RuntimeException('La extracción omitió criterios esperados: '.implode(', ', $failed).'. Revise stages.hechos.facts en este informe.');
            }
            $report['status'] = 'passed';
        } elseif ($stageLimit === 'arbol') {
            $currentStage = 'arbol';
            $facts = benchmarkReviewedFacts();
            $tree = $analyzer->tree($case->narrative, $facts, $model);
            $hasFallToInjuryLink = collect($tree['tree']['edges'])->contains(fn (array $edge): bool => $edge['from'] === 'H2' && $edge['to'] === 'H3' && $edge['link_type'] === 'causal');
            $report['stages']['arbol'] = ['status' => 'passed', 'nodes' => count($tree['tree']['nodes']),
                'causal_edges' => count(array_filter($tree['tree']['edges'], fn (array $edge): bool => $edge['link_type'] === 'causal')),
                'apparent_edges' => count(array_filter($tree['tree']['edges'], fn (array $edge): bool => $edge['link_type'] === 'aparente')),
                'expected_fall_to_injury_link' => $hasFallToInjuryLink, 'warnings' => $tree['quality_warnings']];
            if (! $hasFallToInjuryLink) {
                $report['stages']['arbol']['status'] = 'failed';
                throw new RuntimeException('El árbol no representó el vínculo respaldado entre la caída y el golpe referido; revise stages.arbol.');
            }
            $report['status'] = 'passed';
        } elseif ($stageLimit === 'medidas') {
            $currentStage = 'medidas';
            $scenario = benchmarkMeasuresScenario();
            $candidateCodes = [];
            $batchIndex = 0;
            $batchCount = 1;
            while ($batchIndex < $batchCount) {
                $progress(['run' => $run, 'stage' => 'revision_matriz', 'status' => 'started', 'batch' => $batchIndex + 1, 'batches' => $batchCount]);
                $batch = $analyzer->screenFactorBatch($scenario['story'], $scenario['facts'], $scenario['tree'], $batchIndex, $model);
                $candidateCodes = [...$candidateCodes, ...$batch['candidate_codes']];
                $batchCount = $batch['total_batches'];
                $batchIndex++;
            }
            $report['stages']['revision_matriz'] = ['status' => 'completed', 'batches' => $batchCount,
                'candidate_codes' => array_values(array_unique($candidateCodes))];
            $progress(['run' => $run, 'stage' => 'medidas', 'status' => 'started']);
            $measures = $analyzer->measures($scenario['story'], $scenario['facts'], $scenario['tree'], array_values(array_unique($candidateCodes)), $model);
            $report['stages']['medidas'] = ['status' => $measures['measures'] === [] ? 'failed' : 'passed',
                'matrix_batches' => $batchCount, 'screened_factor_codes' => array_values(array_unique($candidateCodes)),
                'factor_codes' => $measures['factor_codes'], 'measure_count' => count($measures['measures']), 'measures' => $measures['measures'],
                'warnings' => $measures['quality_warnings']];
            if ($measures['measures'] === []) {
                throw new RuntimeException('No se propuso ninguna medida para el cable descubierto, condición causal verificada en este escenario. Revise stages.medidas.');
            }
            $report['status'] = 'passed';
        } else {
            $story = $generator->generate($case, $model);
            $usedSourceIds = collect($story['source_references'])->pluck('source_ids')->flatten()->all();
            $uniquePreEventWitnessIncluded = in_array('ENT-02', $usedSourceIds, true);
            $preEventObservationPosition = mb_stripos($story['story'], 'unos minutos antes');
            $accidentPosition = mb_stripos($story['story'], 'resbaló');
            $preEventObservationIsChronological = $preEventObservationPosition !== false
                && $accidentPosition !== false && $preEventObservationPosition < $accidentPosition;
            $report['stages']['relato'] = [
                'status' => $uniquePreEventWitnessIncluded && $preEventObservationIsChronological ? 'passed' : 'failed',
                'sources' => $story['sources_generated'],
                'interviews_used' => $story['interviews_used'],
                'paragraphs' => count($story['source_references']),
                'unique_pre_event_witness_included' => $uniquePreEventWitnessIncluded,
                'pre_event_observation_is_chronological' => $preEventObservationIsChronological,
                'story' => $story['story'],
                'source_references' => $story['source_references'],
                'omitted_sources' => $story['quality']['omitted_sources'],
                'warnings' => $story['quality_warnings'],
            ];

            if (! $uniquePreEventWitnessIncluded) {
                throw new RuntimeException('Se omitió la observación previa y no redundante de Rocío (ENT-02); el relato no conserva todos los datos pertinentes de las entrevistas.');
            }
            if (! $preEventObservationIsChronological) {
                throw new RuntimeException('La observación previa de Rocío no aparece antes de la caída en el relato; se invirtió la cronología.');
            }

            if ($stageLimit === 'relato') {
                $report['status'] = 'passed';
            } else {
                $currentStage = 'hechos';
                $progress(['run' => $run, 'stage' => $currentStage, 'status' => 'started']);
                $facts = $analyzer->facts($story['story'], $model);
                $factDescriptions = array_column($facts['facts'], 'description');
                $factText = mb_strtolower(implode(' ', $factDescriptions));
                $expectedFactChecks = [
                    'evento y consecuencia' => preg_match('/cay[oó]|resbal[oó]/u', $factText) === 1 && str_contains($factText, 'rodilla'),
                    'humedad atribuida sin inferir origen' => preg_match('/humedad|agua/u', $factText) === 1,
                    'vacíos de inspección separados de los hechos' => $facts['pending_verifications'] !== [],
                ];
                $failedFactChecks = array_keys(array_filter($expectedFactChecks, fn (bool $passed): bool => ! $passed));
                if ($failedFactChecks !== []) {
                    throw new RuntimeException('La extracción omitió criterios esperados: '.implode(', ', $failedFactChecks).'.');
                }
                $report['stages']['hechos'] = [
                    'status' => 'passed', 'count' => count($facts['facts']),
                    'pending_verifications' => $facts['pending_verifications'],
                    'quality_warnings' => $facts['quality_warnings'],
                ];

                $currentStage = 'arbol';
                $progress(['run' => $run, 'stage' => $currentStage, 'status' => 'started']);
                $tree = $analyzer->tree($story['story'], $facts['facts'], $model);
                $report['stages']['arbol'] = [
                    'status' => 'passed', 'nodes' => count($tree['tree']['nodes']),
                    'causal_edges' => count(array_filter($tree['tree']['edges'], fn (array $edge): bool => $edge['link_type'] === 'causal')),
                    'apparent_edges' => count(array_filter($tree['tree']['edges'], fn (array $edge): bool => $edge['link_type'] === 'aparente')),
                    'warnings' => $tree['quality_warnings'],
                ];

                $candidateCodes = [];
                $batchIndex = 0;
                $batchCount = 1;
                while ($batchIndex < $batchCount) {
                    $currentStage = 'revision_matriz';
                    $progress(['run' => $run, 'stage' => $currentStage, 'status' => 'started', 'batch' => $batchIndex + 1, 'batches' => $batchCount]);
                    $batch = $analyzer->screenFactorBatch($story['story'], $facts['facts'], $tree['tree'], $batchIndex, $model);
                    $candidateCodes = [...$candidateCodes, ...$batch['candidate_codes']];
                    $batchCount = $batch['total_batches'];
                    $batchIndex++;
                }

                $currentStage = 'medidas';
                $progress(['run' => $run, 'stage' => $currentStage, 'status' => 'started']);
                $measures = $analyzer->measures($story['story'], $facts['facts'], $tree['tree'], array_values(array_unique($candidateCodes)), $model);
                $report['stages']['medidas'] = [
                    'status' => 'passed', 'matrix_batches' => $batchCount,
                    'factor_codes' => $measures['factor_codes'], 'measure_count' => count($measures['measures']),
                    'warnings' => $measures['quality_warnings'],
                ];
                $report['status'] = 'passed';
            }
        }
    } catch (Throwable $exception) {
        $exitCode = 1;
        $report['status'] = 'failed';
        $report['error'] = $exception->getMessage();
        $report['failed_stage'] = $currentStage;
    }

    $report['duration_seconds'] = round(microtime(true) - $startedAt, 2);
    $reports[] = $report;
    echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR).PHP_EOL;
    flush();
}

echo json_encode([
    'summary' => ['model' => $model, 'runs' => $runs, 'passed' => count(array_filter($reports, fn (array $report): bool => $report['status'] === 'passed')),
        'failed' => count(array_filter($reports, fn (array $report): bool => $report['status'] !== 'passed')),
        'writes_to_case_or_investigation_tables' => false],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR).PHP_EOL;

exit($exitCode);
