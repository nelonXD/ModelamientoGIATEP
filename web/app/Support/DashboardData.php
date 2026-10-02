<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Collection;

class DashboardData
{
    /** @return array{label: string, message: string} */
    public function profileFor(User $user): array
    {
        $roleSlugs = $user->roles->pluck('slug');

        return match (true) {
            $roleSlugs->contains('prevencion') => [
                'label' => 'Prevencionista',
                'message' => 'Gestiona casos, revisiones, medidas preventivas e información consolidada dentro de tu alcance.',
            ],
            $roleSlugs->contains('alta-direccion') => [
                'label' => 'Alta Dirección',
                'message' => 'Consulta investigaciones, seguimiento preventivo e indicadores para apoyar la toma de decisiones.',
            ],
            $roleSlugs->contains('jefatura') => [
                'label' => 'Jefatura Directa',
                'message' => 'Consulta investigaciones y participa en el seguimiento de observaciones y medidas de control.',
            ],
            $roleSlugs->contains('cphs') => [
                'label' => 'Comité Paritario',
                'message' => 'Registra casos, participa en investigaciones y realiza seguimiento de medidas preventivas dentro de tu alcance.',
            ],
            $roleSlugs->contains('delegado') => [
                'label' => 'Delegado de Seguridad',
                'message' => 'Registra casos, participa en investigaciones y realiza seguimiento de medidas preventivas dentro de tu alcance.',
            ],
            $roleSlugs->contains('administrador') => [
                'label' => 'Administrador',
                'message' => 'Administra solicitudes de acceso, usuarios, roles, establecimientos y parámetros institucionales.',
            ],
            default => [
                'label' => 'Panel de inicio',
                'message' => 'Accede a las funciones habilitadas para tus roles y establecimientos autorizados.',
            ],
        };
    }

    /** @return Collection<int, array{permission: string, title: string, description: string, module: string, route: string}> */
    public function actionsFor(User $user): Collection
    {
        return collect([
            ['permission' => 'cases.read', 'title' => 'Casos', 'description' => 'Registra y consulta casos dentro de tus establecimientos autorizados.', 'module' => 'casos', 'route' => 'modules.cases.index'],
            ['permission' => 'investigations.read', 'title' => 'Investigaciones', 'description' => 'Consulta investigaciones y sus antecedentes disponibles.', 'module' => 'investigaciones', 'route' => 'modules.investigations.index'],
            ['permission' => 'reviews.read', 'title' => 'Revisiones y validaciones', 'description' => 'Revisa antecedentes, medidas y planes antes de su validación.', 'module' => 'revisiones', 'route' => 'modules.reviews.index'],
            ['permission' => 'observations.read', 'title' => 'Observaciones', 'description' => 'Registra y consulta observaciones asociadas al seguimiento.', 'module' => 'observaciones', 'route' => 'modules.observations.index'],
            ['permission' => 'measures.read', 'title' => 'Medidas y planes de acción', 'description' => 'Consulta las medidas de control y sus planes de acción.', 'module' => 'medidas', 'route' => 'modules.measures.index'],
            ['permission' => 'statistics.read', 'title' => 'Dashboards y estadísticas', 'description' => 'Accede a indicadores consolidados según tu alcance autorizado.', 'module' => 'estadisticas', 'route' => 'modules.statistics.index'],
            ['permission' => 'reports.export', 'title' => 'Reportes', 'description' => 'Consulta y exporta reportes habilitados para tu rol.', 'module' => 'reportes', 'route' => 'modules.reports.index'],
        ])->filter(fn (array $action): bool => $user->hasPermission($action['permission']))->values();
    }
}
