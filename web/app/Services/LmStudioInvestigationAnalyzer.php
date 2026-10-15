<?php

namespace App\Services;

use App\Models\CauseFactor;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class LmStudioInvestigationAnalyzer
{
    /** @return array{model: string, facts: list<string>, tree: array{final: string, origin: string, causes: list<string>}, factor_codes: list<string>, measures: list<array{description: string, category: string, responsible: string, due_date: string}>} */
    public function analyze(string $story): array
    {
        $factors = $this->relevantFactors($story);
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
                'temperature' => 0.15,
                'max_tokens' => 1800,
                'messages' => [
                    ['role' => 'system', 'content' => $this->systemPrompt($factors)],
                    ['role' => 'user', 'content' => "RELATO FINAL VERIFICADO:\n{$story}"],
                ],
                'response_format' => ['type' => 'json_schema', 'json_schema' => ['name' => 'investigation_analysis', 'strict' => true, 'schema' => $this->schema()]],
            ])->throw();
        } catch (ConnectionException $exception) {
            throw new RuntimeException('No se pudo conectar con LM Studio en '.$baseUrl.'. Inicia el servidor local en LM Studio y carga un modelo para probar.', previous: $exception);
        }

        $content = (string) $response->json('choices.0.message.content');
        $rawJson = trim($content);
        if (preg_match('/```(?:json)?\s*([\s\S]*?)\s*```/', $rawJson, $matches)) {
            $rawJson = trim($matches[1]);
        }
        $analysis = json_decode($rawJson, true);
        if (! is_array($analysis) || ! isset($analysis['facts'], $analysis['tree'], $analysis['factor_codes'], $analysis['measures'])) {
            throw new RuntimeException('LM Studio devolvió una respuesta que no cumple el formato esperado.');
        }

        $allowedCodes = $factors->pluck('codigo_efectivo')->all();
        $analysis['factor_codes'] = array_values(array_intersect($analysis['factor_codes'], $allowedCodes));
        $analysis['factors'] = $factors->whereIn('codigo_efectivo', $analysis['factor_codes'])->map(fn (CauseFactor $factor): array => [
            'code' => $factor->codigo_efectivo,
            'category' => $factor->categoria,
            'label' => $factor->glosa,
            'explanation' => $factor->explicacion_glosa,
        ])->values()->all();

        return ['model' => $model, ...$analysis];
    }

    /** @param Collection<int, CauseFactor> $factors */
    private function systemPrompt(Collection $factors): string
    {
        $catalog = $factors->map(fn (CauseFactor $factor): string => "{$factor->codigo_efectivo}: {$factor->glosa} ({$factor->explicacion_glosa})")->implode("\n");

        return <<<'PROMPT'
Eres un asistente técnico de investigación de accidentes del trabajo y enfermedades profesionales en Chile. Analiza únicamente el relato entregado.

Reglas obligatorias:
1. Separa hechos observables y verificables; no inventes nombres, fechas, condiciones ni responsabilidades.
2. No atribuyas culpa, negligencia, diagnóstico médico ni causalidad legal.
3. Si falta información, formula el hecho con lenguaje prudente como "se informó" o "requiere verificación".
4. Construye un árbol causal lógico: hecho final, antecedente/origen y causas o factores contribuyentes respaldados por el relato.
5. Selecciona únicamente códigos presentes en la MATRIZ DE FACTORES proporcionada. La glosa principal debe describir directamente el mecanismo o condición del relato. No selecciones un código solo porque coincida una palabra secundaria como "señalización", "trabajo" o "accidente". Si ninguno está respaldado, devuelve una lista vacía.
6. Propón medidas usando la jerarquía de controles. Deben ser concretas, verificables y relacionadas con las causas.
7. El responsable debe ser un rol o unidad, nunca una persona inventada.
8. Las fechas pueden quedar vacías si el relato no permite fijar un plazo.
9. Responde solo en español y exactamente con el JSON solicitado.
PROMPT."\n\nMATRIZ DE FACTORES CANDIDATOS:\n{$catalog}";
    }

    /** @return array<string, mixed> */
    private function schema(): array
    {
        return [
            'type' => 'object', 'additionalProperties' => false, 'required' => ['facts', 'tree', 'factor_codes', 'measures'],
            'properties' => [
                'facts' => ['type' => 'array', 'minItems' => 1, 'maxItems' => 12, 'items' => ['type' => 'string']],
                'tree' => ['type' => 'object', 'additionalProperties' => false, 'required' => ['final', 'origin', 'causes'], 'properties' => [
                    'final' => ['type' => 'string'], 'origin' => ['type' => 'string'],
                    'causes' => ['type' => 'array', 'minItems' => 1, 'maxItems' => 8, 'items' => ['type' => 'string']],
                ]],
                'factor_codes' => ['type' => 'array', 'maxItems' => 8, 'items' => ['type' => 'string']],
                'measures' => ['type' => 'array', 'minItems' => 1, 'maxItems' => 8, 'items' => ['type' => 'object', 'additionalProperties' => false, 'required' => ['description', 'category', 'responsible', 'due_date'], 'properties' => [
                    'description' => ['type' => 'string'],
                    'category' => ['type' => 'string', 'enum' => ['Eliminación', 'Sustitución', 'Ingeniería', 'Administrativa', 'EPP']],
                    'responsible' => ['type' => 'string'], 'due_date' => ['type' => 'string'],
                ]]],
            ],
        ];
    }

    /** @return Collection<int, CauseFactor> */
    private function relevantFactors(string $story): Collection
    {
        $terms = collect(preg_split('/[^\pL\pN]+/u', Str::lower($story)))
            ->filter(fn (string $term): bool => mb_strlen($term) >= 4)
            ->unique()
            ->values();

        return CauseFactor::query()->where('activo', true)->get()->map(function (CauseFactor $factor) use ($terms): CauseFactor {
            $haystack = Str::lower($factor->glosa.' '.$factor->explicacion_glosa);
            $factor->relevance_score = $terms->sum(fn (string $term): int => substr_count($haystack, $term));

            return $factor;
        })->sortByDesc('relevance_score')->take(24)->values();
    }
}
