DB::beginTransaction();
try {
    $case = App\Models\OccupationalCase::where('Folio', 'GIATEP-2026-001')->firstOrFail();
    $inv = $case->investigation()->firstOrFail();
    $rut = '88888888-8';
    DB::table('testigo')->updateOrInsert(['Rut_Testigo' => $rut], ['Nombre_Testigo' => 'Testigo Prueba Reversible', 'Establecimiento' => 'CESFAM Prueba', 'Cargo' => 'TENS']);
    DB::table('testigo_caso')->updateOrInsert(['Id_Caso' => $case->getKey(), 'Rut_Testigo' => $rut], ['Fecha_Registro' => now()]);
    $tc = DB::table('testigo_caso')->where('Id_Caso', $case->getKey())->where('Rut_Testigo', $rut)->value('Id_Testigo_Caso');
    $form = DB::table('formulario_declaracion')->insertGetId(['Nombre' => 'Formulario de prueba reversible', 'Version' => '1', 'Estado' => 'Activo']);
    $type = DB::table('tipo_entrevistado')->insertGetId(['Nombre_Tipo' => 'Tipo prueba reversible']);
    $decl = DB::table('declaracion')->insertGetId(['Id_Formulario' => $form, 'Codigo_Investigacion' => $inv->getKey(), 'Id_Testigo_Caso' => $tc, 'Id_Tipo_Entrevistado' => $type, 'Rut_Usuario' => '11111111-1', 'Fecha_Entrevista' => now()->toDateString(), 'Estado' => 'Completada']);
    $q = DB::table('pregunta_declaracion')->insertGetId(['Id_Formulario' => $form, 'Variable' => 'prueba_reversible', 'Texto_Pregunta' => 'Pregunta de prueba', 'Tipo_Respuesta' => 'texto', 'Orden' => 1, 'Obligatorio_Si_Aplica' => 0]);
    DB::table('respuesta_declaracion')->insert(['Id_Declaracion' => $decl, 'Id_Pregunta' => $q, 'Estado_Respuesta' => 'Respondida', 'Texto_Respuesta' => 'Respuesta reversible']);
    dump(['declaracion' => DB::table('declaracion')->where('Id_Declaracion', $decl)->exists(), 'respuesta' => DB::table('respuesta_declaracion')->where('Id_Declaracion', $decl)->value('Texto_Respuesta')]);
} finally { DB::rollBack(); }
