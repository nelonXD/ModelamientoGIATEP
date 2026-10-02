<?php

namespace App\Http\Controllers\Modules\Observations;

use App\Http\Controllers\Modules\ModuleController;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ObservationController extends ModuleController
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): View
    {
        return $this->renderModule($request, 'observations.read', 'modules.observations.index', 'Observaciones', 'Registro de observaciones y seguimiento asociado a investigaciones y medidas.');
    }
}
