@props(['title', 'description'])

<div class='card p-8 text-center sm:p-12'>
    <div class='mx-auto flex size-16 items-center justify-center rounded-2xl bg-blue-100 text-3xl' aria-hidden='true'>◇</div>
    <p class='eyebrow mt-6'>Base de diseño</p>
    <h1 class='mt-2 text-3xl font-bold'>{{ $title }}</h1>
    <p class='mx-auto mt-3 max-w-2xl text-slate-600'>{{ $description }}</p>
    <p class='mx-auto mt-3 max-w-2xl text-sm text-slate-500'>Esta pantalla está preparada para que el equipo implemente el diseño y conecte posteriormente los datos de la API REST.</p>
    <a href='{{ route('dashboard') }}' class='btn-primary mt-7 inline-flex'>Volver al inicio</a>
</div>
