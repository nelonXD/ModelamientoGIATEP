<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CauseFactor;
use App\Models\Investigation;
use App\Models\OccupationalCase;
use App\Models\QuestionnaireForm;
use App\Services\GroqPromptGenerator;
use App\Services\LmStudioCaseAgent;
use App\Services\LmStudioFinalStoryGenerator;
use App\Services\LmStudioInvestigationAnalyzer;
use App\Services\LmStudioModelCatalog;
use App\Services\StagedLmStudioInvestigationAnalyzer;
use App\Support\OriginalGiatepSchema;
use App\Support\Rut;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class AiAnalysisController extends Controller
{
    public function askCaseAgent(Request $request, LmStudioCaseAgent $agent): JsonResponse
    {
        abort_unless($request->user()->hasRole('cphs') || $request->user()->hasRole('delegado') || $request->user()->hasRole('prevencion'), 403);
        $validated = $request->validate([
            'case_folio' => ['required', 'string', $this->caseFolioExistsRule()],
            'question' => ['required', 'string', 'min:5', 'max:2000'],
        ]);
        $case = OccupationalCase::where('folio', $validated['case_folio'])->firstOrFail();

        try {
            return response()->json($agent->ask($case, $validated['question']));
        } catch (RuntimeException $exception) {
            report($exception);

            return response()->json(['message' => $exception->getMessage()], 503);
        }
    }

    public function generateFinalStory(Request $request, LmStudioFinalStoryGenerator $generator): JsonResponse
    {
        abort_unless($request->user()->hasRole('cphs') || $request->user()->hasRole('delegado') || $request->user()->hasRole('prevencion'), 403);
        $validated = $request->validate([
            'case_folio' => ['required', 'string', $this->caseFolioExistsRule()],
            'model' => ['nullable', 'string', 'max:255'],
        ]);

        $case = OccupationalCase::query()
            ->with('investigation.interviews.answers.question')
            ->where('folio', $validated['case_folio'])
            ->firstOrFail();

        $input = [...$validated, 'case_snapshot' => $case->only([
            'folio', 'worker_name', 'job_title', 'event_date', 'event_time', 'event_location', 'narrative', 'witnesses', 'details',
        ]), 'interviews_snapshot' => $case->investigation?->interviews->toArray() ?? []];

        return $this->runStage($request, 'relato', $input, fn (): array => $generator->generate($case, $validated['model'] ?? null));
    }

    public function storeFinalStory(Request $request): JsonResponse
    {
        abort_unless($request->user()->hasRole('cphs') || $request->user()->hasRole('delegado') || $request->user()->hasRole('prevencion'), 403);
        $validated = $request->validate([
            'case_folio' => ['required', 'string', $this->caseFolioExistsRule()],
            'story' => ['required', 'string', 'min:30', 'max:30000'],
        ]);

        $investigation = DB::transaction(function () use ($request, $validated): Investigation {
            $case = OccupationalCase::where('folio', $validated['case_folio'])->firstOrFail();

            if (OriginalGiatepSchema::isActive()) {
                $investigation = $case->investigation()->lockForUpdate()->first();
                if (! $investigation) {
                    $sequence = (int) Investigation::query()->lockForUpdate()->max('Codigo_Investigacion') + 1;
                    $investigation = $case->investigation()->create([
                        'folio' => 'INV-'.now()->year.'-'.str_pad((string) $sequence, 3, '0', STR_PAD_LEFT),
                        'owner_id' => $request->user()->getKey(), 'status' => 'En desarrollo',
                        'created_at' => now(), 'final_story' => $case->narrative,
                    ]);
                }
            } else {
                $investigation = Investigation::firstOrCreate(['occupational_case_id' => $case->id], [
                    'folio' => 'INV-'.now()->year.'-'.str_pad((string) (Investigation::query()->lockForUpdate()->count() + 1), 3, '0', STR_PAD_LEFT),
                    'owner_id' => $request->user()->id,
                    'status' => 'En desarrollo',
                ]);
            }
            $this->ensureInvestigationEditable($investigation);
            $investigation->update(['final_story' => $validated['story']]);

            return $investigation;
        });

        return response()->json(['message' => 'Relato final guardado correctamente.', 'investigation' => $investigation->folio]);
    }

    public function externalAnalysisPrompt(Request $request, LmStudioFinalStoryGenerator $generator): JsonResponse
    {
        abort_unless($request->user()->hasRole('cphs') || $request->user()->hasRole('delegado') || $request->user()->hasRole('prevencion'), 403);
        $validated = $request->validate([
            'case_folio' => ['required', 'string', $this->caseFolioExistsRule()],
            'story' => ['nullable', 'string', 'max:30000'],
            'prompt_provider' => ['nullable', 'in:template,groq'],
            'questionnaire_options' => ['sometimes', 'array', 'max:120'],
            'questionnaire_options.*.field' => ['required', 'string', 'max:100', 'distinct'],
            'questionnaire_options.*.type' => ['required', 'in:radio,checkbox'],
            'questionnaire_options.*.question' => ['nullable', 'string', 'max:500'],
            'questionnaire_options.*.options' => ['required', 'array', 'max:40'],
            'questionnaire_options.*.options.*' => ['required', 'string', 'max:255'],
        ]);
        $case = OccupationalCase::query()->with(['investigation.interviews.answers.question', 'evidences'])
            ->where(OriginalGiatepSchema::isActive() ? 'Folio' : 'folio', $validated['case_folio'])->firstOrFail();

        $prompt = $generator->externalAnalysisPrompt($case, $validated['story'] ?? null, $validated['questionnaire_options'] ?? []);
        if (($validated['prompt_provider'] ?? 'template') === 'groq') {
            try {
                $prompt = app(GroqPromptGenerator::class)->generate($prompt);
            } catch (RuntimeException $exception) {
                report($exception);

                return response()->json(['message' => $exception->getMessage()], 503);
            }
        }

        return response()->json(['prompt' => $prompt, 'provider' => $validated['prompt_provider'] ?? 'template']);
    }

    public function importExternalAnalysis(Request $request, LmStudioFinalStoryGenerator $storyGenerator, StagedLmStudioInvestigationAnalyzer $analyzer): JsonResponse
    {
        abort_unless($request->user()->hasRole('cphs') || $request->user()->hasRole('delegado') || $request->user()->hasRole('prevencion'), 403);
        $validated = $request->validate([
            'case_folio' => ['required', 'string', $this->caseFolioExistsRule()],
            'story' => ['required', 'string', 'min:30', 'max:30000'],
            'external_response' => ['required', 'string', 'min:10', 'max:30000'],
            'model' => ['nullable', 'string', 'max:255'],
            'questionnaire_options' => ['sometimes', 'array', 'max:120'],
            'questionnaire_options.*.field' => ['required', 'string', 'max:100', 'distinct'],
            'questionnaire_options.*.type' => ['required', 'in:radio,checkbox'],
            'questionnaire_options.*.question' => ['nullable', 'string', 'max:500'],
            'questionnaire_options.*.options' => ['required', 'array', 'max:40'],
            'questionnaire_options.*.options.*' => ['required', 'string', 'max:255'],
        ]);
        $case = OccupationalCase::query()->with(['investigation.interviews.answers.question', 'evidences'])
            ->where(OriginalGiatepSchema::isActive() ? 'Folio' : 'folio', $validated['case_folio'])->firstOrFail();
        $options = $validated['questionnaire_options'] ?? [];
        $input = [...$validated, 'source_context' => $storyGenerator->externalAnalysisContext($case, $validated['story'], $options)];

        return $this->runStage($request, 'integracion-de-ia-externa', $input,
            fn (): array => $analyzer->importExternalResponse($input['source_context'], $validated['external_response'], $validated['model'] ?? null, $options));
    }

    public function analyze(Request $request, LmStudioInvestigationAnalyzer $analyzer): JsonResponse
    {
        abort_unless($request->user()->hasRole('cphs') || $request->user()->hasRole('delegado') || $request->user()->hasRole('prevencion'), 403);
        $validated = $request->validate(['story' => ['required', 'string', 'min:30', 'max:30000']]);
        $startedAt = hrtime(true);
        $promptId = DB::table('ai_prompts')->where('nombre', 'analisis-investigacion')->where('activo', true)->value('id');

        try {
            $analysis = $analyzer->analyze($validated['story']);
            DB::table('ai_analysis_runs')->insert([
                'ai_prompt_id' => $promptId, 'requested_by' => $request->user()->id, 'modelo' => $analysis['model'],
                'entrada' => $validated['story'], 'salida' => json_encode($analysis, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                'estado' => 'completado', 'duracion_ms' => (int) ((hrtime(true) - $startedAt) / 1_000_000),
                'created_at' => now(), 'updated_at' => now(),
            ]);

            return response()->json($analysis);
        } catch (RuntimeException $exception) {
            report($exception);
            DB::table('ai_analysis_runs')->insert([
                'ai_prompt_id' => $promptId, 'requested_by' => $request->user()->id,
                'modelo' => (string) config('services.lm_studio.model', 'desconocido'), 'entrada' => $validated['story'],
                'estado' => 'error', 'error' => $exception->getMessage(),
                'duracion_ms' => (int) ((hrtime(true) - $startedAt) / 1_000_000), 'created_at' => now(), 'updated_at' => now(),
            ]);

            return response()->json(['message' => $exception->getMessage()], 503);
        }
    }

    public function models(Request $request, LmStudioModelCatalog $catalog): JsonResponse
    {
        abort_unless($request->user()->hasRole('cphs') || $request->user()->hasRole('delegado') || $request->user()->hasRole('prevencion'), 403);

        return response()->json([
            ...$catalog->list(),
            'configured_model' => (string) config('services.lm_studio.model', ''),
            'groq_available' => (string) config('services.groq.key', '') !== '',
            'groq_model' => (string) config('services.groq.model', 'openai/gpt-oss-120b'),
        ]);
    }

    public function analyzeFacts(Request $request, StagedLmStudioInvestigationAnalyzer $analyzer): JsonResponse
    {
        abort_unless($request->user()->hasRole('cphs') || $request->user()->hasRole('delegado') || $request->user()->hasRole('prevencion'), 403);
        $validated = $request->validate([
            'case_folio' => ['nullable', 'string', $this->caseFolioExistsRule()],
            'story' => ['required', 'string', 'min:30', 'max:30000'],
            'model' => ['nullable', 'string', 'max:255'],
            'provider' => ['nullable', 'in:lm_studio,groq'],
        ]);

        return $this->runStage($request, 'hechos', $validated, fn (): array => $analyzer->facts($validated['story'], $this->stageModel($validated), $validated['provider'] ?? 'lm_studio'));
    }

    public function analyzeTree(Request $request, StagedLmStudioInvestigationAnalyzer $analyzer): JsonResponse
    {
        abort_unless($request->user()->hasRole('cphs') || $request->user()->hasRole('delegado') || $request->user()->hasRole('prevencion'), 403);
        $validated = $request->validate([
            'case_folio' => ['nullable', 'string', $this->caseFolioExistsRule()],
            'story' => ['required', 'string', 'min:30', 'max:30000'],
            'model' => ['nullable', 'string', 'max:255'],
            'provider' => ['nullable', 'in:lm_studio,groq'],
            'facts' => ['required', 'array', 'min:1', 'max:24'],
            'facts.*.description' => ['required', 'string', 'max:2000'],
            'facts.*.classification' => ['required', 'in:hecho,permanente'],
            'facts.*.evidence_status' => ['required', 'in:verificado,declarado,no_verificado'],
            'facts.*.evidence' => ['nullable', 'string', 'max:2000'],
            'facts.*.source' => ['nullable', 'string', 'max:255'],
        ]);

        return $this->runStage($request, 'arbol', $validated, fn (): array => $analyzer->tree($validated['story'], $validated['facts'], $this->stageModel($validated), $validated['provider'] ?? 'lm_studio'));
    }

    public function analyzeMeasures(Request $request, StagedLmStudioInvestigationAnalyzer $analyzer): JsonResponse
    {
        abort_unless($request->user()->hasRole('cphs') || $request->user()->hasRole('delegado') || $request->user()->hasRole('prevencion'), 403);
        $validated = $request->validate([
            'case_folio' => ['nullable', 'string', $this->caseFolioExistsRule()],
            'story' => ['required', 'string', 'min:30', 'max:30000'],
            'model' => ['nullable', 'string', 'max:255'],
            'provider' => ['nullable', 'in:lm_studio,groq'],
            'facts' => ['required', 'array', 'min:1', 'max:24'], 'facts.*.description' => ['required', 'string', 'max:2000'],
            'facts.*.classification' => ['required', 'in:hecho,permanente'],
            'facts.*.evidence_status' => ['required', 'in:verificado,declarado,no_verificado'],
            'facts.*.evidence' => ['nullable', 'string', 'max:2000'],
            'facts.*.source' => ['nullable', 'string', 'max:255'],
            'tree' => ['required', 'array'], 'tree.final_node_id' => ['required', 'string', 'max:40'],
            'tree.nodes' => ['required', 'array', 'min:1', 'max:24'], 'tree.nodes.*.id' => ['required', 'string', 'max:40', 'distinct'],
            'tree.nodes.*.type' => ['required', 'in:hecho,permanente'], 'tree.nodes.*.description' => ['required', 'string', 'max:600'],
            'tree.edges' => ['present', 'array', 'max:40'], 'tree.edges.*.from' => ['required', 'string', 'max:40'],
            'tree.edges.*.evidence' => ['sometimes', 'string', 'max:1000'],
            'tree.edges.*.to' => ['required', 'string', 'max:40'], 'tree.edges.*.link_type' => ['required', 'in:causal,aparente'],
            'tree.edges.*.logic' => ['required', 'in:cadena,conjuncion,disyuncion'], 'tree.edges.*.basis' => ['required', 'string', 'max:1000'],
            'candidate_codes' => ['present', 'array', 'max:64'], 'candidate_codes.*' => ['required', 'string', 'max:80'],
        ]);

        return $this->runStage($request, 'medidas', $validated, fn (): array => $analyzer->measures($validated['story'], $validated['facts'], $validated['tree'], $validated['candidate_codes'], $this->stageModel($validated), $validated['provider'] ?? 'lm_studio'));
    }

    public function analyzeFactorBatch(Request $request, StagedLmStudioInvestigationAnalyzer $analyzer): JsonResponse
    {
        abort_unless($request->user()->hasRole('cphs') || $request->user()->hasRole('delegado') || $request->user()->hasRole('prevencion'), 403);
        $validated = $request->validate([
            'case_folio' => ['nullable', 'string', $this->caseFolioExistsRule()],
            'story' => ['required', 'string', 'min:30', 'max:30000'],
            'model' => ['nullable', 'string', 'max:255'],
            'provider' => ['nullable', 'in:lm_studio,groq'],
            'facts' => ['required', 'array', 'min:1', 'max:24'], 'facts.*.description' => ['required', 'string', 'max:2000'],
            'facts.*.classification' => ['required', 'in:hecho,permanente'],
            'facts.*.evidence_status' => ['required', 'in:verificado,declarado,no_verificado'],
            'facts.*.evidence' => ['nullable', 'string', 'max:2000'],
            'facts.*.source' => ['nullable', 'string', 'max:255'],
            'tree' => ['required', 'array'], 'tree.final_node_id' => ['required', 'string', 'max:40'],
            'tree.nodes' => ['required', 'array', 'min:1', 'max:24'], 'tree.nodes.*.id' => ['required', 'string', 'max:40', 'distinct'],
            'tree.nodes.*.type' => ['required', 'in:hecho,permanente'], 'tree.nodes.*.description' => ['required', 'string', 'max:600'],
            'tree.edges' => ['present', 'array', 'max:40'], 'tree.edges.*.from' => ['required', 'string', 'max:40'],
            'tree.edges.*.evidence' => ['sometimes', 'string', 'max:1000'],
            'tree.edges.*.to' => ['required', 'string', 'max:40'], 'tree.edges.*.link_type' => ['required', 'in:causal,aparente'],
            'tree.edges.*.logic' => ['required', 'in:cadena,conjuncion,disyuncion'], 'tree.edges.*.basis' => ['required', 'string', 'max:1000'],
            'batch_index' => ['required', 'integer', 'min:0', 'max:20'],
        ]);

        return $this->runStage($request, 'revision de factores', $validated,
            fn (): array => $analyzer->screenFactorBatch($validated['story'], $validated['facts'], $validated['tree'], (int) $validated['batch_index'], $this->stageModel($validated), $validated['provider'] ?? 'lm_studio'));
    }

    /** @param array<string, mixed> $validated */
    private function stageModel(array $validated): ?string
    {
        return ($validated['provider'] ?? 'lm_studio') === 'groq' ? null : ($validated['model'] ?? null);
    }

    private function caseFolioExistsRule(): string
    {
        return OriginalGiatepSchema::isActive() ? 'exists:caso,Folio' : 'exists:occupational_cases,folio';
    }

    /** @param array<string, mixed>|string $input */
    private function runStage(Request $request, string $stage, array|string $input, callable $callback): JsonResponse
    {
        $startedAt = hrtime(true);
        $usesOriginalSchema = OriginalGiatepSchema::isActive();
        $promptId = $usesOriginalSchema ? null : DB::table('ai_prompts')->where('nombre', 'analisis-investigacion')->where('activo', true)->value('id');
        $encodedInput = json_encode(['stage' => $stage, 'data' => $input], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        $selectedModel = is_array($input) ? trim((string) ($input['model'] ?? '')) : '';
        $investigationId = null;
        if (is_array($input) && ! empty($input['case_folio'])) {
            $caseFolioColumn = OriginalGiatepSchema::isActive() ? 'Folio' : 'folio';
            $investigationId = Investigation::query()
                ->whereHas('occupationalCase', fn ($query) => $query->where($caseFolioColumn, $input['case_folio']))
                ->value(OriginalGiatepSchema::isActive() ? 'Codigo_Investigacion' : 'id');
        }
        try {
            $result = $callback();
            if (! $usesOriginalSchema) {
                DB::table('ai_analysis_runs')->insert([
                    'investigation_id' => $investigationId,
                    'ai_prompt_id' => $promptId, 'requested_by' => $request->user()->id, 'modelo' => $result['model'],
                    'entrada' => $encodedInput, 'salida' => json_encode($result, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                    'estado' => 'completado', 'duracion_ms' => (int) ((hrtime(true) - $startedAt) / 1_000_000),
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }

            return response()->json($result);
        } catch (RuntimeException $exception) {
            report($exception);
            if (! $usesOriginalSchema) {
                DB::table('ai_analysis_runs')->insert([
                    'investigation_id' => $investigationId,
                    'ai_prompt_id' => $promptId, 'requested_by' => $request->user()->id,
                    'modelo' => $selectedModel ?: (string) config(($input['provider'] ?? 'lm_studio') === 'groq' ? 'services.groq.model' : 'services.lm_studio.model', 'desconocido'), 'entrada' => $encodedInput,
                    'estado' => 'error', 'error' => "Etapa {$stage}: ".$exception->getMessage(),
                    'duracion_ms' => (int) ((hrtime(true) - $startedAt) / 1_000_000), 'created_at' => now(), 'updated_at' => now(),
                ]);
            }

            return response()->json(['message' => $exception->getMessage()], 503);
        }
    }

    public function store(Request $request, StagedLmStudioInvestigationAnalyzer $analyzer): JsonResponse
    {
        abort_unless($request->user()->hasRole('cphs') || $request->user()->hasRole('delegado') || $request->user()->hasRole('prevencion'), 403);
        $validated = $request->validate([
            'case_folio' => ['required', 'string', $this->caseFolioExistsRule()], 'story' => ['required', 'string', 'max:30000'],
            'model' => ['nullable', 'string', 'max:255'], 'facts' => ['required', 'array', 'min:1'], 'facts.*' => ['required', 'string', 'max:2000'],
            'tree' => ['required', 'array'], 'tree.final' => ['required', 'string', 'max:2000'], 'tree.origin' => ['nullable', 'string', 'max:2000'],
            'tree.causes' => ['present', 'array'], 'tree.causes.*' => ['required', 'string', 'max:2000'],
            'tree.graph' => ['sometimes', 'array'], 'tree.graph.final_node_id' => ['required_with:tree.graph', 'string', 'max:40'],
            'tree.graph.nodes' => ['required_with:tree.graph', 'array', 'min:1', 'max:24'],
            'tree.graph.nodes.*.id' => ['required', 'string', 'max:40', 'distinct'],
            'tree.graph.nodes.*.type' => ['required', 'in:hecho,permanente'],
            'tree.graph.nodes.*.description' => ['required', 'string', 'max:600'],
            'tree.graph.edges' => ['present_with:tree.graph', 'array', 'max:40'],
            'tree.graph.edges.*.evidence' => ['nullable', 'string', 'max:1000'],
            'tree.graph.edges.*.from' => ['required', 'string', 'max:40'], 'tree.graph.edges.*.to' => ['required', 'string', 'max:40'],
            'tree.graph.edges.*.link_type' => ['required', 'in:causal,aparente'],
            'tree.graph.edges.*.logic' => ['required', 'in:cadena,conjuncion,disyuncion'], 'tree.graph.edges.*.basis' => ['required', 'string', 'max:1000'],
            'fact_details' => ['sometimes', 'array', 'min:1', 'max:24'],
            'fact_details.*.description' => ['required', 'string', 'max:2000'],
            'fact_details.*.classification' => ['required', 'in:hecho,permanente'],
            'fact_details.*.evidence_status' => ['required', 'in:verificado,declarado,no_verificado'],
            'fact_details.*.evidence' => ['nullable', 'string', 'max:2000'],
            'fact_details.*.source' => ['nullable', 'string', 'max:255'],
            'fact_details.*.source_ids' => ['sometimes', 'array', 'max:50'], 'fact_details.*.source_ids.*' => ['string', 'max:80'],
            'prompt_version' => ['nullable', 'string', 'max:100'],
            'quality_warnings' => ['sometimes', 'array', 'max:30'], 'quality_warnings.*' => ['string', 'max:2000'],
            'pending_verifications' => ['sometimes', 'array', 'max:30'], 'pending_verifications.*' => ['string', 'max:2000'],
            'questionnaire_selections' => ['sometimes', 'array', 'max:80'],
            'questionnaire_selections.*.field' => ['required', 'string', 'max:255'],
            'questionnaire_selections.*.selected_values' => ['required', 'array', 'max:20'],
            'questionnaire_selections.*.selected_values.*' => ['string', 'max:500'],
            'questionnaire_selections.*.evidence' => ['nullable', 'string', 'max:2000'],
            'questionnaire_selections.*.rationale' => ['nullable', 'string', 'max:1000'],
            'manual_entry' => ['sometimes', 'boolean'],
            'factor_codes' => ['present', 'array', 'max:24'], 'factor_codes.*' => ['string', 'exists:cause_factors,codigo_efectivo'],
            'measures' => ['present', 'array'], 'measures.*.description' => ['required', 'string', 'max:3000'],
            'measures.*.category' => ['required', 'string', 'max:80'], 'measures.*.responsible' => ['required', 'string', 'max:255'],
            'measures.*.due_date' => ['nullable', 'date'], 'measures.*.criterion' => ['nullable', 'string', 'max:2000'],
            'measures.*.related_node_ids' => ['sometimes', 'array', 'max:24'], 'measures.*.related_node_ids.*' => ['string', 'max:40'],
        ]);

        if (isset($validated['fact_details']) && array_column($validated['fact_details'], 'description') !== $validated['facts']) {
            throw ValidationException::withMessages(['fact_details' => 'Los hechos detallados no coinciden con la lista revisada. Vuelva a generar las etapas posteriores.']);
        }
        $graph = $validated['tree']['graph'] ?? null;
        if ($graph !== null && ! ($validated['manual_entry'] ?? false)) {
            try {
                $analyzer->validateGraph($graph);
                if (isset($validated['fact_details'])) {
                    $reviewedFacts = array_map(fn (array $fact): array => [
                        'description' => $fact['description'], 'classification' => $fact['classification'],
                        'evidence_status' => $fact['evidence_status'], 'evidence' => $fact['evidence'] ?? '',
                        'source' => $fact['source'] ?? 'Relato final',
                    ], $validated['fact_details']);
                    $analyzer->validateReviewedTree($graph, $reviewedFacts, ! ($validated['manual_entry'] ?? false));
                }
            } catch (RuntimeException $exception) {
                throw ValidationException::withMessages(['tree.graph' => $exception->getMessage()]);
            }
        }
        $nodeIds = array_column($graph['nodes'] ?? [], 'id');
        foreach ($validated['measures'] as $index => $measure) {
            if (array_diff($measure['related_node_ids'] ?? [], $nodeIds) !== []) {
                throw ValidationException::withMessages(["measures.{$index}.related_node_ids" => 'La medida hace referencia a una causa que no existe en el árbol revisado.']);
            }
        }

        $manualEntry = (bool) ($validated['manual_entry'] ?? false);
        $investigation = DB::transaction(function () use ($request, $validated, $manualEntry): Investigation {
            if (OriginalGiatepSchema::isActive()) {
                $case = OccupationalCase::query()->where('Folio', $validated['case_folio'])->firstOrFail();
                $investigation = $case->investigation()->lockForUpdate()->first();

                if (! $investigation) {
                    $sequence = (int) Investigation::query()->lockForUpdate()->max('Codigo_Investigacion') + 1;
                    $investigation = $case->investigation()->create([
                        'folio' => 'INV-'.now()->year.'-'.str_pad((string) $sequence, 3, '0', STR_PAD_LEFT),
                        'owner_id' => $request->user()->getKey(),
                        'status' => 'En desarrollo',
                        'created_at' => now(),
                        'final_story' => $validated['story'],
                    ]);
                }
            } else {
                $case = OccupationalCase::where('folio', $validated['case_folio'])->firstOrFail();
                $investigation = Investigation::firstOrCreate(['occupational_case_id' => $case->id], [
                    'folio' => 'INV-'.now()->year.'-'.str_pad((string) (Investigation::query()->lockForUpdate()->count() + 1), 3, '0', STR_PAD_LEFT),
                    'owner_id' => $request->user()->id,
                    'status' => 'En desarrollo',
                ]);
            }
            $this->ensureInvestigationEditable($investigation);
            $investigation->update(['final_story' => $validated['story'], 'ai_model' => $validated['model'] ?? null, 'ai_analyzed_at' => now()]);
            $investigation->facts()->delete();
            $investigation->causalNodes()->delete();
            $investigation->measures()->delete();
            foreach ($validated['facts'] as $position => $fact) {
                $investigation->facts()->create(['position' => $position + 1, 'description' => $fact, 'ai_generated' => ! $manualEntry]);
            }
            $investigation->causalNodes()->create(['node_type' => 'final', 'position' => 1, 'description' => $validated['tree']['final'], 'ai_generated' => ! $manualEntry]);
            $origin = trim((string) ($validated['tree']['origin'] ?? ''));
            $final = trim($validated['tree']['final']);
            if ($origin !== '' && $origin !== $final) {
                $investigation->causalNodes()->create(['node_type' => 'origin', 'position' => 1, 'description' => $validated['tree']['origin'], 'ai_generated' => ! $manualEntry]);
            }
            foreach ($validated['tree']['causes'] as $position => $cause) {
                $investigation->causalNodes()->create(['node_type' => 'cause', 'position' => $position + 1, 'description' => $cause, 'ai_generated' => ! $manualEntry]);
            }
            if (! empty($validated['tree']['graph'])) {
                $graph = $validated['tree']['graph'];
                $nodeIds = collect($graph['nodes'])->pluck('id');
                abort_unless($nodeIds->contains($graph['final_node_id']), 422, 'El hecho final del árbol no coincide con sus nodos.');
                foreach ($graph['edges'] as $edge) {
                    abort_unless($nodeIds->contains($edge['from']) && $nodeIds->contains($edge['to']) && $edge['from'] !== $edge['to'], 422, 'El árbol contiene un vínculo a un nodo inexistente.');
                }
                $investigation->causalNodes()->create([
                    'node_type' => 'graph', 'position' => 1,
                    'description' => json_encode($graph, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR), 'ai_generated' => ! $manualEntry,
                ]);
            }
            $factorIds = CauseFactor::query()->whereIn('codigo_efectivo', $validated['factor_codes'])->pluck('id');
            $investigation->causeFactors()->sync(
                $factorIds->mapWithKeys(fn (int $id): array => [$id => ['origen' => $manualEntry ? 'manual' : 'ia', 'validated_by' => $request->user()->id, 'validated_at' => now()]])->all(),
            );
            $graphNodes = collect($validated['tree']['graph']['nodes'] ?? [])->keyBy('id');
            foreach ($validated['measures'] as $measure) {
                $description = $measure['description'];
                if (! empty($measure['criterion'] ?? '')) {
                    $description .= "\nCriterio de cobertura y estabilidad: ".$measure['criterion'];
                }
                $related = collect($measure['related_node_ids'] ?? [])->map(fn (string $id): ?string => $graphNodes->get($id)['description'] ?? null)->filter()->values();
                if ($related->isNotEmpty()) {
                    $description .= "\nCausas abordadas: ".$related->implode('; ');
                }
                $investigation->measures()->create([
                    'description' => $description, 'category' => $measure['category'], 'responsible' => $measure['responsible'],
                    'due_date' => ($measure['due_date'] ?? null) ?: null, 'ai_generated' => ! $manualEntry,
                ]);
            }

            DB::table('ai_analysis_runs')->insert([
                'investigation_id' => $investigation->id, 'requested_by' => $request->user()->id,
                'modelo' => $manualEntry ? 'ingreso-manual' : ($validated['model'] ?? 'revision-manual'),
                'entrada' => json_encode(['stage' => 'propuesta revisada', 'data' => $validated], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                'salida' => json_encode(['investigation' => $investigation->folio, 'review_completed' => true], JSON_THROW_ON_ERROR),
                'estado' => 'guardado', 'created_at' => now(), 'updated_at' => now(),
            ]);

            return $investigation;
        });

        return response()->json(['message' => 'Propuesta guardada correctamente.', 'investigation' => $investigation->folio]);
    }

    public function storeInterview(Request $request): JsonResponse
    {
        abort_unless($request->user()->hasRole('cphs') || $request->user()->hasRole('delegado') || $request->user()->hasRole('prevencion'), 403);
        if (OriginalGiatepSchema::isActive()) {
            return $this->storeOriginalInterview($request);
        }

        $validated = $request->validate([
            'case_folio' => ['required', 'string', $this->caseFolioExistsRule()],
            'questionnaire_type' => ['required', 'in:adapted,legacy'],
            'interviewee_name' => ['required', 'string', 'max:255'], 'interviewee_role' => ['nullable', 'string', 'max:255'],
            'witness_type' => ['nullable', 'string', 'max:80'], 'interview_date' => ['nullable', 'date'],
            'interviewee_rut' => ['nullable', 'string', 'max:20'], 'interviewee_company' => ['nullable', 'string', 'max:255'],
            'interview_start_time' => ['nullable', 'date_format:H:i'], 'interview_location' => ['nullable', 'string', 'max:255'],
            'answers' => ['required', 'array'], 'answers.*' => ['present'],
        ]);

        $interview = DB::transaction(function () use ($request, $validated) {
            $case = OccupationalCase::where('folio', $validated['case_folio'])->firstOrFail();
            $investigation = Investigation::firstOrCreate(['occupational_case_id' => $case->id], [
                'folio' => 'INV-'.now()->year.'-'.str_pad((string) (Investigation::query()->lockForUpdate()->count() + 1), 3, '0', STR_PAD_LEFT),
                'owner_id' => $request->user()->id,
                'status' => 'En desarrollo',
            ]);
            $this->ensureInvestigationEditable($investigation);
            $form = QuestionnaireForm::query()->where('tipo', $validated['questionnaire_type'])->where('estado', 'activo')->with('questions')->latest('version')->first();
            $interview = $investigation->interviews()->create([
                'questionnaire_form_id' => $form?->id, 'questionnaire_type' => $validated['questionnaire_type'],
                'interviewee_name' => $validated['interviewee_name'], 'interviewee_role' => $validated['interviewee_role'] ?? null,
                'witness_type' => $validated['witness_type'] ?? null, 'interview_date' => $validated['interview_date'] ?? null,
                'interviewee_rut' => $validated['interviewee_rut'] ?? null, 'interviewee_company' => $validated['interviewee_company'] ?? null,
                'interview_start_time' => $validated['interview_start_time'] ?? null, 'interview_location' => $validated['interview_location'] ?? null,
                'created_by' => $request->user()->id,
            ]);
            foreach ($validated['answers'] as $key => $value) {
                $question = $form?->questions->firstWhere('variable', $key);
                $interview->answers()->create([
                    'questionnaire_question_id' => $question?->id, 'question_key' => $key,
                    'answer_type' => is_array($value) ? 'array' : 'text', 'value' => is_array($value) ? $value : [$value],
                ]);
            }

            return $interview;
        });

        return response()->json(['message' => 'Entrevista guardada correctamente.', 'id' => $interview->id]);
    }

    private function storeOriginalInterview(Request $request): JsonResponse
    {
        $request->merge(['interviewee_rut' => Rut::normalize((string) $request->input('interviewee_rut', ''))]);
        $validated = $request->validate([
            'case_folio' => ['required', 'string', 'exists:caso,Folio'],
            'questionnaire_type' => ['required', 'in:adapted,legacy'],
            'interviewee_name' => ['required', 'string', 'max:150'],
            'interviewee_rut' => ['required', 'string', 'max:12', function (string $attribute, mixed $value, \Closure $fail): void {
                if (! Rut::isValid((string) $value)) {
                    $fail('Ingresa un RUT válido.');
                }
            }],
            'interviewee_role' => ['nullable', 'string', 'max:100'],
            'interviewee_company' => ['nullable', 'string', 'max:150'],
            'witness_type' => ['nullable', 'string', 'max:100'],
            'interview_date' => ['required', 'date'],
            'interview_start_time' => ['nullable', 'date_format:H:i'],
            'interview_location' => ['nullable', 'string', 'max:200'],
            'answers' => ['required', 'array'],
            'answers.*' => ['present'],
            'question_labels' => ['sometimes', 'array', 'max:300'],
            'question_labels.*' => ['nullable', 'string', 'max:500'],
        ]);

        $declarationId = DB::transaction(function () use ($request, $validated): int {
            $case = OccupationalCase::where('folio', $validated['case_folio'])->firstOrFail();
            $investigation = $case->investigation()->first();
            if (! $investigation) {
                $sequence = (int) Investigation::query()->max('Codigo_Investigacion') + 1;
                $investigation = $case->investigation()->create([
                    'folio' => 'INV-'.now()->year.'-'.str_pad((string) $sequence, 3, '0', STR_PAD_LEFT),
                    'owner_id' => $request->user()->getKey(), 'status' => 'En desarrollo', 'created_at' => now(),
                ]);
            }
            $this->ensureInvestigationEditable($investigation);

            $rut = $validated['interviewee_rut'];
            DB::table('testigo')->updateOrInsert(['Rut_Testigo' => $rut], [
                'Nombre_Testigo' => $validated['interviewee_name'],
                'Establecimiento' => $validated['interviewee_company'] ?? null,
                'Cargo' => $validated['interviewee_role'] ?? null,
            ]);
            DB::table('testigo_caso')->updateOrInsert(
                ['Id_Caso' => $case->getKey(), 'Rut_Testigo' => $rut],
                ['Fecha_Registro' => now()],
            );
            $witnessCaseId = DB::table('testigo_caso')->where('Id_Caso', $case->getKey())->where('Rut_Testigo', $rut)->value('Id_Testigo_Caso');

            $formId = DB::table('formulario_declaracion')->where('Nombre', 'Formulario GIATEP adaptado')->value('Id_Formulario');
            if (! $formId) {
                $formId = DB::table('formulario_declaracion')->insertGetId([
                    'Nombre' => 'Formulario GIATEP adaptado', 'Version' => '1.0', 'Estado' => 'Activo',
                ], 'Id_Formulario');
            }

            $typeName = $validated['witness_type'] ?: 'Testigo presencial';
            $typeId = DB::table('tipo_entrevistado')->where('Nombre_Tipo', $typeName)->value('Id_Tipo_Entrevistado');
            if (! $typeId) {
                $typeId = DB::table('tipo_entrevistado')->insertGetId(['Nombre_Tipo' => $typeName], 'Id_Tipo_Entrevistado');
            }

            $id = DB::table('declaracion')->insertGetId([
                'Id_Formulario' => $formId,
                'Codigo_Investigacion' => $investigation->getKey(),
                'Id_Testigo_Caso' => $witnessCaseId,
                'Id_Tipo_Entrevistado' => $typeId,
                'Rut_Usuario' => $request->user()->getKey(),
                'Fecha_Entrevista' => $validated['interview_date'],
                'Hora_Inicio' => $validated['interview_start_time'] ?? null,
                'Lugar_Entrevista' => $validated['interview_location'] ?? null,
                'Modalidad' => 'Presencial',
                'Cargo_Al_Declarar' => $validated['interviewee_role'] ?? null,
                'Empresa_Al_Declarar' => $validated['interviewee_company'] ?? null,
                'Estado' => 'Completada',
            ], 'Id_Declaracion');

            $order = 0;
            foreach ($validated['answers'] as $key => $value) {
                $questionLabel = trim((string) ($validated['question_labels'][$key] ?? ''));
                $question = DB::table('pregunta_declaracion')->where('Variable', $key)->first();
                if (! $question) {
                    $questionId = DB::table('pregunta_declaracion')->insertGetId([
                        'Id_Formulario' => $formId,
                        'Seccion' => str_starts_with($key, 'interview_') ? 'Observaciones' : 'Entrevista',
                        'Texto_Pregunta' => $questionLabel !== '' ? $questionLabel : str_replace(['_', '[]'], ' ', $key),
                        'Tipo_Respuesta' => is_array($value) ? 'multiple' : 'texto',
                        'Orden' => ++$order,
                        'Obligatorio_Si_Aplica' => false,
                        'Variable' => $key,
                    ], 'Id_Pregunta');
                } else {
                    $questionId = $question->Id_Pregunta;
                    if ($questionLabel !== '' && $question->Texto_Pregunta !== $questionLabel) {
                        DB::table('pregunta_declaracion')->where('Id_Pregunta', $questionId)->update(['Texto_Pregunta' => $questionLabel]);
                    }
                }

                $answerText = is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR) : (string) $value;
                DB::table('respuesta_declaracion')->updateOrInsert(
                    ['Id_Declaracion' => $id, 'Id_Pregunta' => $questionId],
                    ['Estado_Respuesta' => 'Respondida', 'Texto_Respuesta' => $answerText],
                );
            }

            return $id;
        });

        return response()->json(['message' => 'Entrevista guardada correctamente.', 'id' => $declarationId]);
    }

    private function ensureInvestigationEditable(Investigation $investigation): void
    {
        abort_unless($investigation->status === 'En desarrollo', 409, 'La investigación ya está en revisión o completada y no admite cambios.');
    }
}
