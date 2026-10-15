@props(['title', 'description'])

<div class='card p-8 text-center sm:p-12 max-w-3xl mx-auto'>
    <div class='mx-auto flex size-14 items-center justify-center rounded-2xl bg-sky-50 text-sky-800 text-2xl border border-sky-100' aria-hidden='true'>◇</div>
    <p class='text-xs font-bold uppercase tracking-wider text-sky-700 mt-5'>Módulo Oficial · Sistema GIATEP</p>
    <h1 class='mt-1 text-2xl sm:text-3xl font-black text-slate-950'>{{ $title }}</h1>
    <p class='mx-auto mt-2 max-w-xl text-sm text-slate-600 leading-relaxed'>{{ $description }}</p>
    <div class='mt-5 rounded-xl border border-slate-200 bg-slate-50 p-4 text-xs text-slate-600 max-w-lg mx-auto'>
        Módulo institucional habilitado para el perfil autorizado de la Red de Salud Municipal bajo la Ley N° 16.744.
    </div>
    <div class='mt-6'>
        <a href='{{ route('dashboard') }}' class='btn-secondary text-xs py-2 px-5 inline-flex'>← Volver al inicio</a>
    </div>
</div>
