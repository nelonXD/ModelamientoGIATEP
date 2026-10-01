<x-layouts.guest title='Ingresar · GIATEP'>
<div class='mb-8 lg:hidden'><span class='text-3xl font-black text-blue-900'>GIATEP</span><p class='mt-2 text-sm text-slate-600'>Gestión e investigación de accidentes del trabajo y enfermedades profesionales</p></div>
<div class='card p-6 sm:p-10'>
    <p class='eyebrow'>Bienvenido</p><h2 class='mt-2 text-3xl font-bold'>Iniciar sesión</h2><p class='mt-2 text-slate-600'>Ingresa con tu RUT y contraseña institucional.</p>
    <form method='POST' action='{{ route('login.store') }}' class='mt-8 grid gap-5' data-submit-form>@csrf
        <div><label for='rut' class='label'>RUT</label><input id='rut' name='rut' value='{{ old('rut') }}' autocomplete='username' class='input' placeholder='12.345.678-5' required>@error('rut')<p class='error'>{{ $message }}</p>@enderror</div>
        <div><label for='password' class='label'>Contraseña</label><div class='relative'><input id='password' name='password' type='password' autocomplete='current-password' class='input pr-24' required><button type='button' class='password-toggle' data-password-toggle='password'>Mostrar</button></div>@error('password')<p class='error'>{{ $message }}</p>@enderror</div>
        <button class='btn-primary w-full' type='submit' data-submit-button>Ingresar</button>
    </form>
    <div class='mt-7 border-t border-slate-200 pt-6 text-center'><p class='text-sm text-slate-600'>¿Eres Delegado de Seguridad o integrante del CPHS?</p><a href='{{ route('register') }}' class='mt-2 inline-block font-bold text-blue-800 hover:underline'>Solicitar acceso</a></div>
</div>
</x-layouts.guest>
