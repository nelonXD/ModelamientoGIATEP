<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Modules\ModuleController;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends ModuleController
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): View
    {
        return $this->renderModule($request, 'settings.manage', 'admin.settings.index', 'Parámetros institucionales', 'Configuración general y valores institucionales de GIATEP.');
    }
}
