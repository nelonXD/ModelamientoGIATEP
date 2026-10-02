<?php

namespace App\Http\Controllers\Modules\Investigations;

use App\Http\Controllers\Modules\ModuleController;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvestigationController extends ModuleController
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): View
    {
        return $this->renderModule($request, 'investigations.read', 'modules.investigations.index', 'Investigaciones', 'Recopilación de antecedentes, evidencias, entrevistas y árboles de causas.');
    }
}
