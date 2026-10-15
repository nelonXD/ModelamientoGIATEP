<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ObservationController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): View
    {
        return $this->renderModule($request, 'observations.read', 'modules.observations.index', 'Observaciones', 'Registro de observaciones y seguimiento asociado a investigaciones y medidas.');
    }
}
