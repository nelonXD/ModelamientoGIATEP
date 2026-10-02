# GIATEP

Repositorio de la plataforma de Gestión e Investigación de Accidentes del Trabajo y Enfermedades Profesionales.

La aplicación Laravel se encuentra en [`web/`](web/README.md).

## Estructura del repositorio

```text
Pruba-login/
├── .github/workflows/  Validación y plantillas de despliegue
├── docker/
│   ├── nginx/           Configuración futura de Nginx
│   └── php/             Imagen futura de PHP-FPM
├── web/        Aplicación Laravel
│   ├── app/        Código PHP
│   ├── resources/  Vistas, CSS y JavaScript
│   ├── routes/     Rutas web y API
│   ├── database/   Migraciones y datos de demostración
│   ├── tests/      Pruebas automatizadas
│   └── docs/       Arquitectura y guía del equipo
├── db_data/    Datos locales ignorados por Git
├── .env.example Variables para Docker Compose
├── .gitattributes Reglas de finales de línea y binarios
├── .gitignore  Archivos excluidos del repositorio
└── docker-compose.yml Orquestación futura de contenedores
```

Los workflows de desarrollo, preproducción y producción son plantillas manuales. No realizan despliegues hasta que el equipo defina servidores, secretos y aprobaciones en GitHub.

## Inicio rápido

```powershell
Set-Location web
composer install
Copy-Item .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install
npm run build
composer run dev
```

Consulta la [documentación de la aplicación](web/README.md) antes de comenzar.
