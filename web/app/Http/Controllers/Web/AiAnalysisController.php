<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CauseFactor;
use App\Models\Investigation;
use App\Models\OccupationalCase;
use App\Models\QuestionnaireForm;
use App\Services\LmStudioInvestigationAnalyzer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class AiAnalysisController extends Controller
{
    public function storeFinalStory(Request $request): JsonResponse
    {
        abort_unless($request->user()->hasPermission('investigations.read'), 403);
        $validated = $request->validate([
            'case_folio' => ['required', 'string', 'exists:occupational_cases,folio'],
            'story' => ['required', 'string', 'min:30', 'max:30000'],
        ]);

        $investigation = DB::transaction(function () use ($request, $validated): Investigation {
            $case = OccupationalCase::where('folio', $validated['case_folio'])->firstOrFail();
            $investigation = Investigation::firstOrCreate(['occupational_case_id' => $case->id], [
                'folio' => 'INV-'.now()->year.'-'.str_pad((string) (Investigation::query()->lockForUpdate()->count() + 1), 3, '0', STR_PAD_LEFT),
                'owner_id' => $request->user()->id,
            ]);
            $investigation->update(['final_story' => $validated['story']]);

            return $investigation;
        });

        return response()->json(['message' => 'Relato final guardado correctamente.', 'investigation' => $investigation->folio]);
    }

    public function analyze(Request $request, LmStudioInvestigationAnalyzer $analyzer): JsonResponse
    {
        abort_unless($request->user()->hasPermission('investigations.read'), 403);
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

    public function store(Request $request): JsonResponse
    {
        abort_unless($request->user()->hasPermission('investigations.read'), 403);
        $validated = $request->validate([
            'case_folio' => ['required', 'string', 'exists:occupational_cases,folio'], 'story' => ['required', 'string', 'max:30000'],
            'model' => ['nullable', 'string', 'max:255'], 'facts' => ['required', 'array', 'min:1'], 'facts.*' => ['required', 'string', 'max:2000'],
            'tree' => ['required', 'array'], 'tree.final' => ['required', 'string', 'max:2000'], 'tree.origin' => ['required', 'string', 'max:2000'],
            'tree.causes' => ['required', 'array', 'min:1'], 'tree.causes.*' => ['required', 'string', 'max:2000'],
            'factor_codes' => ['present', 'array', 'max:8'], 'factor_codes.*' => ['string', 'exists:cause_factors,codigo_efectivo'],
            'measures' => ['required', 'array', 'min:1'], 'measures.*.description' => ['required', 'string', 'max:3000'],
            'measures.*.category' => ['required', 'string', 'max:80'], 'measures.*.responsible' => ['required', 'string', 'max:255'],
            'measures.*.due_date' => ['nullable', 'date'],
        ]);

        $investigation = DB::transaction(function () use ($request, $validated): Investigation {
            $case = OccupationalCase::where('folio', $validated['case_folio'])->firstOrFail();
            $investigation = Investigation::firstOrCreate(['occupational_case_id' => $case->id], [
                'folio' => 'INV-'.now()->year.'-'.str_pad((string) (Investigation::query()->lockForUpdate()->count() + 1), 3, '0', STR_PAD_LEFT),
                'owner_id' => $request->user()->id,
            ]);
            $investigation->update(['final_story' => $validated['story'], 'ai_model' => $validated['model'] ?? null, 'ai_analyzed_at' => now()]);
            $investigation->facts()->delete();
            $investigation->causalNodes()->delete();
            $investigation->measures()->delete();
            foreach ($validated['facts'] as $position => $fact) {
                $investigation->facts()->create(['position' => $position + 1, 'description' => $fact, 'ai_generated' => true]);
            }
            $investigation->causalNodes()->create(['node_type' => 'final', 'position' => 1, 'description' => $validated['tree']['final'], 'ai_generated' => true]);
            $investigation->causalNodes()->create(['node_type' => 'origin', 'position' => 1, 'description' => $validated['tree']['origin'], 'ai_generated' => true]);
            foreach ($validated['tree']['causes'] as $position => $cause) {
                $investigation->causalNodes()->create(['node_type' => 'cause', 'position' => $position + 1, 'description' => $cause, 'ai_generated' => true]);
            }
            $factorIds = CauseFactor::query()->whereIn('codigo_efectivo', $validated['factor_codes'])->pluck('id');
            $investigation->belongsToMany(CauseFactor::class, 'investigation_cause_factor')->sync(
                $factorIds->mapWithKeys(fn (int $id): array => [$id => ['origen' => 'ia', 'validated_by' => $request->user()->id, 'validated_at' => now()]])->all(),
            );
            foreach ($validated['measures'] as $measure) {
                $investigation->measures()->create([...$measure, 'due_date' => $measure['due_date'] ?: null, 'ai_generated' => true]);
            }

            return $investigation;
        });

        return response()->json(['message' => 'Propuesta guardada correctamente.', 'investigation' => $investigation->folio]);
    }

    public function storeInterview(Request $request): JsonResponse
    {
        abort_unless($request->user()->hasPermission('investigations.read'), 403);
        $validated = $request->validate([
            'case_folio' => ['required', 'string', 'exists:occupational_cases,folio'],
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
            ]);
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
}
