<?php

namespace App\Http\Controllers\Modules\Statistics;

use App\Http\Controllers\Modules\ModuleController;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StatisticController extends ModuleController
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): View
    {
        return $this->renderModule($request, 'statistics.read', 'modules.statistics.index', 'Dashboards y estadísticas', 'Visualización de indicadores consolidados dentro del alcance autorizado.');
    }
}
