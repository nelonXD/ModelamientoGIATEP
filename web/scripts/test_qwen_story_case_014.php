<?php

use App\Models\OccupationalCase;
use App\Services\LmStudioFinalStoryGenerator;
use Illuminate\Contracts\Console\Kernel;

require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$case = OccupationalCase::query()->with('investigation.interviews.answers.question')->where('folio', 'GIATEP-2026-014')->firstOrFail();
try {
    $result = app(LmStudioFinalStoryGenerator::class)->generate($case, 'qwen2.5-coder-7b-instruct');
    echo json_encode(['ok' => true, 'model' => $result['model'], 'story' => $result['story'], 'source_references' => array_map(fn (array $reference): array => ['paragraph' => $reference['paragraph'], 'source_ids' => $reference['source_ids']], $result['source_references'])], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
} catch (Throwable $exception) {
    echo json_encode(['ok' => false, 'error' => $exception->getMessage()], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
}
