# Guía de trabajo del equipo

El proyecto se divide por funcionalidad. Los roles solo determinan qué módulos puede utilizar cada usuario.

| Módulo | Controlador web | Vista |
| --- | --- | --- |
| Casos | `Modules/Cases` | `views/modules/cases` |
| Investigaciones | `Modules/Investigations` | `views/modules/investigations` |
| Revisiones | `Modules/Reviews` | `views/modules/reviews` |
| Medidas | `Modules/Measures` | `views/modules/measures` |
| Observaciones | `Modules/Observations` | `views/modules/observations` |
| Estadísticas | `Modules/Statistics` | `views/modules/statistics` |
| Reportes | `Modules/Reports` | `views/modules/reports` |
| Perfil | `Modules/Profile` | `views/modules/profile` |
| Usuarios | `Admin/Users` | `views/admin/users` |
| Roles | `Admin/Roles` | `views/admin/roles` |
| Establecimientos | `Admin/Establishments` | `views/admin/establishments` |
| Parámetros | `Admin/Settings` | `views/admin/settings` |

## Forma de trabajo

1. Cada integrante toma un módulo y limita sus cambios a sus carpetas siempre que sea posible.
2. Los componentes visuales reutilizables se agregan a `resources/views/components`.
3. Las pantallas Blade no realizan consultas de base de datos.
4. Los endpoints nuevos se agregan en `Api/V1` y devuelven JSON.
5. Las validaciones de entrada se agregan como Form Requests.
6. Las respuestas API se publican mediante Resources.
7. Toda ruta debe comprobar permisos en el servidor.
8. Cada cambio funcional debe incluir o actualizar pruebas.

Antes de integrar trabajo, ejecutar:

```powershell
vendor\bin\pint --dirty --format agent
php artisan test --compact
```
