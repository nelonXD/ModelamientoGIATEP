<?php

namespace App\Support;

class RolePermissionMap
{
    /** @var array<string, list<string>> */
    private const PERMISSIONS = [
        'delegado' => ['dashboard.view', 'cases.read', 'investigations.read', 'measures.read', 'observations.read', 'profile.read'],
        'cphs' => ['dashboard.view', 'cases.read', 'investigations.read', 'measures.read', 'observations.read', 'profile.read'],
        'prevencion' => ['dashboard.view', 'cases.read', 'investigations.read', 'reviews.read', 'measures.read', 'statistics.read', 'reports.export', 'profile.read'],
        'jefatura' => ['dashboard.view', 'investigations.read', 'observations.read', 'measures.read', 'profile.read'],
        'alta-direccion' => ['dashboard.view', 'investigations.read', 'observations.read', 'measures.read', 'statistics.read', 'reports.export', 'profile.read'],
        'administrador' => ['admin.dashboard', 'registration-requests.review', 'users.manage', 'roles.manage', 'establishments.manage', 'settings.manage', 'profile.read'],
    ];

    /** @return list<string> */
    public static function forRoles(iterable $roleSlugs): array
    {
        $permissions = [];

        foreach ($roleSlugs as $slug) {
            $permissions = [...$permissions, ...(self::PERMISSIONS[$slug] ?? [])];
        }

        return array_values(array_unique($permissions));
    }
}
