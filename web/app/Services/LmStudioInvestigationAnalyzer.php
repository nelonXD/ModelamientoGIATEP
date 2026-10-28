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
Aplica la metodología MAC con los extractos locales de la Guía ISP incluidos abajo. No tienes Internet: no digas que consultaste fuentes externas ni atribuyas a la guía reglas que no aparezcan aquí. Entrega una propuesta técnica para revisión humana; no es una conclusión legal ni pericial.

REGLAS PRIORITARIAS
1. El relato es la única fuente de hechos del caso. No inventes datos, diagnósticos, mecanismos, incumplimientos, causas ni responsables. Redacta en tercera persona, con lenguaje neutral y concreto; evita culpas o juicios de valor. No evalúes ni determines negligencia inexcusable, culpabilidad ni responsabilidad legal o disciplinaria.
2. No concluyas que faltaban EPP, procedimientos, inspecciones, mantenimiento o capacitación por el solo hecho de que el relato no los mencione; indica qué debe verificarse si es relevante.
3. Extrae hechos atómicos y breves. Conserva el orden temporal solo cuando esté respaldado; incorpora condiciones persistentes como “permanente”. Mantén explícitas las contradicciones y la incertidumbre.
4. En cada hecho, “evidence” debe ser un fragmento literal breve del relato. Clasifica como “verificado” únicamente lo expresamente constatado/documentado, “declarado” lo informado por una persona y “no_verificado” lo que requiere comprobación. Una declaración no equivale a verificación. Si no existe cita literal, deja evidence vacío y usa no_verificado.

CONSTRUCCIÓN DEL ÁRBOL
5. Parte del daño o última consecuencia explícita y razona retrospectivamente qué antecedente(s) fueron necesarios. Si no se describe lesión, no la inventes.
6. Un nodo expresa un solo hecho: “hecho” es evento puntual; “permanente” es condición persistente. Evita nodos abstractos o culpabilizadores.
7. Cada edge va del antecedente (from) a la consecuencia (to). Usa “cadena” para un antecedente, “conjuncion” solo si se necesitan conjuntamente varios, y “disyuncion” para consecuencias independientes. Usa “causal” solo con sustento suficiente; si la relación es plausible pero no demostrada, usa “aparente” y explica en basis qué falta verificar. No inventes causalidad ni añadas vínculos decorativos.
8. El árbol debe ser acíclico, conectado al hecho final, sin nodos aislados ni referencias inexistentes. No es necesario convertir en nodo cada detalle irrelevante del relato.

FACTORES DE LA MATRIZ
9. Tienes disponible el catálogo local completo de 222 factores. Devuelve únicamente códigos exactos del catálogo. Elige un factor solo si glosa y explicación corresponden directamente a un hecho respaldado; no infieras infracciones ni te guíes solo por coincidencias de palabras. Si ninguno aplica claramente, devuelve una lista vacía.

MEDIDAS
10. Vincula cada medida con uno o más node_id que aborda. Propón acciones concretas, verificables y proporcionales a hechos respaldados; si falta evidencia, recomienda primero verificarla y no presentes esa verificación como control definitivo. Si no hay factor causal respaldado, devuelve una lista vacía de medidas en vez de inventar una.
11. Prioriza eliminación, sustitución y controles de ingeniería/colectivos antes de medidas administrativas o EPP, sin presuponer soluciones técnicamente posibles. En criterion indica si la cobertura es individual o colectiva, su alcance y durabilidad. Usa un cargo/unidad genérico como responsible y deja due_date vacío si no hay fundamento para una fecha. Relaciona cada medida con el factor o causa que aborda.

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
