<?php

use App\Support\OriginalGiatepSchema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const PROMPT = 'Aplica la metodología local del Árbol de Causas: extrae hechos neutrales, cronológicos y respaldados con fragmentos literales; distingue hechos puntuales de condiciones permanentes y declara si cada dato está verificado, declarado o pendiente de verificación. Construye retrospectivamente un grafo causal antecedente→consecuencia con cadena, conjunción o disyunción; separa causalidad respaldada de vínculos aparentes, evita ciclos y nodos aislados, e identifica el hecho final. Selecciona exclusivamente códigos respaldados de la matriz local completa que recibe el modelo. Propón medidas concretas, verificables y conectadas a los nodos que abordan; prioriza eliminación, sustitución y controles colectivos/ingeniería, e informa cobertura, estabilidad, cargo responsable y plazo solo si hay base para ello. No inventes hechos, diagnósticos, incumplimientos, personas, fechas ni causas. Devuelve únicamente el JSON estructurado solicitado; es una propuesta editable para revisión humana.';

    public function up(): void
    {
        if (OriginalGiatepSchema::isActive()) {
            return;
        }

        if (Schema::hasTable('ai_prompts')) {
            DB::table('ai_prompts')->where('nombre', 'analisis-investigacion')->update([
                'version' => 2,
                'modelo' => 'qwen2.5-7b-instruct',
                'prompt_base' => self::PROMPT,
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        if (OriginalGiatepSchema::isActive()) {
            return;
        }

        if (Schema::hasTable('ai_prompts')) {
            DB::table('ai_prompts')->where('nombre', 'analisis-investigacion')->update([
                'version' => 1,
                'updated_at' => now(),
            ]);
        }
    }
};
