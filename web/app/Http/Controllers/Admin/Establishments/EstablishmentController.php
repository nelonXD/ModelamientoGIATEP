<?php

namespace App\Http\Controllers\Admin\Establishments;

use App\Http\Controllers\Modules\ModuleController;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EstablishmentController extends ModuleController
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): View
    {
        return $this->renderModule($request, 'establishments.manage', 'admin.establishments.index', 'Establecimientos', 'Administración del catálogo de centros de trabajo de la red.');
    }
}
