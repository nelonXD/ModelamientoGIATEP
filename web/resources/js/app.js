document.querySelectorAll('[data-password-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
        const input = document.getElementById(button.dataset.passwordToggle);
        input.type = input.type === 'password' ? 'text' : 'password';
        button.textContent = input.type === 'password' ? 'Mostrar' : 'Ocultar';
    });
});

document.querySelectorAll('[data-submit-form]').forEach((form) => {
    form.addEventListener('submit', () => {
        const button = form.querySelector('[data-submit-button]');
        button.disabled = true;
        button.textContent = 'Procesando…';
    });
});

const sidebar = document.querySelector('[data-sidebar]');
const openMenu = () => sidebar?.classList.remove('hidden');
const closeMenu = () => sidebar?.classList.add('hidden');
document.querySelector('[data-menu-toggle]')?.addEventListener('click', openMenu);
document.querySelector('[data-menu-close]')?.addEventListener('click', closeMenu);

document.querySelectorAll('[data-confirm]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        if (! window.confirm(form.dataset.confirm)) {
            event.preventDefault();
        }
    });
});

const loginForm = document.querySelector('[data-login-form]');

if (loginForm) {
    const rutInput = loginForm.querySelector('[data-login-rut]');
    const rutStep = loginForm.querySelector('[data-login-rut-step]');
    const continueButton = loginForm.querySelector('[data-login-continue]');
    const passwordStep = loginForm.querySelector('[data-login-password-step]');
    const passwordInput = loginForm.querySelector('#password');
    const changeRutButton = loginForm.querySelector('[data-login-change-rut]');
    const rutSummary = loginForm.querySelector('[data-login-rut-summary]');
    const rutError = loginForm.querySelector('[data-rut-error]');

    const normalizeRut = (value) => value.toUpperCase().replace(/[^0-9K]/g, '');
    const isValidRut = (value) => {
        const rut = normalizeRut(value);

        if (rut.length < 2) {
            return false;
        }

        const body = rut.slice(0, -1);
        const verifier = rut.slice(-1);
        let sum = 0;
        let multiplier = 2;

        for (let index = body.length - 1; index >= 0; index -= 1) {
            sum += Number(body[index]) * multiplier;
            multiplier = multiplier === 7 ? 2 : multiplier + 1;
        }

        const remainder = 11 - (sum % 11);
        const expectedVerifier = remainder === 11 ? '0' : remainder === 10 ? 'K' : String(remainder);

        return verifier === expectedVerifier;
    };
    const formatRut = (value) => {
        const rut = normalizeRut(value);
        const body = rut.slice(0, -1).replace(/\B(?=(\d{3})+(?!\d))/g, '.');

        return `${body}-${rut.slice(-1)}`;
    };
    const showPasswordStep = async () => {
        if (! isValidRut(rutInput.value)) {
            rutError.textContent = 'Ingresa un RUT válido.';
            rutError.classList.remove('hidden');
            rutInput.setAttribute('aria-invalid', 'true');
            rutInput.focus();

            return;
        }

        rutInput.value = formatRut(rutInput.value);
        continueButton.disabled = true;
        continueButton.textContent = 'Verificando…';
        rutError.classList.add('hidden');

        try {
            const response = await fetch(loginForm.dataset.checkRutUrl, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': loginForm.querySelector('[name="_token"]').value,
                },
                body: JSON.stringify({ rut: rutInput.value }),
            });
            const result = await response.json();

            if (! response.ok) {
                rutError.textContent = result.errors?.rut?.[0] ?? 'No fue posible verificar el RUT. Intenta nuevamente.';
                rutError.classList.remove('hidden');
                rutInput.setAttribute('aria-invalid', 'true');
                rutInput.focus();

                return;
            }
        } catch {
            rutError.textContent = 'No fue posible verificar el RUT. Intenta nuevamente.';
            rutError.classList.remove('hidden');
            rutInput.focus();

            return;
        } finally {
            continueButton.disabled = false;
            continueButton.textContent = 'Continuar';
        }

        rutInput.readOnly = true;
        rutInput.removeAttribute('aria-invalid');
        rutError.classList.add('hidden');
        rutSummary.textContent = rutInput.value;
        rutStep.classList.add('hidden');
        continueButton.classList.add('hidden');
        passwordStep.classList.remove('hidden');
        passwordInput.disabled = false;
        passwordInput.focus();
    };

    continueButton.addEventListener('click', showPasswordStep);
    rutInput.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            showPasswordStep();
        }
    });
    rutInput.addEventListener('input', () => {
        rutError.classList.add('hidden');
        rutInput.removeAttribute('aria-invalid');
    });
    changeRutButton.addEventListener('click', () => {
        passwordStep.classList.add('hidden');
        rutStep.classList.remove('hidden');
        continueButton.classList.remove('hidden');
        passwordInput.disabled = true;
        passwordInput.value = '';
        rutInput.readOnly = false;
        rutInput.focus();
        rutInput.select();
    });
}
