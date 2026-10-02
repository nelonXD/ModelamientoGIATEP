<?php

namespace App\Http\Controllers\Modules\Reports;

use App\Http\Controllers\Modules\ModuleController;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends ModuleController
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): View
    {
        return $this->renderModule($request, 'reports.export', 'modules.reports.index', 'Reportes', 'Consulta y exportación de informes habilitados para el usuario.');
    }
}
