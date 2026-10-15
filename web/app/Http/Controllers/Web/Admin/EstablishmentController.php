<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EstablishmentController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): View
    {
        return $this->renderModule($request, 'establishments.manage', 'admin.establishments.index', 'Establecimientos', 'Administración del catálogo de centros de trabajo de la red.');
    }
}
