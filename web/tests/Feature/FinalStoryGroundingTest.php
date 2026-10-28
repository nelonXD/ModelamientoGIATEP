<?php

namespace Tests\Feature;

use App\Models\InterviewAnswer;
use App\Models\Investigation;
use App\Models\InvestigationInterview;
use App\Models\OccupationalCase;
use App\Models\QuestionnaireQuestion;
use App\Services\LmStudioFinalStoryGenerator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class FinalStoryGroundingTest extends TestCase
{
    public function test_returns_a_single_grounded_paragraph_without_claiming_professional_validation(): void
    {
        $case = $this->caseRecord('Elena transitaba con una caja y cayó en el pasillo.');
        $paragraph = $this->paragraph('Elena transitaba con una caja y cayó en el pasillo.', 'CASO', $case->narrative);
        Http::preventStrayRequests();
        Http::fake(['*/chat/completions' => Http::response($this->completion([$paragraph]))]);

        $result = app(LmStudioFinalStoryGenerator::class)->generate($case, 'qwen2.5-coder-7b-instruct');

        $this->assertSame($paragraph['text'], $result['story']);
        $this->assertSame($paragraph['supporting_quotes'], $result['source_references'][0]['supporting_quotes']);
        $this->assertFalse($result['review_completed']);
        $this->assertFalse($result['quality']['semantic_verification_completed']);
        $this->assertSame('draft_requires_review', $result['quality']['status']);
        Http::assertSentCount(1);
    }

    public function test_resolves_model_selected_fragment_ids_to_literal_source_quotes(): void
    {
        $case = $this->caseRecord('Elena transitaba con una caja y cayó en el pasillo.');
        $paragraph = [
            'text' => 'Elena transitaba con una caja y cayó en el pasillo.',
            'source_ids' => ['CASO'],
            'supporting_quotes' => [['source_id' => 'CASO', 'segment_id' => 'CASO-relato_inicial']],
        ];
        Http::preventStrayRequests();
        Http::fake(['*/chat/completions' => Http::response($this->completion([$paragraph]))]);

        $result = app(LmStudioFinalStoryGenerator::class)->generate($case, 'qwen2.5-coder-7b-instruct');

        $this->assertSame([['source_id' => 'CASO', 'quote' => $case->narrative]], $result['source_references'][0]['supporting_quotes']);
        Http::assertSent(fn ($request): bool => str_contains($request['messages'][1]['content'], 'CASO-relato_inicial')
            && str_contains($request['messages'][0]['content'], 'segment_id'));
        Http::assertSentCount(1);
    }

    public function test_a_fragment_cannot_be_attributed_to_a_different_source(): void
    {
        $case = $this->caseRecord('Elena cayó en el pasillo.', [['id' => 'ACTA', 'source' => 'Acta', 'facts' => 'El acta registra humedad en el acceso.']]);
        $paragraph = [
            'text' => 'Elena cayó en el pasillo.',
            'source_ids' => ['CASO'],
            'supporting_quotes' => [['source_id' => 'CASO', 'segment_id' => 'ACTA-S01']],
        ];
        Http::preventStrayRequests();
        Http::fake(['*/chat/completions' => Http::response($this->completion([$paragraph]))]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('cita inexistente');

        app(LmStudioFinalStoryGenerator::class)->generate($case, 'qwen2.5-coder-7b-instruct');
    }

    public function test_marks_an_unused_document_for_review_without_asking_model_to_explain_omissions(): void
    {
        $case = $this->caseRecord('Elena cayó en el pasillo.', [['id' => 'ACTA', 'source' => 'Acta de inspección', 'facts' => 'A las 10:02 se midió una altura de 18 mm en el perfil.']]);
        $event = $this->paragraph('Elena cayó en el pasillo.', 'CASO', $case->narrative);
        Http::preventStrayRequests();
        Http::fake(['*/chat/completions' => Http::response($this->completion([$event]))]);

        $result = app(LmStudioFinalStoryGenerator::class)->generate($case, 'qwen2.5-coder-7b-instruct');

        $this->assertCount(1, $result['attempts']);
        $this->assertSame('ACTA', $result['quality']['omitted_sources'][0]['source_id']);
        $this->assertSame('no_incorporada_por_modelo', $result['quality']['omitted_sources'][0]['reason']);
        $this->assertStringContainsString('revise si aporta', $result['quality']['omitted_sources'][0]['explanation']);
        Http::assertSent(fn ($request): bool => str_contains($request['messages'][0]['content'], 'segment_id')
            && str_contains($request['messages'][0]['content'], 'el sistema identificará automáticamente'));
        Http::assertSentCount(1);
    }

    public function test_can_account_for_a_source_without_forcing_it_into_the_story(): void
    {
        $case = $this->caseRecord('Elena cayó en el pasillo.', [['id' => 'DOC', 'source' => 'Documento sin contenido disponible', 'limitations' => 'No se extrajo texto del documento adjunto.']]);
        $paragraph = $this->paragraph('Elena cayó en el pasillo.', 'CASO', $case->narrative);
        $omitted = [['source_id' => 'DOC', 'reason' => 'informacion_insuficiente', 'explanation' => 'Texto no incorporado en este simulacro.']];
        Http::preventStrayRequests();
        Http::fake(['*/chat/completions' => Http::response($this->completion([$paragraph], $omitted))]);

        $result = app(LmStudioFinalStoryGenerator::class)->generate($case, 'qwen2.5-coder-7b-instruct');

        $this->assertSame('DOC', $result['quality']['omitted_sources'][0]['source_id']);
        $this->assertSame('no_incorporada_por_modelo', $result['quality']['omitted_sources'][0]['reason']);
        $this->assertCount(2, $result['quality_warnings']);
        $this->assertSame($case->narrative, $result['story']);
        Http::assertSentCount(1);
    }

    public function test_a_source_cited_in_the_story_is_not_also_reported_as_omitted(): void
    {
        $case = $this->caseRecord('Elena cayó en el pasillo.', [['id' => 'ACTA', 'source' => 'Acta de inspección', 'facts' => 'El acta registra una medición posterior del perfil.']]);
        $event = $this->paragraph('Elena cayó en el pasillo.', 'CASO', $case->narrative);
        $inspection = $this->paragraph('El acta registra una medición posterior del perfil.', 'ACTA', 'El acta registra una medición posterior del perfil.');
        $contradictoryOmission = [['source_id' => 'ACTA', 'reason' => 'informacion_insuficiente', 'explanation' => 'La fuente no aporta información suficiente al relato.']];
        Http::preventStrayRequests();
        Http::fake(['*/chat/completions' => Http::response($this->completion([$event, $inspection], $contradictoryOmission))]);

        $result = app(LmStudioFinalStoryGenerator::class)->generate($case, 'qwen2.5-coder-7b-instruct');

        $this->assertStringContainsString($inspection['text'], $result['story']);
        $this->assertSame([], $result['quality']['omitted_sources']);
        $this->assertSame([], $result['attempts'][0]['issues']);
        Http::assertSentCount(1);
    }

    public function test_retries_a_relative_time_inversion_instead_of_assigning_witnesses_to_fixed_paragraphs(): void
    {
        $case = $this->caseRecord('Elena cayó en el pasillo.');
        $answer = new InterviewAnswer(['question_key' => 'observacion', 'value' => ['Marta observó el perfil diez minutos antes del accidente.']]);
        $answer->setRelation('question', null);
        $interview = new InvestigationInterview(['interviewee_name' => 'Marta', 'interviewee_role' => 'Técnica de mantenimiento', 'witness_type' => 'technical']);
        $interview->setRelation('answers', new Collection([$answer]));
        $investigation = new Investigation;
        $investigation->setRelation('interviews', new Collection([$interview]));
        $case->setRelation('investigation', $investigation);
        $quote = 'Marta observó el perfil diez minutos antes del accidente.';
        $wrong = $this->paragraph('Marta observó el perfil diez minutos después del accidente.', 'ENT-01', $quote);
        $correct = $this->paragraph($quote, 'ENT-01', $quote);
        $event = $this->paragraph('Elena cayó en el pasillo.', 'CASO', $case->narrative);
        Http::preventStrayRequests();
        Http::fake(['*/chat/completions' => Http::sequence()
            ->push($this->completion([$event, $wrong]))
            ->push($this->completion([$correct, $event]))]);

        $result = app(LmStudioFinalStoryGenerator::class)->generate($case, 'qwen2.5-coder-7b-instruct');

        $this->assertStringStartsWith($quote, $result['story']);
        $this->assertSame(1, $result['interviews_used']);
        $this->assertStringContainsString('invierte', implode('; ', $result['attempts'][0]['issues']));
        Http::assertSentCount(2);
    }

    public function test_a_question_cannot_be_used_as_evidence_for_an_event(): void
    {
        $case = $this->caseRecord('Elena recibió atención posterior en el centro de salud.');
        $answer = new InterviewAnswer(['question_key' => 'presencia', 'value' => ['No presenció la caída.']]);
        $answer->setRelation('question', new QuestionnaireQuestion(['pregunta' => '¿Elena cayó en el pasillo a las 10:15?']));
        $interview = new InvestigationInterview(['interviewee_name' => 'Marta', 'interviewee_role' => 'Administrativa', 'witness_type' => 'Testigo indirecto']);
        $interview->setRelation('answers', new Collection([$answer]));
        $investigation = new Investigation;
        $investigation->setRelation('interviews', new Collection([$interview]));
        $case->setRelation('investigation', $investigation);
        $paragraph = $this->paragraph('Elena cayó en el pasillo a las 10:15.', 'ENT-01', '¿Elena cayó en el pasillo a las 10:15?');
        $omitted = [['source_id' => 'CASO', 'reason' => 'sin_aporte_al_evento', 'explanation' => 'El relato solo registra una atención posterior.']];
        Http::preventStrayRequests();
        Http::fake(['*/chat/completions' => Http::response($this->completion([$paragraph], $omitted))]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('cita inexistente');

        app(LmStudioFinalStoryGenerator::class)->generate($case, 'qwen2.5-coder-7b-instruct');
    }

    public function test_interview_date_cannot_become_the_accident_date(): void
    {
        $case = $this->caseRecord('La trabajadora cayó en el pasillo.');
        $answer = new InterviewAnswer(['question_key' => 'relato', 'value' => ['La trabajadora cayó en el pasillo.']]);
        $answer->setRelation('question', null);
        $interview = new InvestigationInterview(['interviewee_name' => 'Marta', 'interviewee_role' => 'Administrativa', 'witness_type' => 'Testigo', 'interview_date' => '2026-10-22']);
        $interview->setRelation('answers', new Collection([$answer]));
        $investigation = new Investigation;
        $investigation->setRelation('interviews', new Collection([$interview]));
        $case->setRelation('investigation', $investigation);
        $paragraph = $this->paragraph('El 22 de octubre de 2026 la trabajadora cayó en el pasillo.', 'ENT-01', 'La trabajadora cayó en el pasillo.');
        $omitted = [['source_id' => 'CASO', 'reason' => 'sin_aporte_al_evento', 'explanation' => 'El relato inicial no precisa la fecha del accidente.']];
        Http::preventStrayRequests();
        Http::fake(['*/chat/completions' => Http::response($this->completion([$paragraph], $omitted))]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('agrega una fecha no registrada');

        app(LmStudioFinalStoryGenerator::class)->generate($case, 'qwen2.5-coder-7b-instruct');
    }

    public function test_registered_case_time_must_be_selected_as_a_supporting_fragment(): void
    {
        $case = $this->caseRecord('Elena cayó en el pasillo.');
        $case->event_time = '10:15';
        $paragraph = $this->paragraph('Elena cayó a las 10:15.', 'CASO', $case->narrative);
        Http::preventStrayRequests();
        Http::fake(['*/chat/completions' => Http::response($this->completion([$paragraph]))]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('agrega la hora 10:15');

        app(LmStudioFinalStoryGenerator::class)->generate($case, 'qwen2.5-coder-7b-instruct');
    }

    public function test_approximate_event_time_must_not_be_rendered_as_exact(): void
    {
        $case = $this->caseRecord('Elena cayó cerca de las 09:15.');
        $paragraph = $this->paragraph('Elena cayó a las 09:15.', 'CASO', $case->narrative);
        Http::preventStrayRequests();
        Http::fake(['*/chat/completions' => Http::response($this->completion([$paragraph]))]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('convierte la hora aproximada 09:15');

        app(LmStudioFinalStoryGenerator::class)->generate($case, 'qwen2.5-coder-7b-instruct');
    }

    public function test_moves_an_explicitly_pre_event_witness_observation_before_the_accident_and_warns_for_review(): void
    {
        $case = $this->caseRecord('Diego resbaló y cayó en el acceso.');
        $answer = new InterviewAnswer(['question_key' => 'before_event', 'value' => ['Rocío pasó por el acceso unos minutos antes y vio humedad.']]);
        $answer->setRelation('question', null);
        $interview = new InvestigationInterview(['interviewee_name' => 'Rocío', 'interviewee_role' => 'Administrativa', 'witness_type' => 'Testigo indirecto']);
        $interview->setRelation('answers', new Collection([$answer]));
        $investigation = new Investigation;
        $investigation->setRelation('interviews', new Collection([$interview]));
        $case->setRelation('investigation', $investigation);
        $event = $this->paragraph('Diego resbaló y cayó en el acceso.', 'CASO', $case->narrative);
        $witnessQuote = 'Rocío pasó por el acceso unos minutos antes y vio humedad.';
        $witness = $this->paragraph($witnessQuote, 'ENT-01', $witnessQuote);
        Http::preventStrayRequests();
        Http::fake(['*/chat/completions' => Http::response($this->completion([$event, $witness]))]);

        $result = app(LmStudioFinalStoryGenerator::class)->generate($case, 'qwen2.5-coder-7b-instruct');

        $this->assertSame($witnessQuote."\n\n".$event['text'], $result['story']);
        $this->assertTrue($result['quality']['chronology_adjusted']);
        $this->assertStringContainsString('Se reubicó una observación', $result['quality_warnings'][1]);
        Http::assertSentCount(1);
    }

    public function test_rejects_an_invented_exact_time_after_the_bounded_retry(): void
    {
        $case = $this->caseRecord('Elena cayó cerca de las 09:15.');
        $paragraph = $this->paragraph('Elena cayó a las 09:05.', 'CASO', $case->narrative);
        Http::preventStrayRequests();
        Http::fake(['*/chat/completions' => Http::response($this->completion([$paragraph]))]);

        try {
            app(LmStudioFinalStoryGenerator::class)->generate($case, 'qwen2.5-coder-7b-instruct');
            $this->fail('Debe rechazar una hora no registrada.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('09:05', $exception->getMessage());
        }

        Http::assertSentCount(2);
    }

    public function test_rejects_a_fabricated_quote_even_if_the_source_id_exists(): void
    {
        $case = $this->caseRecord('Elena cayó en el pasillo.');
        $paragraph = $this->paragraph('Elena recibió un diagnóstico de fractura.', 'CASO', 'El informe confirmó una fractura del codo.');
        Http::preventStrayRequests();
        Http::fake(['*/chat/completions' => Http::response($this->completion([$paragraph]))]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('cita inexistente');

        app(LmStudioFinalStoryGenerator::class)->generate($case, 'qwen2.5-coder-7b-instruct');
    }

    public function test_rejects_truncated_output_without_repeating_a_request_that_hit_its_output_limit(): void
    {
        $case = $this->caseRecord('Elena cayó en el pasillo.');
        Http::preventStrayRequests();
        Http::fake(['*/chat/completions' => Http::response(['choices' => [['finish_reason' => 'length', 'message' => ['content' => '{"paragraphs":[']]]])]);

        try {
            app(LmStudioFinalStoryGenerator::class)->generate($case, 'qwen2.5-coder-7b-instruct');
            $this->fail('Debe rechazar el texto cortado.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('cortó el relato', $exception->getMessage());
        }

        Http::assertSentCount(1);
    }

    public function test_rejects_a_response_that_ends_mid_sentence(): void
    {
        $case = $this->caseRecord('Elena cayó en el pasillo.');
        $paragraph = $this->paragraph('Elena cayó y después', 'CASO', $case->narrative);
        Http::preventStrayRequests();
        Http::fake(['*/chat/completions' => Http::response($this->completion([$paragraph]))]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('frase incompleta');

        app(LmStudioFinalStoryGenerator::class)->generate($case, 'qwen2.5-coder-7b-instruct');
    }

    public function test_accepts_a_registered_date_rendered_in_spanish_without_inventing_a_different_date(): void
    {
        $case = $this->caseRecord('Elena cayó en el pasillo.');
        $case->event_date = '2026-10-21';
        $paragraph = [
            'text' => 'El 21 de octubre de 2026 Elena cayó en el pasillo.',
            'source_ids' => ['CASO'],
            'supporting_quotes' => [
                ['source_id' => 'CASO', 'segment_id' => 'CASO-metadatos_del_caso-fecha_registrada'],
                ['source_id' => 'CASO', 'segment_id' => 'CASO-relato_inicial'],
            ],
        ];
        Http::preventStrayRequests();
        Http::fake(['*/chat/completions' => Http::response($this->completion([$paragraph]))]);

        $result = app(LmStudioFinalStoryGenerator::class)->generate($case, 'qwen2.5-coder-7b-instruct');

        $this->assertSame($paragraph['text'], $result['story']);
        Http::assertSentCount(1);
    }

    /** @param list<array<string, mixed>> $evidence */
    private function caseRecord(string $narrative, array $evidence = []): OccupationalCase
    {
        $case = new OccupationalCase(['folio' => 'PRUEBA-RELATO', 'narrative' => $narrative, 'details' => ['ai_evidence_sources' => $evidence]]);
        $case->setRelation('investigation', null);

        return $case;
    }

    /** @return array{text: string, source_ids: list<string>, supporting_quotes: list<array{source_id: string, quote: string}>} */
    private function paragraph(string $text, string $sourceId, string $quote): array
    {
        return ['text' => $text, 'source_ids' => [$sourceId], 'supporting_quotes' => [['source_id' => $sourceId, 'quote' => $quote]]];
    }

    /** @param list<array> $paragraphs @param list<array> $omitted @return array<string, mixed> */
    private function completion(array $paragraphs, array $omitted = []): array
    {
        return ['choices' => [['finish_reason' => 'stop', 'message' => ['content' => json_encode(['paragraphs' => $paragraphs, 'omitted_sources' => $omitted], JSON_THROW_ON_ERROR)]]]];
    }
}
