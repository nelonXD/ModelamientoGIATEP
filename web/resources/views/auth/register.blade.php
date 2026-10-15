<x-layouts.guest title='Solicitar acceso institucional · GIATEP'>
<div class='mb-6'>
    <a href='{{ route('login') }}' class='inline-flex items-center gap-1.5 text-xs font-bold text-[#0b2545] transition hover:text-[#133e7c] hover:underline'>
        <span>←</span> Volver al inicio de sesión
    </a>
</div>

<div class='card p-6 sm:p-10 shadow-sm'>
    <div class='border-b border-slate-100 pb-5'>
        <p class='eyebrow'>Solicitud de Acceso</p>
        <h2 class='mt-1 text-2xl font-black text-slate-950 sm:text-3xl'>Registro de Funcionario</h2>
        <p class='mt-2 text-xs leading-relaxed text-slate-600'>
            Habilitado exclusivamente para integrantes de <strong>Comités Paritarios (CPHS)</strong> y <strong>Delegados de Seguridad</strong> de la red APS. Toda solicitud es validada formalmente por el Administrador Institucional.
        </p>
    </div>

    <form method='POST' action='{{ route('register.store') }}' class='mt-6 grid gap-5 sm:grid-cols-2' data-submit-form>
        @csrf
        <div>
            <label for='rut' class='label'>RUT del Funcionario</label>
            <input id='rut' name='rut' value='{{ old('rut') }}' class='input' placeholder='12.345.678-5' required>
            @error('rut')<p class='error'>{{ $message }}</p>@enderror
        </div>
        <div>
            <label for='name' class='label'>Nombre Completo</label>
            <input id='name' name='name' value='{{ old('name') }}' autocomplete='name' class='input' placeholder='Nombres y Apellidos' required>
            @error('name')<p class='error'>{{ $message }}</p>@enderror
        </div>
        <div>
            <label for='job_title' class='label'>Cargo u Oficio Habitual</label>
            <input id='job_title' name='job_title' value='{{ old('job_title') }}' class='input' placeholder='Ej. TENS, Odontólogo/a, Chofer' required>
            <p class='hint'>Función laboral que desempeña en el centro de salud.</p>
            @error('job_title')<p class='error'>{{ $message }}</p>@enderror
        </div>
        <div>
            <label for='establishment_id' class='label'>Centro de Salud Asignado</label>
            <select id='establishment_id' name='establishment_id' class='input' required>
                <option value=''>Seleccione un establecimiento</option>
                @foreach($establishments as $establishment)
                    <option value='{{ $establishment->id }}' @selected(old('establishment_id') == $establishment->id)>
                        {{ $establishment->type }} · {{ $establishment->name }}
                    </option>
                @endforeach
            </select>
            @error('establishment_id')<p class='error'>{{ $message }}</p>@enderror
        </div>
        <div class='sm:col-span-2'>
            <label for='requested_role' class='label'>Rol de Seguridad Solicitado</label>
            <select id='requested_role' name='requested_role' class='input' required>
                <option value=''>Seleccione el rol</option>
                <option value='delegado' @selected(old('requested_role') === 'delegado')>Delegado de Seguridad</option>
                <option value='cphs' @selected(old('requested_role') === 'cphs')>Comité Paritario de Higiene y Seguridad (CPHS)</option>
            </select>
            @error('requested_role')<p class='error'>{{ $message }}</p>@enderror
        </div>
        <div>
            <label for='email' class='label'>Correo Electrónico Institucional</label>
            <input id='email' name='email' type='email' value='{{ old('email') }}' autocomplete='email' class='input' placeholder='funcionario@losangeles.cl' required>
            @error('email')<p class='error'>{{ $message }}</p>@enderror
        </div>
        <div>
            <label for='phone' class='label'>Teléfono de Contacto</label>
            <input id='phone' name='phone' value='{{ old('phone') }}' autocomplete='tel' class='input' placeholder='+56 9 ...' required>
            @error('phone')<p class='error'>{{ $message }}</p>@enderror
        </div>
        <div>
            <label for='password' class='label'>Contraseña</label>
            <div class='relative'>
                <input id='password' name='password' type='password' autocomplete='new-password' class='input pr-20' required>
                <button type='button' class='password-toggle' data-password-toggle='password'>Mostrar</button>
            </div>
            @error('password')<p class='error'>{{ $message }}</p>@enderror
        </div>
        <div>
            <label for='password_confirmation' class='label'>Confirmar Contraseña</label>
            <input id='password_confirmation' name='password_confirmation' type='password' autocomplete='new-password' class='input' required>
        </div>
        <div class='sm:col-span-2 pt-2 border-t border-slate-100'>
            <button type='submit' class='btn-primary w-full py-3 text-sm' data-submit-button>
                Enviar Solicitud de Registro
            </button>
        </div>
    </form>
</div>
</x-layouts.guest>
