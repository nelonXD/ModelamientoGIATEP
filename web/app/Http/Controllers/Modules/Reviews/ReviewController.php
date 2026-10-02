<?php

namespace App\Http\Controllers\Modules\Reviews;

use App\Http\Controllers\Modules\ModuleController;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewController extends ModuleController
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): View
    {
        return $this->renderModule($request, 'reviews.read', 'modules.reviews.index', 'Revisiones y validaciones', 'Revisión humana de antecedentes, propuestas y medidas antes de su validación.');
    }
}
