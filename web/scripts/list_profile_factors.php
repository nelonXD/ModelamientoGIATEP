<?php

use App\Models\CauseFactor;
use Illuminate\Contracts\Console\Kernel;

require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$factors = CauseFactor::query()->where('activo', true)->get()->filter(function (CauseFactor $factor): bool {
    return preg_match('/piso|superficie|desnivel|irregular|obst[aá]culo|pasillo|circulaci[oó]n|ca[ií]da|tr[aá]nsito|escal[oó]n/iu', $factor->glosa.' '.$factor->explicacion_glosa) === 1;
})->map(fn (CauseFactor $factor): array => ['code' => $factor->codigo_efectivo, 'category' => $factor->categoria, 'subcategory' => $factor->subcategoria, 'label' => $factor->glosa, 'explanation' => $factor->explicacion_glosa])->values();
echo json_encode($factors, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
