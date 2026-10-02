<?php

namespace App\Http\Controllers\Modules\Cases;

use App\Http\Controllers\Modules\ModuleController;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CaseController extends ModuleController
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): View
    {
        return $this->renderModule($request, 'cases.read', 'modules.cases.index', 'Casos', 'Registro y consulta de accidentes del trabajo y enfermedades profesionales.');
    }
}
