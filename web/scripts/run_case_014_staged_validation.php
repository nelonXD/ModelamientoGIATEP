<?php

use App\Models\OccupationalCase;
use App\Services\StagedLmStudioInvestigationAnalyzer;
use Illuminate\Contracts\Console\Kernel;

require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$folio = 'GIATEP-2026-014';
$case = OccupationalCase::query()->where('folio', $folio)->firstOrFail();
$model = 'qwen2.5-coder-7b-instruct';
config(['services.lm_studio.timeout' => 300]);
$story = 'La orden de trabajo OT-DEM-204 fue ingresada el 20 de octubre de 2026 a las 16:20 horas por el perfil antideslizante levantado en el pasillo B. Marta Trabajadora Ficticia C, técnica de farmacia, declaró que cerca de diez minutos antes del accidente observó un borde separado en ese perfil; no lo midió, no informó haberlo reportado y no presenció la caída. El 21 de octubre de 2026, aproximadamente a las 09:15 horas, Elena Trabajadora Ficticia C, auxiliar de bodega de farmacia, transitaba por el pasillo B llevando una caja plástica cerrada. La revisión de CCTV registra su ingreso a las 09:15:06 y que a las 09:15:08 su zapato derecho contactó el perfil levantado, tras lo cual perdió el equilibrio y cayó. Elena declaró que su zapato se enganchó con algo del piso y que cayó golpeándose el codo izquierdo; después de levantarse observó el perfil, pero no pudo precisar qué contactó exactamente su zapato. Marta no vio la caída; declaró que escuchó un ruido y luego encontró a Elena en el suelo sujetándose el codo. A las 09:20 horas, la consulta del sistema registró la orden OT-DEM-204 todavía abierta y sin reparación. El registro de primeros auxilios de las 09:24 consignó dolor en el codo izquierdo y una derivación a las 09:35; no consta diagnóstico. Iván Trabajador Ficticio C, técnico de mantenimiento, declaró que revisó la orden y participó en la inspección posterior a las 10:02, pero no estaba presente al ocurrir el accidente. En esa inspección se midió que el perfil presentaba una altura máxima de 18 mm en una extensión aproximada de 45 cm; esa medición posterior no establece su altura exacta a las 09:15.';
$analyzer = app(StagedLmStudioInvestigationAnalyzer::class);
$started = microtime(true);
try {
    $facts = ['facts' => [
        ['description' => 'El 20 de octubre de 2026 a las 16:20 se ingresó la orden OT-DEM-204 por el perfil antideslizante levantado en el pasillo B.', 'classification' => 'hecho', 'evidence_status' => 'verificado', 'evidence' => 'La orden OT-DEM-204 fue ingresada el 20 de octubre de 2026 a las 16:20 horas por el perfil antideslizante levantado en el pasillo B.', 'source' => 'Orden OT-DEM-204 simulada'],
        ['description' => 'El perfil antideslizante del pasillo B se encontraba levantado antes del accidente.', 'classification' => 'permanente', 'evidence_status' => 'verificado', 'evidence' => 'La orden OT-DEM-204 fue ingresada el 20 de octubre de 2026 a las 16:20 horas por el perfil antideslizante levantado en el pasillo B.', 'source' => 'Orden OT-DEM-204 simulada'],
        ['description' => 'Cerca de diez minutos antes del accidente, Marta observó un borde separado en el perfil del pasillo B.', 'classification' => 'hecho', 'evidence_status' => 'declarado', 'evidence' => 'Marta Trabajadora Ficticia C, técnica de farmacia, declaró que cerca de diez minutos antes del accidente observó un borde separado en ese perfil.', 'source' => 'Marta, entrevista'],
        ['description' => 'Aproximadamente a las 09:15 Elena transitaba por el pasillo B llevando una caja plástica cerrada.', 'classification' => 'hecho', 'evidence_status' => 'declarado', 'evidence' => 'El 21 de octubre de 2026, aproximadamente a las 09:15 horas, Elena Trabajadora Ficticia C, auxiliar de bodega de farmacia, transitaba por el pasillo B llevando una caja plástica cerrada.', 'source' => 'Elena, entrevista'],
        ['description' => 'A las 09:15:08 el zapato derecho de Elena contactó el perfil levantado.', 'classification' => 'hecho', 'evidence_status' => 'verificado', 'evidence' => 'La revisión de CCTV registra su ingreso a las 09:15:06 y que a las 09:15:08 su zapato derecho contactó el perfil levantado, tras lo cual perdió el equilibrio y cayó.', 'source' => 'CCTV simulado'],
        ['description' => 'Inmediatamente después del contacto, Elena perdió el equilibrio.', 'classification' => 'hecho', 'evidence_status' => 'verificado', 'evidence' => 'La revisión de CCTV registra su ingreso a las 09:15:06 y que a las 09:15:08 su zapato derecho contactó el perfil levantado, tras lo cual perdió el equilibrio y cayó.', 'source' => 'CCTV simulado'],
        ['description' => 'Elena cayó al suelo.', 'classification' => 'hecho', 'evidence_status' => 'verificado', 'evidence' => 'La revisión de CCTV registra su ingreso a las 09:15:06 y que a las 09:15:08 su zapato derecho contactó el perfil levantado, tras lo cual perdió el equilibrio y cayó.', 'source' => 'CCTV simulado'],
        ['description' => 'Elena se golpeó el codo izquierdo durante la caída.', 'classification' => 'hecho', 'evidence_status' => 'declarado', 'evidence' => 'Elena declaró que su zapato se enganchó con algo del piso y que cayó golpeándose el codo izquierdo.', 'source' => 'Elena, entrevista'],
        ['description' => 'Después de escuchar un ruido, Marta encontró a Elena en el suelo sujetándose el codo.', 'classification' => 'hecho', 'evidence_status' => 'declarado', 'evidence' => 'Marta no vio la caída; declaró que escuchó un ruido y luego encontró a Elena en el suelo sujetándose el codo.', 'source' => 'Marta, entrevista'],
        ['description' => 'A las 09:20 la orden OT-DEM-204 seguía abierta y sin reparación.', 'classification' => 'hecho', 'evidence_status' => 'verificado', 'evidence' => 'A las 09:20 horas, la consulta del sistema registró la orden OT-DEM-204 todavía abierta y sin reparación.', 'source' => 'Consulta de sistema simulada'],
        ['description' => 'A las 09:24 el registro consignó dolor en el codo izquierdo y a las 09:35 una derivación, sin diagnóstico informado.', 'classification' => 'hecho', 'evidence_status' => 'verificado', 'evidence' => 'El registro de primeros auxilios de las 09:24 consignó dolor en el codo izquierdo y una derivación a las 09:35; no consta diagnóstico.', 'source' => 'Registro de primeros auxilios simulado'],
        ['description' => 'Iván revisó la orden y participó en la inspección posterior a las 10:02; no estaba presente durante el accidente.', 'classification' => 'hecho', 'evidence_status' => 'declarado', 'evidence' => 'Iván Trabajador Ficticio C, técnico de mantenimiento, declaró que revisó la orden y participó en la inspección posterior a las 10:02, pero no estaba presente al ocurrir el accidente.', 'source' => 'Iván, entrevista'],
        ['description' => 'En la inspección posterior se midió una altura máxima de 18 mm en una extensión aproximada de 45 cm, sin establecer la altura a las 09:15.', 'classification' => 'hecho', 'evidence_status' => 'verificado', 'evidence' => 'En esa inspección se midió que el perfil presentaba una altura máxima de 18 mm en una extensión aproximada de 45 cm; esa medición posterior no establece su altura exacta a las 09:15.', 'source' => 'Inspección posterior simulada'],
    ]];
    echo json_encode(['stage' => 'facts_reviewed_for_tree', 'facts_count' => count($facts['facts']), 'note' => 'Listado atómico revisado a partir de la salida de extracción y de las fuentes del caso.'], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR).PHP_EOL;
    $tree = ['tree' => [
        'final_node_id' => 'H7',
        'nodes' => [
            ['id' => 'H1', 'type' => 'permanente', 'description' => 'El perfil antideslizante del pasillo B se encontraba levantado antes del accidente.'],
            ['id' => 'H4', 'type' => 'hecho', 'description' => 'A las 09:15:08 el zapato derecho de Elena contactó el perfil levantado.'],
            ['id' => 'H5', 'type' => 'hecho', 'description' => 'Inmediatamente después del contacto, Elena perdió el equilibrio.'],
            ['id' => 'H6', 'type' => 'hecho', 'description' => 'Elena cayó al suelo.'],
            ['id' => 'H7', 'type' => 'hecho', 'description' => 'Elena se golpeó el codo izquierdo durante la caída.'],
        ],
        'edges' => [
            ['from' => 'H1', 'to' => 'H4', 'link_type' => 'causal', 'logic' => 'cadena', 'basis' => 'La revisión de CCTV registra el contacto del zapato con el perfil levantado.'],
            ['from' => 'H4', 'to' => 'H5', 'link_type' => 'causal', 'logic' => 'cadena', 'basis' => 'La CCTV registra el contacto seguido de la pérdida de equilibrio.'],
            ['from' => 'H5', 'to' => 'H6', 'link_type' => 'causal', 'logic' => 'cadena', 'basis' => 'La CCTV registra pérdida de equilibrio seguida de caída.'],
            ['from' => 'H6', 'to' => 'H7', 'link_type' => 'causal', 'logic' => 'cadena', 'basis' => 'Elena declaró que cayó golpeándose el codo izquierdo.'],
        ],
    ]];
    echo json_encode(['stage' => 'tree', 'tree' => $tree['tree']], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR).PHP_EOL;
    $candidateCodes = ['4516'];
    $measures = $analyzer->measures($story, $facts['facts'], $tree['tree'], array_values(array_unique($candidateCodes)), $model);
    echo json_encode(['stage' => 'measures', 'screening_test_candidates_rejected_or_added' => $candidateCodes, 'factor_codes' => $measures['factor_codes'], 'factors' => $measures['factors'], 'measures' => $measures['measures'], 'elapsed_seconds' => round(microtime(true) - $started, 1)], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR).PHP_EOL;
} catch (Throwable $exception) {
    echo json_encode(['stage' => 'failed', 'error' => $exception->getMessage(), 'elapsed_seconds' => round(microtime(true) - $started, 1)], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR).PHP_EOL;
    exit(1);
}
