<?php

namespace Tests\Feature;

use App\Models\Establishment;
use App\Models\OccupationalCase;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\InvestigationCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class InvestigationAiTest extends TestCase
{
    use RefreshDatabase;

    public function test_final_story_is_saved_without_calling_ai(): void
    {
        $user = $this->authorizedUser();
        $case = $this->occupationalCase($user);
        Http::fake();

        $this->actingAs($user)->postJson(route('modules.investigations.story.store'), [
            'case_folio' => $case->folio,
            'story' => 'Relato final redactado y validado por el equipo investigador responsable.',
        ])->assertOk()->assertJsonPath('message', 'Relato final guardado correctamente.');

        $this->assertDatabaseHas('investigations', [
            'occupational_case_id' => $case->id,
            'final_story' => 'Relato final redactado y validado por el equipo investigador responsable.',
            'ai_model' => null,
            'ai_analyzed_at' => null,
        ]);
        Http::assertNothingSent();
    }

    public function test_ai_analysis_uses_matrix_codes_and_the_selected_qwen_model(): void
    {
        $this->seed(InvestigationCatalogSeeder::class);
        $user = $this->authorizedUser();
        config(['services.lm_studio.model' => 'qwen2.5-coder-7b-instruct']);
        Http::fake(['*/chat/completions' => Http::response([
            'choices' => [['message' => ['content' => json_encode([
                'facts' => ['El piso presentaba humedad.'],
                'tree' => ['final' => 'Caída', 'origin' => 'Traslado', 'causes' => ['Piso húmedo']],
                'factor_codes' => ['1104'],
                'measures' => [['description' => 'Inspeccionar el área', 'category' => 'Administrativa', 'responsible' => 'Jefatura', 'due_date' => '']],
            ], JSON_THROW_ON_ERROR)]]],
        ])]);

        $this->actingAs($user)->postJson(route('modules.investigations.ai.analyze'), [
            'story' => 'Durante el traslado se informó una caída en una superficie húmeda.',
        ])->assertOk()->assertJsonPath('model', 'qwen2.5-coder-7b-instruct')->assertJsonPath('factor_codes.0', '1104');

        Http::assertSent(fn ($request): bool => $request['model'] === 'qwen2.5-coder-7b-instruct'
            && str_contains($request['messages'][0]['content'], '1104:'));
    }

    public function test_proposal_and_both_questionnaire_types_use_shared_tables(): void
    {
        $this->seed(InvestigationCatalogSeeder::class);
        $user = $this->authorizedUser();
        $case = $this->occupationalCase($user);

        $this->actingAs($user)->postJson(route('modules.investigations.ai.store'), [
            'case_folio' => $case->folio, 'story' => $case->narrative, 'model' => 'qwen2.5-coder-7b-instruct',
            'facts' => ['El piso presentaba humedad.'],
            'tree' => ['final' => 'Caída', 'origin' => 'Traslado', 'causes' => ['Piso húmedo']],
            'factor_codes' => ['1104'],
            'measures' => [['description' => 'Inspeccionar el área', 'category' => 'Administrativa', 'responsible' => 'Jefatura', 'due_date' => null]],
        ])->assertOk();

        foreach (['adapted', 'legacy'] as $type) {
            $this->actingAs($user)->postJson(route('modules.investigations.interviews.store'), [
                'case_folio' => $case->folio, 'questionnaire_type' => $type,
                'interviewee_name' => 'Testigo de prueba', 'witness_type' => 'Testigo',
                'answers' => ['task_habitual' => 'yes', 'environment_conditions' => ['Piso resbaladizo']],
            ])->assertOk();
        }

        $this->assertDatabaseHas('investigation_cause_factor', ['origen' => 'ia', 'validated_by' => $user->id]);
        $this->assertDatabaseCount('investigation_interviews', 2);
        $this->assertDatabaseHas('interview_answers', ['question_key' => 'task_habitual']);
        $this->assertDatabaseHas('interview_answers', ['question_key' => 'environment_conditions']);
    }

    private function authorizedUser(): User
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::factory()->create(['slug' => 'prevencion', 'name' => 'Prevencionista']));

        return $user;
    }

    private function occupationalCase(User $user): OccupationalCase
    {
        $establishment = Establishment::factory()->create();

        return OccupationalCase::create([
            'folio' => 'GIATEP-2026-900', 'created_by' => $user->id, 'establishment_id' => $establishment->id,
            'case_type' => 'Accidente de trabajo', 'employer_name' => 'Empleador', 'employer_rut' => '69.170.102-6',
            'employer_address' => 'Dirección', 'employer_commune' => 'Los Ángeles', 'economic_activity' => 'Salud',
            'company_type' => 'Principal', 'administrator' => 'ACHS', 'worker_name' => 'Trabajadora',
            'worker_rut' => '17.456.321-8', 'job_title' => 'Técnica', 'workplace' => 'Centro',
            'event_date' => now(), 'narrative' => 'Durante el traslado se informó una caída en una superficie húmeda.',
        ]);
    }
}
