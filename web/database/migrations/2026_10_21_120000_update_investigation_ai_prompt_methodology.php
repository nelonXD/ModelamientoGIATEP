<?php

use App\Support\OriginalGiatepSchema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const PROMPT = 'Aplica la metodología del Árbol de Causas con trazabilidad por fuente. Distingue eventos puntuales, condiciones permanentes, declaraciones, hechos corroborados y datos pendientes; declarado no significa verificado, y una cita no equivale por sí sola a corroboración. Produce por separado hechos cronológicos, vacíos de información y árbol retrospectivo desde la lesión. No incluyas falta de recuerdo o de inspección como hechos: regístralas como verificaciones pendientes. El árbol usa solo hechos revisados, vincula antecedente→consecuencia y clasifica cadena, conjunción (antecedentes independientes conjuntamente necesarios) o disyunción; valida cada vínculo con la prueba contrafactual. No confundas cronología con causalidad, no fuerces conectividad ni inventes hechos, evidencia, relaciones, diagnósticos, normas infringidas ni responsabilidades. Usa exclusivamente códigos de la matriz local entregada y solo si están respaldados por hechos. Propón medidas concretas y verificables ligadas a nodos causales, priorizando controles de mayor jerarquía. Explicita fuentes, contradicciones y vacíos; la salida es una propuesta editable que requiere revisión humana.';

    public function up(): void
    {
        if (OriginalGiatepSchema::isActive()) {
            return;
        }

        if (Schema::hasTable('ai_prompts')) {
            DB::table('ai_prompts')->where('nombre', 'analisis-investigacion')->update([
                'version' => 3,
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
                'version' => 2,
                'prompt_base' => 'Aplica la metodología local del Árbol de Causas: extrae hechos neutrales y cronológicos, registra la fuente, separa eventos puntuales de condiciones permanentes, y distingue declaraciones de constataciones corroboradas. No conviertas falta de recuerdo o de inspección en hechos; registra esos vacíos aparte. Construye retrospectivamente un grafo causal antecedente→consecuencia con cadena, conjunción o disyunción; separa causalidad respaldada de vínculos aparentes, evita ciclos y nodos aislados, e identifica el hecho final. Selecciona exclusivamente códigos respaldados de la matriz local completa que recibe el modelo. Propón medidas concretas, verificables y conectadas a los nodos que abordan; prioriza eliminación, sustitución y controles colectivos/ingeniería, e informa cobertura, estabilidad, cargo responsable y plazo solo si hay base para ello. No inventes hechos, diagnósticos, incumplimientos, personas, fechas ni causas. Devuelve únicamente el JSON estructurado solicitado; es una propuesta editable para revisión humana.',
                'updated_at' => now(),
            ]);
        }
    }
};
