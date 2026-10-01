<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='utf-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <title>{{ $title ?? 'GIATEP' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class='min-h-screen bg-slate-50 text-slate-900 antialiased'>
<main class='grid min-h-screen lg:grid-cols-[minmax(0,1fr)_minmax(32rem,0.8fr)]'>
    <section class='brand-panel hidden p-12 text-white lg:flex lg:flex-col lg:justify-between'>
        <a href='{{ route('login') }}' class='text-3xl font-black'>GIATEP</a>
        <div class='max-w-xl'><p class='mb-4 text-sm font-bold uppercase tracking-[0.2em] text-sky-200'>Salud ocupacional</p><h1 class='text-4xl font-bold leading-tight'>Gestión e investigación de accidentes del trabajo y enfermedades profesionales</h1><p class='mt-6 text-lg text-sky-100'>Dirección Comunal de Salud · Municipalidad de Los Ángeles</p></div>
        <p class='text-sm text-sky-200'>Acceso seguro para personal autorizado</p>
    </section>
    <section class='flex items-center justify-center p-5 sm:p-10'><div class='w-full max-w-xl'>{{ $slot }}</div></section>
</main>
</body>
</html>
