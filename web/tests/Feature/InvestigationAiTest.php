<?php

namespace Tests\Feature;

use App\Models\CaseEvidence;
use App\Models\CauseFactor;
use App\Models\Establishment;
use App\Models\InterviewAnswer;
use App\Models\Investigation;
use App\Models\InvestigationInterview;
use App\Models\OccupationalCase;
use App\Models\Role;
use App\Models\User;
use App\Services\LmStudioFinalStoryGenerator;
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

    public function test_model_catalog_exposes_models_available_in_lm_studio(): void
    {
        $user = $this->authorizedUser();
        config(['services.lm_studio.model' => 'qwen2.5-coder-7b-instruct']);
        Http::fake(['*/models' => Http::response(['data' => [
            ['id' => 'lmstudio-community/qwen3.5-4b-GGUF'],
            ['id' => 'lmstudio-community/gemma-4-E4B-it-GGUF'],
        ]])]);

        $this->actingAs($user)->getJson(route('modules.investigations.ai.models'))
            ->assertOk()
            ->assertJsonPath('connected', true)
            ->assertJsonPath('configured_model', 'qwen2.5-coder-7b-instruct')
            ->assertJsonPath('models.0.id', 'lmstudio-community/qwen3.5-4b-GGUF')
            ->assertJsonPath('models.1.id', 'lmstudio-community/gemma-4-E4B-it-GGUF');
    }

    public function test_analysis_stage_uses_the_model_selected_in_the_proposal_view(): void
    {
        $this->seed(InvestigationCatalogSeeder::class);
        $user = $this->authorizedUser();
        config(['services.lm_studio.model' => 'qwen2.5-coder-7b-instruct']);
        Http::fake(['*/chat/completions' => Http::response([
            'choices' => [['message' => ['content' => json_encode(['facts' => [[
                'description' => 'La trabajadora informó dolor en la muñeca a su supervisora.', 'classification' => 'hecho', 'evidence_status' => 'declarado',
                'evidence' => 'la trabajadora informó dolor en la muñeca a su supervisora.', 'source' => 'Relato final',
            ]], 'pending_verifications' => []], JSON_THROW_ON_ERROR)]]],
        ])]);

        $this->actingAs($user)->postJson(route('modules.investigations.ai.facts'), [
            'story' => 'Durante el traslado, la trabajadora informó dolor en la muñeca a su supervisora.',
            'model' => 'lmstudio-community/qwen3.5-4b-GGUF',
        ])->assertOk()->assertJsonPath('model', 'lmstudio-community/qwen3.5-4b-GGUF')->assertJsonPath('facts.0.description', 'La trabajadora informó dolor en la muñeca a su supervisora.');

        Http::assertSent(fn ($request): bool => $request['model'] === 'lmstudio-community/qwen3.5-4b-GGUF');
    }

    public function test_fact_stage_requires_a_literal_supporting_excerpt_and_corrects_event_classification(): void
    {
        $this->seed(InvestigationCatalogSeeder::class);
        $user = $this->authorizedUser();
        Http::fake(['*/chat/completions' => Http::response(['choices' => [['message' => ['content' => json_encode([
            'facts' => [['description' => 'A las 09:15 Elena transitaba por el pasillo con una caja.', 'classification' => 'permanente', 'evidence_status' => 'declarado', 'evidence' => 'A las 09:15 Elena transitaba por el pasillo con una caja.', 'source' => 'Relato final']],
            'pending_verifications' => [],
        ], JSON_THROW_ON_ERROR)]]]])]);

        $this->actingAs($user)->postJson(route('modules.investigations.ai.facts'), [
            'story' => 'A las 09:15 Elena transitaba por el pasillo con una caja.',
        ])->assertOk()->assertJsonPath('facts.0.classification', 'hecho')
            ->assertJsonPath('facts.0.evidence_status', 'declarado')
            ->assertJsonPath('facts.0.evidence', 'A las 09:15 Elena transitaba por el pasillo con una caja.')
            ->assertJsonPath('facts.0.source', 'Relato final');
    }

    public function test_tree_stage_rejects_nodes_that_do_not_match_reviewed_facts(): void
    {
        $this->seed(InvestigationCatalogSeeder::class);
        $user = $this->authorizedUser();
        Http::fake(['*/chat/completions' => Http::response(['choices' => [['message' => ['content' => json_encode(['tree' => [
            'final_node_id' => 'N3',
            'nodes' => [
                ['id' => 'N1', 'type' => 'permanente', 'description' => 'El perfil antideslizante del pasillo se encontraba levantado.'],
                ['id' => 'N2', 'type' => 'hecho', 'description' => 'Elena se golpeó el codo izquierdo durante la caída.'],
                ['id' => 'N3', 'type' => 'hecho', 'description' => 'Marta encontró después a Elena en el suelo.'],
            ],
            'edges' => [
                ['from' => 'N1', 'to' => 'N2', 'link_type' => 'causal', 'logic' => 'cadena', 'basis' => 'La caída se vinculó con el perfil.', 'evidence' => 'El perfil antideslizante del pasillo se encontraba levantado.'],
                ['from' => 'N2', 'to' => 'N3', 'link_type' => 'aparente', 'logic' => 'cadena', 'basis' => 'Marta llegó después.', 'evidence' => 'Elena se golpeó el codo izquierdo durante la caída.'],
            ],
        ]], JSON_THROW_ON_ERROR)]]]])]);

        $facts = [
            ['description' => 'El perfil del pasillo se encontraba levantado.', 'classification' => 'permanente', 'evidence_status' => 'verificado', 'evidence' => 'La inspección confirmó el perfil levantado.', 'source' => 'Inspección'],
            ['description' => 'Elena se golpeó el codo izquierdo durante la caída.', 'classification' => 'hecho', 'evidence_status' => 'declarado', 'evidence' => 'Elena declaró que cayó golpeándose el codo izquierdo.', 'source' => 'Elena'],
        ];
        $this->actingAs($user)->postJson(route('modules.investigations.ai.tree'), [
            'story' => 'Elena cayó y se golpeó el codo izquierdo junto a un perfil levantado.', 'facts' => $facts,
        ])->assertStatus(503)->assertJsonPath('message', 'La propuesta no superó la validación de esta etapa: Cada nodo debe conservar un ID Hn de los hechos revisados; se recibió un hecho inexistente. No se incorporó al resultado.');
    }

    public function test_tree_stage_keeps_pre_event_humidity_apparent_without_forcing_a_root_cause(): void
    {
        $this->seed(InvestigationCatalogSeeder::class);
        $user = $this->authorizedUser();
        config(['services.lm_studio.model' => 'qwen2.5-7b-instruct']);
        $facts = [
            ['description' => 'Diego resbaló y cayó en el acceso a la bodega de farmacia.', 'classification' => 'hecho', 'evidence_status' => 'declarado',
                'evidence' => 'Diego caminaba por el acceso a la bodega de farmacia cuando resbaló y cayó', 'source' => 'Diego'],
            ['description' => 'Diego se golpeó la rodilla derecha al caer.', 'classification' => 'hecho', 'evidence_status' => 'declarado',
                'evidence' => 'golpeándose la rodilla derecha', 'source' => 'Diego'],
            ['description' => 'Rocío observó humedad en una parte del acceso unos minutos antes de la caída.', 'classification' => 'hecho', 'evidence_status' => 'declarado',
                'evidence' => 'Rocío declaró que vio humedad en una parte del acceso unos minutos antes; no presenció la caída ni identificó su origen.', 'source' => 'Rocío'],
        ];
        $tree = ['tree' => [
            'final_node_id' => 'H99',
            'nodes' => [
                ['id' => 'H1', 'type' => 'hecho', 'description' => $facts[0]['description']],
                ['id' => 'H2', 'type' => 'hecho', 'description' => $facts[1]['description']],
                ['id' => 'H3', 'type' => 'hecho', 'description' => $facts[2]['description']],
            ],
            'edges' => [
                ['from' => 'H1', 'to' => 'H2', 'link_type' => 'causal', 'logic' => 'cadena',
                    'basis' => 'La declaración vincula directamente la caída con el golpe en la rodilla.', 'evidence' => 'golpeándose la rodilla derecha'],
                ['from' => 'H3', 'to' => 'H1', 'link_type' => 'aparente', 'logic' => 'cadena',
                    'basis' => 'La humedad fue observada antes, pero Rocío no presenció la caída y no se confirmó la condición del piso durante el evento.',
                    'evidence' => 'Rocío declaró que vio humedad en una parte del acceso unos minutos antes'],
            ],
        ]];
        Http::fake(['*/chat/completions' => Http::response(['choices' => [['message' => ['content' => json_encode($tree, JSON_THROW_ON_ERROR)]]]])]);

        $story = 'Diego caminaba por el acceso a la bodega de farmacia cuando resbaló y cayó, golpeándose la rodilla derecha. Después observó humedad en el piso, sin identificar su origen. Rocío declaró que vio humedad en una parte del acceso unos minutos antes; no presenció la caída ni identificó su origen. La inspección posterior no confirmó el estado del piso ni la presencia de señalización.';
        $this->actingAs($user)->postJson(route('modules.investigations.ai.tree'), ['story' => $story, 'facts' => $facts])
            ->assertOk()
            ->assertJsonPath('prompt_version', 'mac-etapas-v17')
            ->assertJsonPath('tree.final_node_id', 'H2')
            ->assertJsonPath('tree.edges.0.link_type', 'causal')
            ->assertJsonPath('tree.edges.1.link_type', 'aparente')
            ->assertJsonPath('quality_warnings.0', 'El modelo indicó un hecho final inexistente; se seleccionó H2 por corresponder al daño explícito revisado. Confirme que sea la consecuencia final correcta.');

        Http::assertSent(fn ($request): bool => str_contains($request['messages'][0]['content'], 'prueba de necesidad')
            && str_contains($request['messages'][0]['content'], 'No es obligatorio identificar una “causa raíz” única')
            && str_contains($request['messages'][0]['content'], 'nunca la uses para completar una ruta')
            && str_contains($request['messages'][1]['content'], 'no presenció la caída'));
    }

    public function test_tree_stage_downgrades_routine_context_and_witness_observation_to_apparent_links(): void
    {
        $this->seed(InvestigationCatalogSeeder::class);
        $user = $this->authorizedUser();
        config(['services.lm_studio.model' => 'qwen2.5-7b-instruct']);
        $facts = [
            ['description' => 'La trabajadora entró al pasillo trasladando una caja.', 'classification' => 'hecho', 'evidence_status' => 'declarado', 'evidence' => 'La trabajadora entró al pasillo trasladando una caja.', 'source' => 'Relato final'],
            ['description' => 'El pie derecho contactó el perfil levantado.', 'classification' => 'hecho', 'evidence_status' => 'declarado', 'evidence' => 'El pie derecho contactó el perfil levantado.', 'source' => 'Relato final'],
            ['description' => 'La trabajadora cayó al mismo nivel.', 'classification' => 'hecho', 'evidence_status' => 'declarado', 'evidence' => 'La trabajadora cayó al mismo nivel.', 'source' => 'Relato final'],
            ['description' => 'La trabajadora se golpeó el codo izquierdo.', 'classification' => 'hecho', 'evidence_status' => 'declarado', 'evidence' => 'La trabajadora cayó y se golpeó el codo izquierdo.', 'source' => 'Relato final'],
            ['description' => 'Marta vio un extremo del perfil separado del piso.', 'classification' => 'hecho', 'evidence_status' => 'declarado', 'evidence' => 'Marta vio un extremo del perfil separado del piso.', 'source' => 'Marta'],
        ];
        $tree = ['tree' => [
            'final_node_id' => 'H4',
            'nodes' => array_map(fn (array $fact, int $index): array => ['id' => 'H'.($index + 1), 'type' => $fact['classification'], 'description' => $fact['description']], $facts, array_keys($facts)),
            'edges' => [
                ['from' => 'H1', 'to' => 'H2', 'link_type' => 'causal', 'logic' => 'cadena', 'basis' => 'La trabajadora entró antes del contacto.', 'evidence' => 'La trabajadora entró al pasillo trasladando una caja.'],
                ['from' => 'H2', 'to' => 'H3', 'link_type' => 'causal', 'logic' => 'cadena', 'basis' => 'El pie contactó el perfil y la trabajadora cayó.', 'evidence' => 'El pie derecho contactó el perfil levantado.'],
                ['from' => 'H3', 'to' => 'H4', 'link_type' => 'causal', 'logic' => 'cadena', 'basis' => 'La caída se produjo junto con el golpe.', 'evidence' => 'La trabajadora cayó y se golpeó el codo izquierdo.'],
                ['from' => 'H5', 'to' => 'H2', 'link_type' => 'causal', 'logic' => 'cadena', 'basis' => 'La testigo vio el perfil antes.', 'evidence' => 'Marta vio un extremo del perfil separado del piso.'],
            ],
        ]];
        Http::fake(['*/chat/completions' => Http::response(['choices' => [['message' => ['content' => json_encode($tree, JSON_THROW_ON_ERROR)]]]])]);

        $this->actingAs($user)->postJson(route('modules.investigations.ai.tree'), ['story' => 'Caso de prueba.', 'facts' => $facts])
            ->assertOk()->assertJsonPath('prompt_version', 'mac-etapas-v17')
            ->assertJsonPath('tree.final_node_id', 'H4')
            ->assertJsonPath('tree.edges.0.link_type', 'aparente')
            ->assertJsonPath('tree.edges.1.link_type', 'causal')
            ->assertJsonPath('tree.edges.2.link_type', 'causal')
            ->assertJsonPath('tree.edges.3.link_type', 'aparente');
    }

    public function test_local_case_agent_answers_from_server_read_only_case_and_interview_context(): void
    {
        $this->seed(InvestigationCatalogSeeder::class);
        $user = $this->authorizedUser();
        $case = $this->occupationalCase($user);
        $investigation = Investigation::create([
            'occupational_case_id' => $case->id, 'folio' => 'INV-2026-901', 'owner_id' => $user->id,
        ]);
        $interview = InvestigationInterview::create([
            'investigation_id' => $investigation->id, 'created_by' => $user->id,
            'interviewee_name' => 'Testigo Parcial', 'interviewee_role' => 'Auxiliar',
            'witness_type' => 'Testigo presencial parcial', 'status' => 'Completada',
        ]);
        InterviewAnswer::create([
            'investigation_interview_id' => $interview->id,
            'question_key' => 'observed_event', 'answer_type' => 'text',
            'value' => ['Vio que el carro se detuvo, pero no observó la molestia de la trabajadora.'],
        ]);
        config(['services.lm_studio.model' => 'qwen2.5-7b-instruct']);
        Http::fake(['*/chat/completions' => Http::response([
            'choices' => [['message' => ['content' => 'El testigo declaró que vio detenerse el carro, pero no observó la molestia. El relato inicial informa una caída en una superficie húmeda.']]],
        ])]);

        $this->actingAs($user)->postJson(route('modules.investigations.ai.agent'), [
            'case_folio' => $case->folio,
            'question' => '¿Qué observó el testigo y qué información corresponde al relato inicial?',
        ])->assertOk()
            ->assertJsonPath('model', 'qwen2.5-7b-instruct')
            ->assertJsonPath('tools_used.0', 'read_case_context')
            ->assertJsonPath('read_only', true)
            ->assertJsonPath('answer', 'El testigo declaró que vio detenerse el carro, pero no observó la molestia. El relato inicial informa una caída en una superficie húmeda.');

        Http::assertSentCount(1);
        Http::assertSent(fn ($request): bool => ! isset($request['tools'])
            && str_contains($request['messages'][1]['content'], 'Testigo Parcial')
            && str_contains($request['messages'][1]['content'], 'no observó la molestia')
            && str_contains($request['messages'][1]['content'], 'CONSULTA DEL PREVENCIONISTA'));
        $this->assertDatabaseCount('investigation_interviews', 1);
        $this->assertDatabaseCount('interview_answers', 1);
    }

    public function test_local_case_agent_uses_read_only_matrix_search_only_for_matrix_questions(): void
    {
        $this->seed(InvestigationCatalogSeeder::class);
        $user = $this->authorizedUser();
        $case = $this->occupationalCase($user);
        $factor = CauseFactor::query()->where('activo', true)->firstOrFail();
        config(['services.lm_studio.model' => 'qwen2.5-7b-instruct']);
        Http::fake(['*/chat/completions' => Http::sequence()
            ->push(['choices' => [['message' => ['content' => "```json\n{\"name\":\"search_cause_factors\",\"arguments\":{\"query\":\"{$factor->categoria}\"}}\n```"]]]])
            ->push(['choices' => [['message' => ['content' => 'La búsqueda encontró factores de la matriz relacionados con la consulta; deben revisarse contra los hechos del caso.']]]])]);

        $this->actingAs($user)->postJson(route('modules.investigations.ai.agent'), [
            'case_folio' => $case->folio,
            'question' => '¿Qué factores causales aparecen en la matriz relacionados con este caso?',
        ])->assertOk()
            ->assertJsonPath('tools_used.1', 'search_cause_factors')
            ->assertJsonPath('read_only', true);

        Http::assertSentCount(2);
        Http::assertSent(fn ($request): bool => collect($request['tools'] ?? [])->pluck('function.name')->all() === ['search_cause_factors']);
        Http::assertSent(fn ($request): bool => collect($request['messages'])->contains(fn (array $message): bool => ($message['role'] ?? '') === 'tool'
            && str_contains($message['content'] ?? '', $factor->codigo_efectivo)));
    }

    public function test_staged_fact_prompt_handles_partial_testimony_and_marks_unverified_quotes(): void
    {
        $this->seed(InvestigationCatalogSeeder::class);
        $user = $this->authorizedUser();
        config(['services.lm_studio.model' => 'qwen2.5-7b-instruct']);
        Http::fake(['*/chat/completions' => Http::response([
            'choices' => [['message' => ['content' => json_encode([
                'facts' => [
                    ['description' => 'La trabajadora cayó en el pasillo.', 'classification' => 'hecho', 'evidence_status' => 'declarado', 'evidence' => 'La trabajadora cayó en el pasillo.', 'source' => 'Trabajadora afectada'],
                    ['description' => 'Una compañera observó humedad antes del accidente.', 'classification' => 'hecho', 'evidence_status' => 'declarado', 'evidence' => 'Una compañera observó humedad antes del accidente', 'source' => 'Compañera'],
                ],
                'pending_verifications' => ['No se ha inspeccionado el origen de la humedad.'],
            ], JSON_THROW_ON_ERROR)]]],
        ])]);

        $this->actingAs($user)->postJson(route('modules.investigations.ai.facts'), [
            'story' => 'La trabajadora cayó en el pasillo. Una compañera observó humedad antes del accidente, pero no presenció la caída ni conoce el origen de la humedad.',
        ])->assertOk()->assertJsonCount(2, 'facts')->assertJsonPath('pending_verifications.0', 'No se ha inspeccionado el origen de la humedad.')
            ->assertJsonPath('facts.1.source', 'Compañera');
        Http::assertSent(fn ($request): bool => str_contains($request['messages'][0]['content'], 'pending_verifications')
            && str_contains($request['messages'][0]['content'], 'No conviertas proximidad temporal en causalidad.')
            && str_contains($request['messages'][1]['content'], 'Una compañera observó humedad antes del accidente'));
    }

    public function test_staged_fact_output_corrects_event_classification_deduplicates_exact_facts_and_separates_gaps(): void
    {
        $this->seed(InvestigationCatalogSeeder::class);
        $user = $this->authorizedUser();
        config(['services.lm_studio.model' => 'qwen2.5-7b-instruct']);
        $facts = [
            ['description' => 'A las 10:20, Camila empujaba una caja en un carro.', 'classification' => 'permanente', 'evidence_status' => 'declarado', 'evidence' => 'A las 10:20, Camila empujaba una caja en un carro.', 'source' => 'Camila'],
            ['description' => 'A las 10:20, Camila empujaba una caja en un carro.', 'classification' => 'permanente', 'evidence_status' => 'declarado', 'evidence' => 'A las 10:20, Camila empujaba una caja en un carro.', 'source' => 'Camila'],
            ['description' => 'Camila no recuerda si la unión del piso estaba desnivelada.', 'classification' => 'permanente', 'evidence_status' => 'declarado', 'evidence' => 'Camila no recuerda si la unión del piso estaba desnivelada.', 'source' => 'Camila'],
        ];
        Http::fake(['*/chat/completions' => Http::response([
            'choices' => [['message' => ['content' => json_encode(['facts' => $facts, 'pending_verifications' => []], JSON_THROW_ON_ERROR)]]],
        ])]);

        $response = $this->actingAs($user)->postJson(route('modules.investigations.ai.facts'), [
            'story' => implode(' ', array_column($facts, 'evidence')),
        ])->assertOk()->assertJsonCount(1, 'facts')->assertJsonCount(1, 'pending_verifications');

        $normalizedFacts = $response->json('facts');
        $this->assertSame('hecho', $normalizedFacts[0]['classification']);
        $this->assertSame('A las 10:20, Camila empujaba una caja en un carro.', $normalizedFacts[0]['evidence']);
        $this->assertSame('Camila no recuerda si la unión del piso estaba desnivelada.', $response->json('pending_verifications.0'));
    }

    public function test_staged_fact_output_keeps_supported_facts_and_warns_when_omitting_unsupported_fact(): void
    {
        $this->seed(InvestigationCatalogSeeder::class);
        $user = $this->authorizedUser();
        config(['services.lm_studio.model' => 'qwen2.5-7b-instruct']);
        Http::fake(['*/chat/completions' => Http::response([
            'choices' => [['message' => ['content' => json_encode(['facts' => [
                ['description' => 'Elena caminaba por el pasillo B.', 'classification' => 'hecho', 'evidence_status' => 'declarado', 'evidence' => 'Elena caminaba por el pasillo B.', 'source' => 'Elena'],
                ['description' => 'Elena ingresó al pasillo B del Centro Norte.', 'classification' => 'hecho', 'evidence_status' => 'declarado', 'evidence' => 'Elena caminaba por el pasillo B.', 'source' => 'Elena'],
            ], 'pending_verifications' => []], JSON_THROW_ON_ERROR)]]],
        ])]);

        $response = $this->actingAs($user)->postJson(route('modules.investigations.ai.facts'), [
            'story' => 'Elena caminaba por el pasillo B.',
        ])->assertOk()->assertJsonCount(1, 'facts')->assertJsonCount(1, 'quality_warnings')
            ->assertJsonPath('facts.0.description', 'Elena caminaba por el pasillo B.')
            ->assertJsonPath('prompt_version', 'mac-etapas-v17');

        $this->assertStringContainsString('se omitió un hecho propuesto', mb_strtolower($response->json('quality_warnings.0')));
    }

    public function test_staged_fact_output_recovers_a_literal_source_sentence_for_a_supported_fact(): void
    {
        $this->seed(InvestigationCatalogSeeder::class);
        $user = $this->authorizedUser();
        config(['services.lm_studio.model' => 'qwen2.5-7b-instruct']);
        Http::fake(['*/chat/completions' => Http::response([
            'choices' => [['message' => ['content' => json_encode(['facts' => [[
                'description' => 'La trabajadora golpea su codo izquierdo durante la caída.', 'classification' => 'hecho', 'evidence_status' => 'declarado',
                'evidence' => 'La trabajadora cayó y se golpeó el codo izquierdo.', 'source' => 'Trabajadora',
            ], [
                'description' => 'En inspección se midieron 18 mm de elevación en el perfil.', 'classification' => 'permanente', 'evidence_status' => 'verificado',
                'evidence' => 'Se midieron 18 mm de elevación máxima en el perfil.', 'source' => 'Relato final',
            ], [
                'description' => 'Iván revisó la OT-DEM-204 del perfil antideslizante.', 'classification' => 'hecho', 'evidence_status' => 'declarado',
                'evidence' => 'Iván revisó la OT-DEM-204 del perfil antideslizante.', 'source' => 'Iván',
            ]], 'pending_verifications' => []], JSON_THROW_ON_ERROR)]]],
        ])]);
        $story = 'La trabajadora declaró que cayó al mismo nivel, golpeándose el codo izquierdo. En inspección se midieron 18 mm de elevación en el perfil. Iván revisó la OT-DEM-204 del perfil antideslizante.';

        $this->actingAs($user)->postJson(route('modules.investigations.ai.facts'), ['story' => $story])
            ->assertOk()->assertJsonCount(1, 'facts')->assertJsonCount(2, 'pending_verifications')->assertJsonPath('prompt_version', 'mac-etapas-v17')
            ->assertJsonPath('facts.0.evidence', 'La trabajadora declaró que cayó al mismo nivel, golpeándose el codo izquierdo.')
            ->assertJsonPath('quality_warnings.0', 'Se reemplazó una cita alterada por una oración literal del relato que respalda el hecho; revísela junto con la atribución.');
    }

    public function test_story_generator_explicitly_disclaims_unprovided_attached_evidence(): void
    {
        $user = $this->authorizedUser();
        $case = $this->occupationalCase($user);
        CaseEvidence::create([
            'occupational_case_id' => $case->id, 'uploaded_by' => $user->id,
            'original_name' => 'fotografia_sintetica.png', 'path' => 'cases/prueba/fotografia_sintetica.png',
            'mime_type' => 'image/png', 'size' => 256,
        ]);
        CaseEvidence::create([
            'occupational_case_id' => $case->id, 'uploaded_by' => $user->id,
            'original_name' => 'informe_sintetico.pdf', 'path' => 'cases/prueba/informe_sintetico.pdf',
            'mime_type' => 'application/pdf', 'size' => 512,
        ]);
        config(['services.lm_studio.model' => 'qwen2.5-7b-instruct']);
        $draft = ['paragraphs' => [[
            'text' => 'Durante la jornada, la persona trabajadora informó una caída.',
            'source_ids' => ['CASO'],
            'supporting_quotes' => [['source_id' => 'CASO', 'quote' => 'Durante el traslado se informó una caída en una superficie húmeda.']],
        ]], 'omitted_sources' => []];
        Http::fake(['*/chat/completions' => Http::response(['choices' => [['message' => ['content' => json_encode($draft, JSON_THROW_ON_ERROR)]]]])]);

        $result = app(LmStudioFinalStoryGenerator::class)->generate($case);
        $this->assertFalse($result['review_completed']);
        Http::assertSent(fn ($request): bool => str_contains($request['messages'][0]['content'], 'No tienes acceso a archivos, imágenes, internet')
            && ! str_contains($request['messages'][1]['content'], 'fotografia_sintetica.png')
            && ! str_contains($request['messages'][1]['content'], 'cases/prueba/fotografia_sintetica.png')
            && ! str_contains($request['messages'][1]['content'], 'informe_sintetico.pdf')
            && ! str_contains($request['messages'][1]['content'], 'cases/prueba/informe_sintetico.pdf'));
    }

    public function test_final_story_integrates_case_and_interviews_in_one_chronological_request(): void
    {
        $user = $this->authorizedUser();
        $case = $this->occupationalCase($user);
        $case->update([
            'narrative' => 'La trabajadora trasladaba una caja cuando sintió dolor en la muñeca.',
            'witnesses' => 'Revisión de cámara: se observa el contacto con el perfil antes de la caída. Acta posterior: se midió el perfil levantado.',
            'details' => ['ai_evidence_sources' => [[
                'id' => 'EVD-TEST', 'source' => 'Cámara de seguridad', 'recorded_at' => '09:15',
                'facts' => 'Se observa el contacto con el perfil antes de la caída.', 'limitations' => 'No acredita diagnóstico.',
            ]]],
        ]);
        $investigation = Investigation::create([
            'occupational_case_id' => $case->id, 'folio' => 'INV-2026-902', 'owner_id' => $user->id,
        ]);
        $matias = InvestigationInterview::create([
            'investigation_id' => $investigation->id, 'created_by' => $user->id,
            'interviewee_name' => 'Matías de prueba', 'interviewee_role' => 'Auxiliar',
            'witness_type' => 'indirect', 'status' => 'Completada',
        ]);
        InterviewAnswer::create([
            'investigation_interview_id' => $matias->id, 'question_key' => 'observed_event',
            'answer_type' => 'text', 'value' => ['Vio el carro detenido, pero no observó cuándo comenzó el dolor.'],
        ]);
        $rocio = InvestigationInterview::create([
            'investigation_id' => $investigation->id, 'created_by' => $user->id,
            'interviewee_name' => 'Rocío de prueba', 'interviewee_role' => 'Administrativa',
            'witness_type' => 'context', 'status' => 'Completada',
        ]);
        InterviewAnswer::create([
            'investigation_interview_id' => $rocio->id, 'question_key' => 'before_event',
            'answer_type' => 'text', 'value' => ['Observó humedad en el acceso antes del evento.'],
        ]);
        config(['services.lm_studio.model' => 'qwen2.5-coder-7b-instruct']);
        Http::preventStrayRequests();
        $paragraphs = [
            ['text' => 'La trabajadora trasladaba una caja cuando sintió dolor en la muñeca.', 'source_ids' => ['CASO'], 'supporting_quotes' => [
                ['source_id' => 'CASO', 'quote' => 'La trabajadora trasladaba una caja cuando sintió dolor en la muñeca.'],
            ]],
            ['text' => 'Matías vio el carro detenido y Rocío observó humedad antes del evento.', 'source_ids' => ['ENT-01', 'ENT-02'], 'supporting_quotes' => [
                ['source_id' => 'ENT-01', 'quote' => 'Vio el carro detenido, pero no observó cuándo comenzó el dolor.'],
                ['source_id' => 'ENT-02', 'quote' => 'Observó humedad en el acceso antes del evento.'],
            ]],
            ['text' => 'El registro documental describe contacto con el perfil antes de la caída; el expediente también registra antecedentes del evento.', 'source_ids' => ['EVD-TEST', 'ANTECEDENTES'], 'supporting_quotes' => [
                ['source_id' => 'EVD-TEST', 'quote' => 'Se observa el contacto con el perfil antes de la caída.'],
                ['source_id' => 'ANTECEDENTES', 'quote' => 'Revisión de cámara: se observa el contacto con el perfil antes de la caída. Acta posterior: se midió el perfil levantado.'],
            ]],
        ];
        $draft = ['paragraphs' => $paragraphs, 'omitted_sources' => []];
        $story = collect($paragraphs)->pluck('text')->implode("\n\n");
        Http::fake(['*/chat/completions' => Http::response(['choices' => [[
            'message' => ['content' => json_encode($draft, JSON_THROW_ON_ERROR)], 'finish_reason' => 'stop',
        ]]])]);

        $result = app(LmStudioFinalStoryGenerator::class)->generate($case->load('investigation.interviews.answers.question'), 'lmstudio-community/qwen3.5-4b-GGUF');

        $this->assertSame(2, $result['interviews_used']);
        $this->assertSame('lmstudio-community/qwen3.5-4b-GGUF', $result['model']);
        $this->assertFalse($result['review_completed']);
        $this->assertSame(5, $result['sources_generated']);
        $this->assertSame($paragraphs[1]['text']."\n\n".$paragraphs[2]['text']."\n\n".$paragraphs[0]['text'], $result['story']);
        $this->assertTrue($result['quality']['chronology_adjusted']);
        Http::assertSentCount(1);
        Http::assertSent(fn ($request): bool => $request['model'] === 'lmstudio-community/qwen3.5-4b-GGUF'
            && str_contains($request['messages'][0]['content'], 'relato final único')
            && str_contains($request['messages'][0]['content'], 'Distingue declaración, observación directa')
            && str_contains($request['messages'][1]['content'], 'relato_inicial')
            && str_contains($request['messages'][1]['content'], 'antecedentes_y_testigos_registrados')
            && str_contains($request['messages'][1]['content'], 'fuente_documental')
            && str_contains($request['messages'][1]['content'], 'EVD-TEST')
            && str_contains($request['messages'][1]['content'], 'Matías de prueba')
            && str_contains($request['messages'][1]['content'], 'Rocío de prueba')
            && strpos($request['messages'][1]['content'], 'ENT-01') < strpos($request['messages'][1]['content'], 'EVD-TEST')
            && $request['response_format']['json_schema']['schema']['properties']['paragraphs']['minItems'] === 1
            && $request['response_format']['json_schema']['schema']['properties']['paragraphs']['maxItems'] === 6);
    }

    public function test_final_story_rejects_lm_studio_output_truncated_by_token_limit(): void
    {
        $user = $this->authorizedUser();
        $case = $this->occupationalCase($user);
        Http::preventStrayRequests();
        Http::fake(['*/chat/completions' => Http::response(['choices' => [[
            'message' => ['content' => 'La persona trabajadora se encontraba realizando una tarea y luego'],
            'finish_reason' => 'length',
        ]]])]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('cortó el relato');

        app(LmStudioFinalStoryGenerator::class)->generate($case, 'google/gemma-4-e4b');
    }

    public function test_ai_analysis_uses_matrix_codes_and_the_selected_qwen_model(): void
    {
        $this->seed(InvestigationCatalogSeeder::class);
        $user = $this->authorizedUser();
        config(['services.lm_studio.model' => 'qwen2.5-coder-7b-instruct']);
        Http::fake(['*/chat/completions' => Http::response([
            'choices' => [['message' => ['content' => json_encode([
                'facts' => [['description' => 'La trabajadora cayó en el pasillo.', 'classification' => 'hecho', 'evidence_status' => 'declarado', 'evidence' => 'se informó una caída', 'source' => 'Relato final']],
                'tree' => [
                    'final_node_id' => 'H2',
                    'nodes' => [
                        ['id' => 'P1', 'type' => 'permanente', 'description' => 'Superficie húmeda en el pasillo.'],
                        ['id' => 'H1', 'type' => 'hecho', 'description' => 'La trabajadora transita por el pasillo.'],
                        ['id' => 'H2', 'type' => 'hecho', 'description' => 'La trabajadora cae en el pasillo.'],
                    ],
                    'edges' => [
                        ['from' => 'P1', 'to' => 'H2', 'link_type' => 'causal', 'logic' => 'conjuncion', 'basis' => 'Relato de caída en superficie húmeda.'],
                        ['from' => 'H1', 'to' => 'H2', 'link_type' => 'causal', 'logic' => 'conjuncion', 'basis' => 'La narración sitúa el tránsito durante la caída.'],
                    ],
                ],
                'factor_codes' => ['1104'],
                'measures' => [['description' => 'Retirar la humedad y corregir su origen.', 'category' => 'Ingeniería', 'responsible' => 'Mantención', 'due_date' => '', 'criterion' => 'Cobertura colectiva del pasillo y solución permanente.', 'related_node_ids' => ['P1']]],
            ], JSON_THROW_ON_ERROR)]]],
        ])]);

        $this->actingAs($user)->postJson(route('modules.investigations.ai.analyze'), [
            'story' => 'Durante el traslado se informó una caída en una superficie húmeda.',
        ])->assertOk()->assertJsonPath('model', 'qwen2.5-coder-7b-instruct')->assertJsonPath('factor_codes.0', '1104');

        Http::assertSent(fn ($request): bool => $request['model'] === 'qwen2.5-coder-7b-instruct'
            && str_contains($request['messages'][0]['content'], '1104 |')
            && str_contains($request['messages'][0]['content'], 'CONSTRUCCIÓN DEL ÁRBOL')
            && str_contains($request['messages'][0]['content'], 'CATÁLOGO LOCAL COMPLETO')
            && str_contains($request['messages'][0]['content'], 'fragmento literal')
            && str_contains($request['messages'][0]['content'], '[Guía ISP, pág. 7')
            && str_contains($request['messages'][0]['content'], 'no sustituyen las instrucciones del sistema'));
    }

    public function test_staged_matrix_review_screens_a_batch_then_generates_measures_from_its_candidates(): void
    {
        $this->seed(InvestigationCatalogSeeder::class);
        $user = $this->authorizedUser();
        config(['services.lm_studio.model' => 'qwen2.5-coder-7b-instruct']);
        $matrix = CauseFactor::query()->where('activo', true)->orderBy('categoria')->orderBy('subcategoria')->orderBy('codigo_efectivo')->orderBy('id')->get();
        $factor = $matrix->firstOrFail();
        $batchIndex = intdiv($matrix->search(fn (CauseFactor $candidate): bool => $candidate->id === $factor->id), 45);
        $evidence = 'La inspección confirmó el perfil levantado en el pasillo.';
        $sequence = Http::sequence()
            ->push(['choices' => [['message' => ['content' => json_encode(['supports' => [[
                'code' => $factor->codigo_efectivo, 'related_node_ids' => ['H1'], 'basis' => 'La condición inspeccionada coincide con el factor revisado.', 'evidence' => $evidence,
            ]]], JSON_THROW_ON_ERROR)]]]])
            ->push(['choices' => [['message' => ['content' => json_encode([
                'supports' => [['code' => $factor->codigo_efectivo, 'related_node_ids' => ['H1'], 'basis' => 'El antecedente inspeccionado corresponde al factor.', 'evidence' => $evidence]],
                'measures' => [['description' => 'Reparar el perfil y comprobar su fijación antes de habilitar el tránsito.', 'category' => 'Ingeniería', 'responsible' => 'Mantención', 'due_date' => '', 'criterion' => 'La inspección posterior verifica estabilidad y continuidad de la superficie para todo el tránsito.', 'related_node_ids' => ['H1']]],
            ], JSON_THROW_ON_ERROR)]]]]);
        Http::fake(['*/chat/completions' => $sequence]);
        $tree = [
            'final_node_id' => 'H2',
            'nodes' => [
                ['id' => 'H1', 'type' => 'permanente', 'description' => $evidence],
                ['id' => 'H2', 'type' => 'hecho', 'description' => 'La trabajadora cae en el pasillo.'],
            ],
            'edges' => [['from' => 'H1', 'to' => 'H2', 'link_type' => 'causal', 'logic' => 'cadena', 'basis' => 'La condición precede a la caída y requiere revisión investigativa.', 'evidence' => $evidence]],
        ];
        $payload = [
            'story' => 'La trabajadora cayó en un pasillo cuya superficie presentaba humedad.',
            'facts' => [
                ['description' => $evidence, 'classification' => 'permanente', 'evidence_status' => 'verificado', 'evidence' => $evidence, 'source' => 'Inspección'],
                ['description' => 'La trabajadora cae en el pasillo.', 'classification' => 'hecho', 'evidence_status' => 'declarado', 'evidence' => 'La trabajadora cayó en el pasillo.', 'source' => 'Relato final'],
            ],
            'tree' => $tree,
        ];

        $this->actingAs($user)->postJson(route('modules.investigations.ai.factor-batch'), [...$payload, 'batch_index' => $batchIndex])
            ->assertOk()->assertJsonPath('batch_index', $batchIndex)->assertJsonPath('candidate_codes.0', $factor->codigo_efectivo);
        $this->actingAs($user)->postJson(route('modules.investigations.ai.measures'), [...$payload, 'candidate_codes' => [$factor->codigo_efectivo]])
            ->assertOk()->assertJsonPath('factor_codes.0', $factor->codigo_efectivo)->assertJsonPath('measures.0.related_node_ids.0', 'H1');

        Http::assertSentCount(2);
        Http::assertSent(fn ($request): bool => str_contains($request['messages'][0]['content'], 'ETAPA 3A · COTEJO CON MATRIZ LOCAL'));
    }

    public function test_proposal_can_be_saved_without_measures_when_the_analysis_does_not_support_any(): void
    {
        $this->seed(InvestigationCatalogSeeder::class);
        $user = $this->authorizedUser();
        $case = $this->occupationalCase($user);

        $this->actingAs($user)->postJson(route('modules.investigations.ai.store'), [
            'case_folio' => $case->folio,
            'story' => 'La persona informó una molestia, sin antecedentes suficientes para establecer una causa.',
            'facts' => ['La persona informó una molestia.'],
            'tree' => [
                'final' => 'La persona informó una molestia.', 'origin' => 'La persona informó una molestia.', 'causes' => [],
                'graph' => [
                    'final_node_id' => 'H1',
                    'nodes' => [['id' => 'H1', 'type' => 'hecho', 'description' => 'La persona informó una molestia.']],
                    'edges' => [],
                ],
            ],
            'factor_codes' => [],
            'measures' => [],
        ])->assertOk()->assertJsonPath('message', 'Propuesta guardada correctamente.');

        $this->assertDatabaseCount('corrective_measures', 0);
        $this->assertDatabaseCount('investigations', 1);
        $this->assertDatabaseCount('causal_nodes', 2);
    }

    public function test_saving_reviewed_proposal_rejects_graph_content_outside_the_fact_list(): void
    {
        $this->seed(InvestigationCatalogSeeder::class);
        $user = $this->authorizedUser();
        $case = $this->occupationalCase($user);
        $payload = [
            'case_folio' => $case->folio,
            'story' => 'La trabajadora cayó.',
            'model' => 'qwen3.5-4b',
            'facts' => ['La trabajadora cayó.'],
            'fact_details' => [[
                'description' => 'La trabajadora cayó.', 'classification' => 'hecho', 'evidence_status' => 'declarado',
                'evidence' => 'La trabajadora cayó.', 'source' => 'Relato final', 'source_ids' => ['CASO'],
            ]],
            'tree' => [
                'final' => 'La trabajadora cayó.', 'origin' => 'La trabajadora cayó.', 'causes' => [],
                'graph' => ['final_node_id' => 'H1', 'nodes' => [['id' => 'H1', 'type' => 'hecho', 'description' => 'La trabajadora cayó.']], 'edges' => []],
            ],
            'factor_codes' => [], 'measures' => [], 'prompt_version' => 'mac-etapas-v4', 'quality_warnings' => [],
        ];
        $this->actingAs($user)->postJson(route('modules.investigations.ai.store'), $payload)->assertOk();
        $investigation = Investigation::query()->firstOrFail();
        $savedGraph = $investigation->causalNodes()->where('node_type', 'graph')->value('description');

        $invalid = $payload;
        $invalid['story'] = 'Este relato no debe reemplazar al guardado.';
        $invalid['tree']['graph']['nodes'][0]['description'] = 'La trabajadora sufrió una lesión no descrita.';
        $this->actingAs($user)->postJson(route('modules.investigations.ai.store'), $invalid)->assertUnprocessable();

        $this->assertSame($savedGraph, $investigation->causalNodes()->where('node_type', 'graph')->value('description'));
        $this->assertSame('La trabajadora cayó.', $investigation->fresh()->final_story);
        $this->assertDatabaseCount('ai_analysis_runs', 1);
    }

    public function test_staged_measures_can_return_empty_when_no_causal_factor_is_supported(): void
    {
        $this->seed(InvestigationCatalogSeeder::class);
        $user = $this->authorizedUser();
        config(['services.lm_studio.model' => 'qwen2.5-coder-7b-instruct']);
        Http::preventStrayRequests();

        $this->actingAs($user)->postJson(route('modules.investigations.ai.measures'), [
            'story' => 'La persona informó una molestia después de la actividad, sin antecedentes suficientes para establecer una causa.',
            'facts' => [[
                'description' => 'La persona informó una molestia.', 'classification' => 'hecho',
                'evidence_status' => 'declarado', 'evidence' => 'informó una molestia', 'source' => 'Relato final',
            ]],
            'tree' => [
                'final_node_id' => 'H1',
                'nodes' => [['id' => 'H1', 'type' => 'hecho', 'description' => 'La persona informó una molestia.']],
                'edges' => [],
            ],
            'candidate_codes' => [],
        ])->assertOk()->assertJsonCount(0, 'factor_codes')->assertJsonCount(0, 'factors')->assertJsonCount(0, 'measures');

        Http::assertNothingSent();
    }

    public function test_staged_measures_rejects_empty_output_for_a_permanent_causal_condition(): void
    {
        $this->seed(InvestigationCatalogSeeder::class);
        $user = $this->authorizedUser();
        config(['services.lm_studio.model' => 'qwen2.5-coder-7b-instruct']);
        $empty = ['supports' => [], 'measures' => []];
        Http::fakeSequence()->push(['choices' => [['message' => ['content' => json_encode($empty, JSON_THROW_ON_ERROR)]]]])
            ->push(['choices' => [['message' => ['content' => json_encode($empty, JSON_THROW_ON_ERROR)]]]]);

        $response = $this->actingAs($user)->postJson(route('modules.investigations.ai.measures'), [
            'story' => 'La inspección verificó que un cable cruzaba el pasillo. La persona tropezó con el cable y cayó.',
            'facts' => [
                ['description' => 'Un cable cruzaba el pasillo.', 'classification' => 'permanente', 'evidence_status' => 'verificado',
                    'evidence' => 'La inspección verificó que un cable cruzaba el pasillo.', 'source' => 'Inspección'],
                ['description' => 'La persona tropezó con el cable y cayó.', 'classification' => 'hecho', 'evidence_status' => 'declarado',
                    'evidence' => 'La persona tropezó con el cable y cayó.', 'source' => 'Relato final'],
            ],
            'tree' => [
                'final_node_id' => 'H2',
                'nodes' => [
                    ['id' => 'H1', 'type' => 'permanente', 'description' => 'Un cable cruzaba el pasillo.'],
                    ['id' => 'H2', 'type' => 'hecho', 'description' => 'La persona tropezó con el cable y cayó.'],
                ],
                'edges' => [['from' => 'H1', 'to' => 'H2', 'link_type' => 'causal', 'logic' => 'cadena',
                    'basis' => 'La persona declaró que tropezó con el cable que cruzaba el pasillo.',
                    'evidence' => 'La inspección verificó que un cable cruzaba el pasillo.']],
            ],
            'candidate_codes' => [],
        ])->assertStatus(503);

        $this->assertStringContainsString('condición permanente', $response->json('message'));
        Http::assertSentCount(2);
    }

    public function test_staged_measures_reject_a_factor_outside_the_screened_candidates(): void
    {
        $this->seed(InvestigationCatalogSeeder::class);
        $user = $this->authorizedUser();
        config(['services.lm_studio.model' => 'qwen2.5-coder-7b-instruct']);
        Http::fake(['*/chat/completions' => Http::response(['choices' => [['message' => ['content' => json_encode([
            'supports' => [['code' => '4114', 'related_node_ids' => ['H1'], 'basis' => 'Correspondencia propuesta con el perfil levantado.', 'evidence' => 'Registro de CCTV: perfil levantado.']],
            'measures' => [],
        ], JSON_THROW_ON_ERROR)]]]])]);

        $this->actingAs($user)->postJson(route('modules.investigations.ai.measures'), [
            'story' => 'La CCTV registra contacto con un perfil levantado en el pasillo y una caída.',
            'facts' => [
                ['description' => 'El perfil antideslizante del pasillo B se encontraba levantado.', 'classification' => 'permanente', 'evidence_status' => 'verificado', 'evidence' => 'Registro de CCTV: perfil levantado.', 'source' => 'CCTV'],
                ['description' => 'La trabajadora cayó.', 'classification' => 'hecho', 'evidence_status' => 'declarado', 'evidence' => 'y una caída', 'source' => 'Relato final'],
            ],
            'tree' => [
                'final_node_id' => 'H2',
                'nodes' => [['id' => 'H1', 'type' => 'permanente', 'description' => 'El perfil antideslizante del pasillo B se encontraba levantado.'], ['id' => 'H2', 'type' => 'hecho', 'description' => 'La trabajadora cayó.']],
                'edges' => [['from' => 'H1', 'to' => 'H2', 'link_type' => 'causal', 'logic' => 'cadena', 'basis' => 'La grabación muestra el contacto con el perfil levantado.', 'evidence' => 'Registro de CCTV: perfil levantado.']],
            ],
            'candidate_codes' => ['4516'],
        ])->assertStatus(503)->assertJsonPath('message', 'La propuesta no superó la validación de esta etapa: Un factor está duplicado o no pertenece al catálogo recibido. No se incorporó al resultado.');
    }

    public function test_proposal_and_both_questionnaire_types_use_shared_tables(): void
    {
        $this->seed(InvestigationCatalogSeeder::class);
        $user = $this->authorizedUser();
        $case = $this->occupationalCase($user);

        $this->actingAs($user)->postJson(route('modules.investigations.ai.store'), [
            'case_folio' => $case->folio, 'story' => $case->narrative, 'model' => 'qwen2.5-coder-7b-instruct',
            'facts' => ['El piso presentaba humedad.'],
            'tree' => [
                'final' => 'Caída', 'origin' => 'Traslado', 'causes' => ['Piso húmedo'],
                'graph' => [
                    'final_node_id' => 'H2',
                    'nodes' => [['id' => 'P1', 'type' => 'permanente', 'description' => 'Piso húmedo'], ['id' => 'H2', 'type' => 'hecho', 'description' => 'Caída']],
                    'edges' => [['from' => 'P1', 'to' => 'H2', 'link_type' => 'causal', 'logic' => 'cadena', 'basis' => 'El relato señala caída en piso húmedo.']],
                ],
            ],
            'factor_codes' => ['1104'],
            'measures' => [['description' => 'Corregir la humedad del piso', 'category' => 'Ingeniería', 'responsible' => 'Mantención', 'due_date' => null, 'criterion' => 'Cobertura de todo el pasillo y solución duradera.', 'related_node_ids' => ['P1']]],
        ])->assertOk();

        $this->assertDatabaseHas('causal_nodes', ['node_type' => 'graph']);
        $this->assertDatabaseHas('corrective_measures', ['responsible' => 'Mantención']);

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
