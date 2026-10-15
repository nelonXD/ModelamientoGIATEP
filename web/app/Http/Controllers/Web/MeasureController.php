<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MeasureController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): View
    {
        return $this->renderModule($request, 'measures.read', 'modules.measures.index', 'Medidas y planes de acción', 'Definición y seguimiento de medidas de control y planes preventivos.');
    }
}
