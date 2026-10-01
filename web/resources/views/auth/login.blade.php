<x-layouts.guest title='Ingresar · GIATEP'>
<div class='mb-8 lg:hidden'><span class='text-3xl font-black text-blue-900'>GIATEP</span><p class='mt-2 text-sm text-slate-600'>Gestión e investigación de accidentes del trabajo y enfermedades profesionales</p></div>
<div class='card p-6 sm:p-10'>
    <p class='eyebrow'>Bienvenido</p><h2 class='mt-2 text-3xl font-bold'>Iniciar sesión</h2><p class='mt-2 text-slate-600'>Primero ingresa tu RUT para continuar.</p>
    <form method='POST' action='{{ route('login.store') }}' class='mt-8 grid gap-5' data-submit-form data-login-form data-check-rut-url='{{ route('login.check-rut') }}'>@csrf
        <div data-login-rut-step><label for='rut' class='label'>RUT</label><input id='rut' name='rut' value='{{ old('rut') }}' autocomplete='username' class='input' placeholder='12.345.678-5' required data-login-rut aria-describedby='rut-client-error'>@error('rut')<p class='error'>{{ $message }}</p>@enderror<p id='rut-client-error' class='error hidden' data-rut-error>Ingresa un RUT válido.</p></div>
        <button class='btn-primary w-full' type='button' data-login-continue>Continuar</button>
        <div class='hidden' data-login-password-step>
            <div class='mb-5 flex items-center justify-between gap-3 rounded-xl bg-sky-50 px-4 py-3'><div><p class='text-xs font-bold uppercase tracking-wide text-sky-800'>Usuario</p><p class='font-bold text-slate-950' data-login-rut-summary></p></div><button type='button' class='text-sm font-bold text-blue-800 hover:underline' data-login-change-rut>Cambiar</button></div>
            <label for='password' class='label'>Contraseña</label><div class='relative'><input id='password' name='password' type='password' autocomplete='current-password' class='input pr-24' required disabled><button type='button' class='password-toggle' data-password-toggle='password'>Mostrar</button></div>@error('password')<p class='error'>{{ $message }}</p>@enderror
            <button class='btn-primary mt-5 w-full' type='submit' data-submit-button>Ingresar</button>
        </div>
        <noscript><p class='error'>Debes habilitar JavaScript para continuar con el inicio de sesión.</p></noscript>
    </form>
    <div class='mt-7 border-t border-slate-200 pt-6 text-center'><p class='text-sm text-slate-600'>Solo Comité Paritario y Delegado de Seguridad requieren aprobación previa.</p><a href='{{ route('register') }}' class='mt-2 inline-block font-bold text-blue-800 hover:underline'>Solicitar acceso</a><p class='mt-3 text-xs text-slate-500'>Prevencionistas, Jefaturas y Alta Dirección serán validados mediante el registro institucional.</p></div>
</div>
@if(app()->environment(['local', 'testing']))
<aside class='mt-5 rounded-2xl border border-sky-200 bg-sky-50 p-5' aria-labelledby='demo-credentials-title'>
    <p class='eyebrow'>Entorno de demostración</p>
    <h2 id='demo-credentials-title' class='mt-2 text-lg font-bold text-slate-950'>Credenciales de prueba</h2>
    <p class='mt-1 text-sm text-slate-600'>Todas las cuentas usan la contraseña <code class='rounded bg-white px-1.5 py-0.5 font-bold text-blue-900'>GiatepDemo2026!</code></p>
    <dl class='mt-4 grid gap-3 text-sm sm:grid-cols-2'>
        <div><dt>Comité Paritario</dt><dd class='font-mono'>44.444.444-4</dd></div>
        <div><dt>Delegado de Seguridad</dt><dd class='font-mono'>55.555.555-5</dd></div>
        <div><dt>Prevencionista</dt><dd class='font-mono'>11.111.111-1</dd></div>
        <div><dt>Jefatura</dt><dd class='font-mono'>22.222.222-2</dd></div>
        <div><dt>Alta Dirección</dt><dd class='font-mono'>33.333.333-3</dd></div>
    </dl>
</aside>
@endif
</x-layouts.guest>
