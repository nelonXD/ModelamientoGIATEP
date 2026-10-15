<x-layouts.guest title='Solicitud enviada correctamente · GIATEP'>
<div class='card p-8 text-center sm:p-12 shadow-sm'>
    <div class='mx-auto flex size-16 items-center justify-center rounded-2xl bg-emerald-100 text-3xl font-black text-emerald-800 shadow-xs'>
        ✓
    </div>
    <p class='eyebrow mt-6'>Recepción Oficial</p>
    <h2 class='mt-2 text-2xl font-black text-slate-950 sm:text-3xl'>Solicitud Recibida Correctamente</h2>
    <p class='mx-auto mt-4 max-w-md text-xs leading-relaxed text-slate-600'>
        Su requerimiento de acceso ha sido registrado en el sistema. El Administrador de la Dirección Comunal de Salud revisará sus antecedentes y activará su cuenta tras la validación correspondiente.
    </p>
    <div class='mt-8 pt-6 border-t border-slate-100'>
        <a href='{{ route('login') }}' class='btn-primary inline-flex px-6'>
            Volver al Inicio de Sesión
        </a>
    </div>
</div>
</x-layouts.guest>
