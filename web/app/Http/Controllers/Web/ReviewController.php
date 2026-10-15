<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): View
    {
        return $this->renderModule($request, 'reviews.read', 'modules.reviews.index', 'Revisiones y validaciones', 'Revisión humana de antecedentes, propuestas y medidas antes de su validación.');
    }
}
