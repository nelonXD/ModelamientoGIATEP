<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): View
    {
        return $this->renderModule($request, 'profile.read', 'modules.profile.index', 'Mi perfil', 'Consulta de datos personales, roles y establecimientos autorizados.');
    }
}
