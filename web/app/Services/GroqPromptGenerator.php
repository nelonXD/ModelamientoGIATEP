<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class GroqPromptGenerator
{
    public function generate(string $basePrompt): string
    {
        $key = (string) config('services.groq.key', '');
        if ($key === '') {
            throw new RuntimeException('No está configurada la clave de Groq (GROQ_API_KEY).');
        }

        try {
            $response = Http::withToken($key)->acceptJson()->timeout((int) config('services.groq.timeout', 300))
                ->post(rtrim((string) config('services.groq.url'), '/').'/chat/completions', [
                    'model' => (string) config('services.groq.model', 'openai/gpt-oss-120b'),
                    'temperature' => 0.2,
                    'messages' => [
                        ['role' => 'system', 'content' => 'Redacta y organiza prompts para análisis de investigaciones de accidentes laborales. Devuelve únicamente el prompt final listo para copiar. Conserva íntegramente el esquema JSON exigido, las reglas de evidencia, las limitaciones, y el contexto del expediente del texto fuente. No ejecutes el análisis ni respondas el JSON solicitado por el prompt. No elimines ni debilites ninguna salvaguarda.'],
                        ['role' => 'user', 'content' => "Prepara una versión clara y completa de este prompt. Conserva el contenido del expediente y todas sus instrucciones metodológicas.\n\nPROMPT BASE:\n{$basePrompt}"],
                    ],
                ));
        } catch (\Throwable $exception) {
            throw new RuntimeException('No se pudo completar la generación del prompt con Groq. Comprueba la conexión y la configuración de la API.', previous: $exception);
        }

        if (! $response->successful()) {
            throw new RuntimeException('Groq rechazó la generación del prompt (HTTP '.$response->status().'). Comprueba el modelo y la clave API.');
        }

        $prompt = trim((string) $response->json('choices.0.message.content', ''));
        if ($prompt === '' || mb_strlen($prompt) < 100) {
            throw new RuntimeException('Groq no devolvió un prompt completo.');
        }

        return $prompt;
    }
}
