<?php

namespace App\Http\Controllers\Admin\Roles;

use App\Http\Controllers\Modules\ModuleController;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoleController extends ModuleController
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): View
    {
        return $this->renderModule($request, 'roles.manage', 'admin.roles.index', 'Roles y permisos', 'Configuración de funciones del sistema y sus permisos asociados.');
    }
}
