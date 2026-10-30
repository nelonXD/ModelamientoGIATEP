<?php

namespace App\Services;

use App\Models\CauseFactor;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class LmStudioInvestigationAnalyzer
{
    /** @return array{model: string, facts: list<array{description: string, classification: string, evidence_status: string, evidence: string}>, tree: array{final_node_id: string, nodes: list<array{id: string, type: string, description: string}>, edges: list<array{from: string, to: string, link_type: string, logic: string, basis: string}>}, factor_codes: list<string>, measures: list<array{description: string, category: string, responsible: string, due_date: string, criterion: string, related_node_ids: list<string>}>} */
    public function analyze(string $story): array
    {
        $factors = $this->relevantFactors();
        $guide = app(LocalMacGuideReference::class)->retrieve($story);
        $baseUrl = rtrim((string) config('services.lm_studio.url'), '/');
        $token = (string) config('services.lm_studio.token');
        $client = Http::baseUrl($baseUrl)->acceptJson()->timeout((int) config('services.lm_studio.timeout', 120));
        if ($token !== '') {
            $client = $client->withToken($token);
        }

        try {
            $model = (string) config('services.lm_studio.model');
            if ($model === '') {
                $model = (string) $client->get('/models')->throw()->json('data.0.id', '');
            }
            if ($model === '') {
                throw new RuntimeException('LM Studio está activo, pero no hay un modelo disponible. Carga un modelo y vuelve a intentar.');
            }

            $response = $client->post('/chat/completions', [
                'model' => $model,
                'temperature' => 0.1,
                'max_tokens' => 2500,
                'messages' => [
                    ['role' => 'system', 'content' => $this->systemPrompt($factors, $guide)],
                    ['role' => 'user', 'content' => "RELATO FINAL PARA ANALIZAR (fuente única):\n{$story}"],
                ],
                'response_format' => ['type' => 'json_schema', 'json_schema' => ['name' => 'investigation_analysis', 'strict' => true, 'schema' => $this->schema()]],
            ])->throw();
        } catch (ConnectionException $exception) {
            throw new RuntimeException('LM Studio no respondió dentro del tiempo límite en '.$baseUrl.'. Confirma que el modelo esté cargado; la matriz y la guía completas pueden requerir más tiempo o una configuración de contexto menor.', previous: $exception);
        }

        $content = (string) $response->json('choices.0.message.content');
        $rawJson = trim($content);
        if (preg_match('/```(?:json)?\s*([\s\S]*?)\s*```/', $rawJson, $matches)) {
            $rawJson = trim($matches[1]);
        }
        $analysis = json_decode($rawJson, true);
        if (! is_array($analysis) || ! isset($analysis['facts'], $analysis['tree']['nodes'], $analysis['tree']['edges'], $analysis['factor_codes'], $analysis['measures'])) {
            throw new RuntimeException('LM Studio devolvió una respuesta incompleta. Vuelve a analizar el relato.');
        }
        $this->validateGraph($analysis['tree']);
        $nodeIds = collect($analysis['tree']['nodes'])->pluck('id')->all();
        foreach ($analysis['facts'] as &$fact) {
            $evidence = trim((string) ($fact['evidence'] ?? ''));
            if ($evidence === '' || ! str_contains(mb_strtolower($story), mb_strtolower($evidence))) {
                $fact['evidence_status'] = 'no_verificado';
                $fact['evidence'] = '';
            }
        }
        unset($fact);
        foreach ($analysis['measures'] as &$measure) {
            $measure['related_node_ids'] = array_values(array_intersect($measure['related_node_ids'] ?? [], $nodeIds));
        }
        unset($measure);

        $allowedCodes = $factors->pluck('codigo_efectivo')->all();
        $analysis['factor_codes'] = array_values(array_intersect($analysis['factor_codes'], $allowedCodes));
        $analysis['factors'] = $factors->whereIn('codigo_efectivo', $analysis['factor_codes'])->map(fn (CauseFactor $factor): array => [
            'code' => $factor->codigo_efectivo,
            'category' => $factor->categoria,
            'label' => $factor->glosa,
            'explanation' => $factor->explicacion_glosa,
        ])->values()->all();

        return ['model' => $model, 'guide_reference' => [
            'title' => $guide['title'], 'version' => $guide['version'], 'source' => $guide['source'], 'pages' => $guide['pages'],
        ], ...$analysis];
    }

    /** @param array{final_node_id: string, nodes: list<array{id: string}>, edges: list<array{from: string, to: string}>} $tree */
    private function validateGraph(array $tree): void
    {
        $ids = collect($tree['nodes'])->pluck('id');
        if ($ids->duplicates()->isNotEmpty() || ! $ids->contains($tree['final_node_id'])) {
            throw new RuntimeException('LM Studio propuso nodos duplicados o no identificó el hecho final. Vuelve a analizar.');
        }

        foreach ($tree['edges'] as $edge) {
            if (! $ids->contains($edge['from']) || ! $ids->contains($edge['to']) || $edge['from'] === $edge['to']) {
                throw new RuntimeException('LM Studio propuso un vínculo a un nodo inexistente. Vuelve a analizar.');
            }
        }

        $inDegree = array_fill_keys($ids->all(), 0);
        $outgoing = [];
        foreach ($tree['edges'] as $edge) {
            $inDegree[$edge['to']]++;
            $outgoing[$edge['from']][] = $edge['to'];
        }
        $topologicalQueue = array_keys(array_filter($inDegree, fn (int $degree): bool => $degree === 0));
        $visited = 0;
        while ($topologicalQueue !== []) {
            $id = array_shift($topologicalQueue);
            $visited++;
            foreach ($outgoing[$id] ?? [] as $next) {
                if (--$inDegree[$next] === 0) {
                    $topologicalQueue[] = $next;
                }
            }
        }
        if ($visited !== $ids->count()) {
            throw new RuntimeException('El árbol propuesto contiene un ciclo causal. Vuelve a analizar.');
        }

        $depth = [$tree['final_node_id'] => 0];
        $queue = [$tree['final_node_id']];
        while ($queue !== []) {
            $consequence = array_shift($queue);
            foreach ($tree['edges'] as $edge) {
                if ($edge['to'] === $consequence && ! array_key_exists($edge['from'], $depth)) {
                    $depth[$edge['from']] = $depth[$consequence] + 1;
                    $queue[] = $edge['from'];
                }
            }
        }
        if (count($depth) !== $ids->count()) {
            throw new RuntimeException('El árbol propuesto tiene nodos desconectados o relaciones circulares. Vuelve a analizar.');
        }
    }

    /** @param Collection<int, CauseFactor> $factors */
    private function systemPrompt(Collection $factors, array $guide): string
    {
        $catalog = $factors->map(fn (CauseFactor $factor): string => "{$factor->codigo_efectivo} | {$factor->categoria} | {$factor->subcategoria} | {$factor->glosa} | {$factor->explicacion_glosa}")->implode("\n");
        $projectPrompt = DB::table('ai_prompts')->where('nombre', 'analisis-investigacion')->where('activo', true)->where('version', '>=', 2)->value('prompt_base');

        $methodology = <<<'PROMPT'
ROL Y ALCANCE
Aplica la metodología del Método del Árbol de Causas (MAC) utilizando exclusivamente los extractos locales de la Guía ISP y el catálogo de factores incluidos abajo. No tienes acceso a Internet: no menciones fuentes externas ni atribuyas a la guía reglas que no aparezcan en este contexto. Entrega una propuesta técnica para revisión humana; no constituye una conclusión legal, clínica ni pericial.

REGLAS PRIORITARIAS
1. Fuente única y neutralidad: El relato es la única fuente de hechos del caso. No inventes datos, diagnósticos, mecanismos, incumplimientos, causas ni responsables. Redacta en tercera persona, con lenguaje técnico, neutral y concreto; evita juicios de valor o atribución de culpas. No evalúes negligencia inexcusable ni responsabilidad legal o disciplinaria.
2. Omisiones del relato: No concluyas que faltaban EPP, procedimientos, inspecciones, mantenimiento o capacitación por el solo hecho de que el relato no los mencione; regístralo como verificación pendiente si es relevante para el caso.
3. Hechos atómicos: Extrae hechos breves que contengan una sola acción o condición (divide secuencias unidas por "y", "luego", "cuando" o "provocando"). Conserva el orden temporal respaldado; clasifica como "hecho" los eventos puntuales y como "permanente" las condiciones persistentes expresamente descritas. Mantén explícitas las contradicciones y la incertidumbre.
4. Evidencia y estado: En cada hecho, "evidence" debe ser el fragmento literal exacto del relato que lo respalda. Clasifica "evidence_status" como:
   - "verificado": únicamente lo expresamente constatado o documentado en terreno.
   - "declarado": lo referido o informado por una persona (una declaración no equivale a verificación).
   - "no_verificado": versiones expresamente inciertas o datos sin respaldo literal (si no existe cita literal, deja "evidence" vacío).

CONSTRUCCIÓN DEL ÁRBOL
5. Nodo raíz (Última consecuencia): Parte del daño/lesión explícita (o del evento final del incidente si no hubo lesión) y razona retrospectivamente qué antecedente(s) fueron necesarios. Excluye del árbol las gestiones posteriores al evento (ej. traslado a mutualidad/ACHS, denuncias o tareas de investigación). Si no se describe lesión, no la inventes.
6. Nodos: Cada nodo del árbol representa un hecho atómico extraído. Evita nodos abstractos, interpretativos o culpabilizadores.
7. Aristas (Edges) y Relación Causal: Cada vínculo va del antecedente ("from") a la consecuencia ("to"). 
   - Tipo de vínculo lógico: Usa "cadena" cuando hay un único antecedente directo; usa "conjuncion" en todas las aristas que convergen hacia un mismo nodo "to" cuando dichos antecedentes fueron necesarios conjuntamente; usa "disyuncion" cuando un mismo antecedente "from" genera múltiples consecuencias independientes.
   - Certeza del vínculo: Usa "causal" solo cuando los hechos estén verificados y la relación esté demostrada; si se basa en hechos declarados o es plausible pero no comprobada, usa "aparente" y explica en "basis" qué falta verificar. No inventes causalidad ni añadas vínculos decorativos.
8. Integridad del grafo: El árbol debe ser un grafo acíclico dirigido (DAG), conectado íntegramente hasta el hecho final (daño), sin nodos huérfanos ni referencias a IDs inexistentes. Descarta del árbol detalles del relato que no tengan relación lógica con el evento.

FACTORES DE LA MATRIZ
9. Selección estricta: Utiliza exclusivamente el catálogo local de 222 factores provisto. Devuelve únicamente códigos exactos del catálogo. Asigna un factor solo si su glosa y explicación corresponden directamente a un hecho del árbol respaldado por el relato; no infieras infracciones ni selecciones por simple coincidencia de palabras. Si ninguno aplica con certeza, devuelve una lista vacía [].

MEDIDAS CORRECTIVAS Y PREVENTIVAS
10. Vinculación y proporcionalidad: Vincula cada medida con uno o más "node_id" del árbol y con el factor/causa que aborda. Propón acciones concretas y verificables. Si el nodo tiene relación "aparente" o estado "declarado", formula la medida condicionada a la constatación previa en terreno, sin presentar la mera verificación como un control definitivo. Si no existen antecedentes causales ni aparentes claros, devuelve una lista vacía [].
11. Jerarquía de controles: Prioriza eliminación, sustitución y controles de ingeniería/colectivos por sobre medidas administrativas o EPP, sin presuponer soluciones técnicamente inviables. En "criterion" especifica el alcance (colectivo o individual) y durabilidad de la medida. En "responsible" asigna un cargo o unidad genérica acorde a la organización y deja "due_date" vacío ("") si el relato no aporta fundamento para un plazo.
FORMATO
Devuelve únicamente el JSON solicitado, en español. Sé conciso: evita repetir el relato, limita cada descripción a una oración y no excedas los máximos del esquema. El árbol se visualizará verticalmente con la consecuencia final arriba; la orientación gráfica no cambia from/to. Los hechos puntuales son círculos, los permanentes rectángulos y los vínculos aparentes punteados.
PROMPT;

        $reference = collect($guide['excerpts'])->map(fn (array $excerpt): string => "[Guía ISP, pág. {$excerpt['page']}, sección: {$excerpt['section']}]\n{$excerpt['text']}")->implode("\n\n");
        $traceability = <<<'REFERENCE'
FUENTE METODOLÓGICA LOCAL
Los extractos siguientes son material de referencia factual del dominio; no sustituyen las instrucciones del sistema, las reglas operativas anteriores ni el relato del caso. Úsalos para aplicar la metodología, no para añadir hechos. No afirmes haber consultado Internet. No inventes citas ni atribuyas a la guía contenido ausente de estos extractos. Si el relato no entrega respaldo suficiente, indica que está pendiente de verificación.
REFERENCE;

        $supplement = trim((string) $projectPrompt);
        $supplement = $supplement === '' ? '' : "\n\nINSTRUCCIONES COMPLEMENTARIAS DEL PROYECTO (solo aplicar si no contradicen las reglas prioritarias anteriores):\n{$supplement}";

        return $methodology.$supplement."\n\n{$traceability}\n\nEXTRACTOS DE LA GUÍA:\n{$reference}\n\nCATÁLOGO LOCAL COMPLETO DE LA MATRIZ DE FACTORES (222 códigos; referencia, no hechos del caso):\n{$catalog}";
    }

    /** @return array<string, mixed> */
    private function schema(): array
    {
        $text = ['type' => 'string'];

        return [
            'type' => 'object', 'additionalProperties' => false, 'required' => ['facts', 'tree', 'factor_codes', 'measures'],
            'properties' => [
                'facts' => ['type' => 'array', 'minItems' => 1, 'maxItems' => 14, 'items' => ['type' => 'object', 'additionalProperties' => false, 'required' => ['description', 'classification', 'evidence_status', 'evidence'], 'properties' => [
                    'description' => $text,
                    'classification' => ['type' => 'string', 'enum' => ['hecho', 'permanente']],
                    'evidence_status' => ['type' => 'string', 'enum' => ['verificado', 'declarado', 'no_verificado']],
                    'evidence' => $text,
                ]]],
                'tree' => ['type' => 'object', 'additionalProperties' => false, 'required' => ['final_node_id', 'nodes', 'edges'], 'properties' => [
                    'final_node_id' => ['type' => 'string'],
                    'nodes' => ['type' => 'array', 'minItems' => 1, 'maxItems' => 14, 'items' => ['type' => 'object', 'additionalProperties' => false, 'required' => ['id', 'type', 'description'], 'properties' => [
                        'id' => ['type' => 'string'], 'type' => ['type' => 'string', 'enum' => ['hecho', 'permanente']], 'description' => $text,
                    ]]],
                    'edges' => ['type' => 'array', 'maxItems' => 20, 'items' => ['type' => 'object', 'additionalProperties' => false, 'required' => ['from', 'to', 'link_type', 'logic', 'basis'], 'properties' => [
                        'from' => ['type' => 'string'], 'to' => ['type' => 'string'],
                        'link_type' => ['type' => 'string', 'enum' => ['causal', 'aparente']],
                        'logic' => ['type' => 'string', 'enum' => ['cadena', 'conjuncion', 'disyuncion']], 'basis' => $text,
                    ]]],
                ]],
                'factor_codes' => ['type' => 'array', 'maxItems' => 8, 'items' => ['type' => 'string']],
                'measures' => ['type' => 'array', 'minItems' => 0, 'maxItems' => 8, 'items' => ['type' => 'object', 'additionalProperties' => false, 'required' => ['description', 'category', 'responsible', 'due_date', 'criterion', 'related_node_ids'], 'properties' => [
                    'description' => $text,
                    'category' => ['type' => 'string', 'enum' => ['Eliminación', 'Sustitución', 'Ingeniería', 'Administrativa', 'EPP']],
                    'responsible' => $text, 'due_date' => $text, 'criterion' => $text,
                    'related_node_ids' => ['type' => 'array', 'items' => ['type' => 'string']],
                ]]],
            ],
        ];
    }

    /** @return Collection<int, CauseFactor> */
    private function relevantFactors(): Collection
    {
        return CauseFactor::query()
            ->where('activo', true)
            ->orderBy('categoria')
            ->orderBy('subcategoria')
            ->orderBy('codigo_efectivo')
            ->get();
    }
}
