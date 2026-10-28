<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CauseFactor;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CauseFactorController extends Controller
{
    public function __invoke(Request $request): View
    {
        abort_unless($request->user()->hasPermission('settings.manage'), 403);

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'categoria' => ['nullable', 'string', 'max:255'],
        ]);

        $query = CauseFactor::query()->where('activo', true);

        if (! empty($filters['q'])) {
            $term = '%'.$filters['q'].'%';
            $query->where(function ($query) use ($term): void {
                $query->where('codigo_efectivo', 'like', $term)
                    ->orWhere('codigo', 'like', $term)
                    ->orWhere('subcodigo', 'like', $term)
                    ->orWhere('categoria', 'like', $term)
                    ->orWhere('subcategoria', 'like', $term)
                    ->orWhere('glosa', 'like', $term)
                    ->orWhere('explicacion_glosa', 'like', $term);
            });
        }

        if (! empty($filters['categoria'])) {
            $query->where('categoria', $filters['categoria']);
        }

        return view('modules.cause-factors.index', [
            'factors' => $query->orderBy('categoria')->orderBy('subcategoria')->orderBy('codigo_efectivo')->paginate(20)->withQueryString(),
            'categories' => CauseFactor::query()->where('activo', true)->distinct()->orderBy('categoria')->pluck('categoria'),
            'activeCount' => CauseFactor::query()->where('activo', true)->count(),
            'selectedCategory' => $filters['categoria'] ?? '',
            'search' => $filters['q'] ?? '',
        ]);
    }
}
