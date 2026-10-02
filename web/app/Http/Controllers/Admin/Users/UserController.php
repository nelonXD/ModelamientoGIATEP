<?php

namespace App\Http\Controllers\Admin\Users;

use App\Http\Controllers\Modules\ModuleController;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends ModuleController
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): View
    {
        return $this->renderModule($request, 'users.manage', 'admin.users.index', 'Usuarios', 'Administración de cuentas, estados, roles y establecimientos asignados.');
    }
}
