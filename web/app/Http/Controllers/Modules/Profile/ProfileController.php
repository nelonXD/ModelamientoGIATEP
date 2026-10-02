<?php

namespace App\Http\Controllers\Modules\Profile;

use App\Http\Controllers\Modules\ModuleController;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends ModuleController
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): View
    {
        return $this->renderModule($request, 'profile.read', 'modules.profile.index', 'Mi perfil', 'Consulta de datos personales, roles y establecimientos autorizados.');
    }
}
