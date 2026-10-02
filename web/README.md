# GIATEP

Sistema de Gestión e Investigación de Accidentes del Trabajo y Enfermedades Profesionales para la red comunal de salud.

Esta etapa del proyecto contiene las pantallas base, autenticación, solicitud de acceso, navegación por permisos y estructura modular preparada para trabajo colaborativo.

## Tecnologías

- PHP 8.3
- Laravel 13
- Laravel Sanctum
- Blade
- Tailwind CSS y Vite
- PHPUnit

## Instalación

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install
npm run build
php artisan serve
```

Para desarrollo con recarga automática:

```powershell
composer run dev
```

## Organización

```text
app/Http/Controllers/
├── Admin/          Pantallas administrativas
├── Api/V1/         API REST JSON versionada
├── Auth/           Autenticación web
└── Modules/        Pantallas de los módulos operativos

resources/views/
├── admin/          Vistas administrativas
├── auth/           Login y solicitud de acceso
├── components/     Componentes visuales compartidos
├── layouts/        Estructuras generales
└── modules/        Vistas de cada módulo
```

Los controladores web devuelven HTML mediante Blade. Los controladores de `Api/V1` devuelven JSON. La lógica compartida no debe duplicarse entre ambas interfaces.

## Documentación del equipo

- [Arquitectura](docs/architecture.md)
- [Guía de trabajo y reparto](docs/team-work-guide.md)

## Verificación

```powershell
vendor\bin\pint --dirty --format agent
php artisan test --compact
```

Los datos creados por `GiatepDemoSeeder` son exclusivamente de demostración.
