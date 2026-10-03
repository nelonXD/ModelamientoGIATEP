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

document.querySelectorAll('[data-tabs]').forEach((tabs) => {
    tabs.querySelectorAll('[data-tab-target]').forEach((button) => button.addEventListener('click', () => {
        const scope = tabs.parentElement;
        tabs.querySelectorAll('[data-tab-target]').forEach((item) => item.classList.toggle('tab-button-active', item === button));
        scope.querySelectorAll('[data-tab-panel]').forEach((panel) => panel.classList.toggle('hidden', panel.dataset.tabPanel !== button.dataset.tabTarget));
    }));
});

document.querySelectorAll('[data-section-target]').forEach((button) => button.addEventListener('click', () => {
    const workspace = button.closest('[data-demo-workspace]');
    workspace.querySelectorAll('[data-section-target]').forEach((item) => item.classList.toggle('section-link-active', item === button));
    workspace.querySelectorAll('[data-section]').forEach((section) => section.classList.toggle('hidden', section.dataset.section !== button.dataset.sectionTarget));
}));

document.querySelectorAll('[data-demo-form]').forEach((form) => {
    let currentStep = 0;
    const steps = [...form.querySelectorAll('[data-step]')];
    const setStep = (index) => {
        currentStep = Math.max(0, Math.min(index, steps.length - 1));
        steps.forEach((step, stepIndex) => step.classList.toggle('hidden', stepIndex !== currentStep));
        form.querySelectorAll('[data-step-target]').forEach((button, buttonIndex) => button.classList.toggle('step-pill-active', buttonIndex === currentStep));
        form.querySelector('[data-step-prev]').disabled = currentStep === 0;
        form.querySelector('[data-step-next]').classList.toggle('hidden', currentStep === steps.length - 1);
        form.querySelector('[data-step-submit]').classList.toggle('hidden', currentStep !== steps.length - 1);
    };
    form.querySelector('[data-step-next]')?.addEventListener('click', () => setStep(currentStep + 1));
    form.querySelector('[data-step-prev]')?.addEventListener('click', () => setStep(currentStep - 1));
    form.querySelectorAll('[data-step-target]').forEach((button, index) => button.addEventListener('click', () => setStep(index)));
    setStep(0);
});

document.querySelector('[data-case-type]')?.addEventListener('change', (event) => {
    const isDisease = event.target.value === 'Enfermedad profesional';
    document.querySelectorAll('[data-accident-field]').forEach((field) => field.classList.toggle('hidden', isDisease));
    document.querySelectorAll('[data-disease-field]').forEach((field) => field.classList.toggle('hidden', ! isDisease));
});

const applyTableFilters = (workspace) => {
    const search = workspace.querySelector('[data-table-search]')?.value.toLowerCase() ?? '';
    const filters = [...workspace.querySelectorAll('[data-table-filter]')];
    let visible = 0;
    workspace.querySelectorAll('[data-table-row]').forEach((row) => {
        const matchesText = row.textContent.toLowerCase().includes(search);
        const matchesFilters = filters.every((filter) => ! filter.value || row.dataset[filter.dataset.tableFilter] === filter.value);
        row.classList.toggle('hidden', ! (matchesText && matchesFilters));
        visible += matchesText && matchesFilters ? 1 : 0;
    });
    workspace.querySelector('[data-filter-empty]')?.classList.toggle('hidden', visible > 0);
};
document.querySelectorAll('[data-table-search], [data-table-filter]').forEach((input) => input.addEventListener('input', () => applyTableFilters(input.closest('[data-demo-workspace]'))));

document.querySelectorAll('[data-local-file]').forEach((input) => input.addEventListener('change', () => {
    const list = input.closest('section, form')?.querySelector('[data-file-list]');
    if (! list) return;
    list.innerHTML = [...input.files].map((file) => `<li class="rounded-xl border bg-white p-3 text-sm"><strong>${file.name}</strong><span class="ml-2 text-slate-500">${Math.ceil(file.size / 1024)} KB · vista previa local</span></li>`).join('');
}));

document.querySelector('[data-ai-example]')?.addEventListener('click', (event) => {
    event.currentTarget.nextElementSibling.classList.remove('hidden');
});
document.querySelector('[data-confirm-ai]')?.addEventListener('click', () => {
    if (window.confirm('¿Incorporar este ejemplo? Se conservará como cambio temporal de la demostración.')) window.alert('Propuesta incorporada a la vista de demostración.');
});

document.querySelectorAll('[data-report-type]').forEach((button) => button.addEventListener('click', () => {
    document.querySelectorAll('[data-report-type]').forEach((item) => item.classList.toggle('report-card-active', item === button));
    document.querySelector('[data-report-title]').textContent = button.dataset.reportType;
}));
document.querySelector('[data-report-preview]')?.addEventListener('click', () => window.alert('Vista previa actualizada con datos de demostración.'));
document.querySelector('[data-refresh-stats]')?.addEventListener('click', () => {
    document.querySelectorAll('[data-stat-number]').forEach((number) => number.classList.add('text-sky-700'));
    window.setTimeout(() => document.querySelectorAll('[data-stat-number]').forEach((number) => number.classList.remove('text-sky-700')), 500);
});

const modal = document.querySelector('[data-demo-modal]');
document.querySelectorAll('[data-open-demo-modal]').forEach((button) => button.addEventListener('click', () => {
    modal?.querySelector('[data-modal-title]')?.replaceChildren(document.createTextNode(button.dataset.record || 'Nuevo registro'));
    modal?.showModal();
}));
document.querySelector('[data-close-demo-modal]')?.addEventListener('click', () => modal?.close());

document.querySelectorAll('[data-unsaved-form]').forEach((form) => {
    let changed = false;
    form.addEventListener('input', () => { changed = true; });
    form.addEventListener('submit', () => { changed = false; });
    window.addEventListener('beforeunload', (event) => {
        if (changed) event.preventDefault();
    });
});
