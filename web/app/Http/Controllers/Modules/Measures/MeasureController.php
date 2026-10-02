<?php

namespace App\Http\Controllers\Modules\Measures;

use App\Http\Controllers\Modules\ModuleController;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MeasureController extends ModuleController
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): View
    {
        return $this->renderModule($request, 'measures.read', 'modules.measures.index', 'Medidas y planes de acción', 'Definición y seguimiento de medidas de control y planes preventivos.');
    }
}
