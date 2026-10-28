<?php

use App\Models\OccupationalCase;
use App\Services\StagedLmStudioInvestigationAnalyzer;
use Illuminate\Contracts\Console\Kernel;

require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$analyzer = app(StagedLmStudioInvestigationAnalyzer::class);
$folios = array_slice($argv, 1) ?: ['GIATEP-2026-012', 'GIATEP-2026-013'];

foreach ($folios as $folio) {
    $case = OccupationalCase::query()->where('folio', $folio)->first();
    if (! $case?->investigation?->final_story) {
        echo json_encode(['case' => $folio, 'error' => 'No existe el caso de prueba o no tiene relato final guardado.'], JSON_UNESCAPED_UNICODE).PHP_EOL;

        continue;
    }

    $started = microtime(true);
    echo json_encode(['case' => $folio, 'stage' => 'iniciando', 'worker' => $case->worker_name], JSON_UNESCAPED_UNICODE).PHP_EOL;
    if (function_exists('ob_flush')) {
        @ob_flush();
    }
    flush();

    try {
        $facts = $analyzer->facts($case->investigation->final_story);
        echo json_encode(['case' => $folio, 'stage' => 'hechos', 'elapsed_seconds' => round(microtime(true) - $started, 1), 'facts' => $facts['facts']], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES).PHP_EOL;
        if (function_exists('ob_flush')) {
            @ob_flush();
        }
        flush();

        $tree = $analyzer->tree($case->investigation->final_story, $facts['facts']);
        echo json_encode(['case' => $folio, 'stage' => 'arbol', 'elapsed_seconds' => round(microtime(true) - $started, 1), 'tree' => $tree['tree']], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES).PHP_EOL;
        if (function_exists('ob_flush')) {
            @ob_flush();
        }
        flush();

        $candidateCodes = [];
        $batchIndex = 0;
        $totalBatches = 1;
        while ($batchIndex < $totalBatches) {
            echo json_encode(['case' => $folio, 'stage' => 'revision_matriz', 'batch' => $batchIndex + 1, 'batches' => $totalBatches], JSON_UNESCAPED_UNICODE).PHP_EOL;
            if (function_exists('ob_flush')) {
                @ob_flush();
            }
            flush();
            $batch = $analyzer->screenFactorBatch($case->investigation->final_story, $facts['facts'], $tree['tree'], $batchIndex);
            $candidateCodes = array_merge($candidateCodes, $batch['candidate_codes']);
            $totalBatches = $batch['total_batches'];
            $batchIndex++;
        }
        $measures = $analyzer->measures($case->investigation->final_story, $facts['facts'], $tree['tree'], array_values(array_unique($candidateCodes)));
        echo json_encode([
            'case' => $folio, 'stage' => 'medidas', 'elapsed_seconds' => round(microtime(true) - $started, 1),
            'factor_codes' => $measures['factor_codes'], 'factors' => $measures['factors'], 'measures' => $measures['measures'],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES).PHP_EOL;
    } catch (Throwable $exception) {
        echo json_encode(['case' => $folio, 'stage' => 'fallida', 'elapsed_seconds' => round(microtime(true) - $started, 1), 'error' => $exception->getMessage()], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES).PHP_EOL;
    }
}
