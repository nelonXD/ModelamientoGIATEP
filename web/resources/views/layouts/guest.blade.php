<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='utf-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <title>{{ $title ?? 'GIATEP · Dirección Comunal de Salud Los Ángeles' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class='min-h-screen bg-slate-50 text-slate-900 antialiased selection:bg-sky-500 selection:text-white'>
<main class='grid min-h-screen lg:grid-cols-[minmax(0,1fr)_minmax(32rem,0.9fr)]'>
    <!-- Panel Institucional -->
    <section class='brand-panel relative hidden overflow-hidden p-12 text-white lg:flex lg:flex-col lg:justify-between'>
        <div class='absolute -right-24 -top-24 size-96 rounded-full bg-sky-400/10 blur-3xl pointer-events-none'></div>
        <div class='absolute -left-20 -bottom-20 size-80 rounded-full bg-blue-600/10 blur-3xl pointer-events-none'></div>

        <div class='relative'>
            <a href='{{ route('login') }}' class='flex items-center gap-3 text-2xl font-black tracking-tight text-white'>
                <span class='flex size-9 items-center justify-center rounded-xl bg-sky-500 text-base font-black text-slate-950 shadow-md'>G</span>
                <span>GIATEP</span>
            </a>
            <p class='mt-2 text-xs font-bold uppercase tracking-widest text-sky-300'>
                Dirección Comunal de Salud · Los Ángeles
            </p>
        </div>

        <div class='relative max-w-xl my-auto py-12'>
            <div class='inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-bold text-sky-200 ring-1 ring-inset ring-white/15'>
                <span class='size-2 rounded-full bg-sky-400'></span>
                Salud Ocupacional · Red APS
            </div>
            <h1 class='mt-6 text-3xl font-black leading-tight sm:text-4xl text-white'>
                Gestión e Investigación de Accidentes del Trabajo y Enfermedades Profesionales
            </h1>
            <p class='mt-4 text-base leading-relaxed text-sky-100'>
                Plataforma oficial para la emisión de denuncias DIAT / DIEP, investigación técnica por Árbol de Causas y trazabilidad de medidas preventivas en la red comunal de atención primaria.
            </p>
            <div class='mt-8 grid grid-cols-2 gap-4 border-t border-white/10 pt-6 text-xs text-sky-200'>
                <div>
                    <strong class='block font-bold text-white'>Normativa Oficial</strong>
                    <span class='text-slate-300'>Ley N° 16.744 y Ley N° 21.180</span>
                </div>
                <div>
                    <strong class='block font-bold text-white'>Organismo Administrador</strong>
                    <span class='text-slate-300'>Convenio ACHS</span>
                </div>
            </div>
        </div>

        <div class='relative border-t border-white/10 pt-4 text-xs text-sky-300/80 flex items-center justify-between'>
            <span>Sistema Seguro con Autenticación RBAC</span>
            <span>v2.0 Institucional</span>
        </div>
    </section>

    <!-- Contenedor del Formulario -->
    <section class='flex items-center justify-center p-6 sm:p-12 lg:p-16'>
        <div class='w-full max-w-lg'>
            {{ $slot }}
        </div>
    </section>
</main>
</body>
</html>
