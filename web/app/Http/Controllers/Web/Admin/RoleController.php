<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoleController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): View
    {
        return $this->renderModule($request, 'roles.manage', 'admin.roles.index', 'Roles y permisos', 'Configuración de funciones del sistema y sus permisos asociados.');
    }
}
