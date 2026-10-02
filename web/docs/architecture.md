# Arquitectura de GIATEP

GIATEP utiliza Laravel con una arquitectura híbrida:

- Las rutas de `routes/web.php` entregan pantallas Blade.
- Las rutas de `routes/api.php` entregan JSON bajo `/api/v1`.
- Las reglas de permisos se comparten entre ambas interfaces.
- Las pantallas se organizan por módulo, no por rol.

## Flujo web

```text
routes/web.php
    -> app/Http/Controllers/Modules o Admin
    -> resources/views/modules o admin
```

Los controladores web coordinan autorización y selección de vistas. Las vistas Blade contienen la presentación y no consultan directamente la base de datos.

## Flujo API REST

```text
routes/api.php
    -> app/Http/Controllers/Api/V1
    -> app/Http/Requests/Api/V1
    -> app/Http/Resources/Api/V1
```

Los controladores API responden JSON y utilizan tokens Sanctum. `V1` permite evolucionar el contrato sin romper clientes existentes.

## Módulos

Cada módulo tiene un controlador y una vista propios. Cuando se implemente su API, debe agregarse el recurso equivalente dentro de `Api/V1`.

```text
Casos
├── app/Http/Controllers/Modules/Cases/CaseController.php
├── resources/views/modules/cases/index.blade.php
└── app/Http/Controllers/Api/V1/Cases/CaseController.php  (futuro)
```

## Responsabilidades

- `Models`: entidades y relaciones de Eloquent.
- `Http/Controllers`: entrada HTTP y coordinación de respuestas.
- `Http/Requests`: autorización y validación de entradas.
- `Http/Resources`: formato público de las respuestas JSON.
- `Support`: reglas pequeñas y compartidas, como permisos y RUT.
- `resources/views`: diseño Blade.
- `resources/css` y `resources/js`: estilos y comportamiento frontend.
- `database`: migraciones, factories y datos de demostración.
- `tests`: contratos funcionales y unitarios.

No se debe colocar lógica de negocio importante en Blade ni duplicarla entre controladores web y API.
