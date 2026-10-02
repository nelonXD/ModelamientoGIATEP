<script>
    (() => {
        const wizard = document.querySelector('[data-case-wizard]');

        if (!wizard) {
            return;
        }

        const panels = [...wizard.querySelectorAll('[data-step-panel]')];
        const triggers = [...wizard.querySelectorAll('[data-step-trigger]')];
        const form = wizard.querySelector('[data-case-form]');
        const storageKey = 'giatep.demo.cases';
        let currentStep = 1;

        const fieldValue = (name) => form.elements.namedItem(name)?.value?.trim() || '';

        const showStep = (step) => {
            currentStep = Math.min(4, Math.max(1, step));

            panels.forEach((panel) => panel.classList.toggle('hidden', Number(panel.dataset.stepPanel) !== currentStep));
            triggers.forEach((trigger) => {
                const isCurrent = Number(trigger.dataset.stepTrigger) === currentStep;
                trigger.classList.toggle('border-blue-200', isCurrent);
                trigger.classList.toggle('bg-blue-50', isCurrent);
                trigger.setAttribute('aria-current', isCurrent ? 'step' : 'false');

                const number = trigger.querySelector('[data-step-number]');
                number.classList.toggle('bg-blue-950', isCurrent);
                number.classList.toggle('text-white', isCurrent);
                number.classList.toggle('bg-slate-200', !isCurrent);
                number.classList.toggle('text-slate-600', !isCurrent);
            });

            if (currentStep === 4) {
                wizard.querySelectorAll('[data-review]').forEach((output) => {
                    const field = form.elements.namedItem(output.dataset.review);
                    output.textContent = field?.value?.trim() || 'Sin informar';
                });

                const actionSummary = wizard.querySelector('[data-review-actions]');
                const actionRows = [...wizard.querySelectorAll('[data-action-row]')];
                actionSummary.replaceChildren(...actionRows.map((row) => {
                    const item = document.createElement('li');
                    const measure = row.querySelector('[name="control_measures[]"]')?.value.trim() || 'Medida sin describir';
                    const owner = row.querySelector('[name="action_owners[]"]')?.value.trim() || 'Responsable pendiente';
                    const deadline = row.querySelector('[name="action_deadlines[]"]')?.value || 'Sin plazo';
                    item.className = 'rounded-xl border border-sky-100 bg-white p-4 text-sm text-slate-600';
                    item.textContent = `${measure} · ${owner} · ${deadline}`;

                    return item;
                }));
            }

            wizard.scrollIntoView({ behavior: 'smooth', block: 'start' });
        };

        wizard.addEventListener('click', (event) => {
            const trigger = event.target.closest('[data-step-trigger]');
            const next = event.target.closest('[data-next-step]');
            const previous = event.target.closest('[data-previous-step]');
            const addAction = event.target.closest('[data-add-action]');
            const removeAction = event.target.closest('[data-remove-action]');

            if (addAction) {
                const template = wizard.querySelector('[data-action-template]');
                wizard.querySelector('[data-action-plan]').append(template.content.cloneNode(true));
            } else if (removeAction) {
                const rows = wizard.querySelectorAll('[data-action-row]');
                if (rows.length > 1) {
                    removeAction.closest('[data-action-row]').remove();
                }
            } else if (trigger) {
                showStep(Number(trigger.dataset.stepTrigger));
            } else if (next) {
                showStep(currentStep + 1);
            } else if (previous) {
                showStep(currentStep - 1);
            }
        });

        wizard.querySelector('[data-generate-ai]')?.addEventListener('click', () => {
            wizard.querySelector('[data-ai-empty]').classList.add('hidden');
            wizard.querySelector('[data-ai-results]').classList.remove('hidden');
        });

        const confirmation = wizard.querySelector('[data-confirm-review]');
        const finishButton = wizard.querySelector('[data-finish]');
        confirmation?.addEventListener('change', () => {
            finishButton.disabled = !confirmation.checked;
        });

        form.addEventListener('submit', (event) => {
            event.preventDefault();

            const measures = [...wizard.querySelectorAll('[data-action-row]')].map((row) => ({
                measure: row.querySelector('[name="control_measures[]"]')?.value.trim() || '',
                owner: row.querySelector('[name="action_owners[]"]')?.value.trim() || '',
                deadline: row.querySelector('[name="action_deadlines[]"]')?.value || '',
            })).filter((action) => action.measure || action.owner || action.deadline);

            const now = new Date();
            const storedCase = {
                folio: `GIATEP-DEMO-${now.getTime().toString().slice(-7)}`,
                type: form.querySelector('[name="case_type"]:checked')?.value || 'Sin informar',
                person: fieldValue('worker_name'),
                workerRut: fieldValue('worker_rut'),
                jobTitle: fieldValue('job_title'),
                workplace: fieldValue('workplace'),
                phone: fieldValue('phone'),
                employerName: fieldValue('employer_name'),
                employerRut: fieldValue('employer_rut'),
                administrator: fieldValue('administrator'),
                establishment: fieldValue('establishment'),
                employerAddress: fieldValue('employer_address'),
                date: fieldValue('event_date'),
                time: fieldValue('event_time'),
                location: fieldValue('event_location'),
                status: 'Borrador',
                summary: fieldValue('narrative'),
                witnesses: fieldValue('witnesses'),
                measures,
                savedAt: now.toISOString(),
                demo: true,
            };

            try {
                const storedCases = JSON.parse(localStorage.getItem(storageKey) || '[]');
                const cases = Array.isArray(storedCases) ? storedCases : [];
                cases.unshift(storedCase);
                localStorage.setItem(storageKey, JSON.stringify(cases));

                wizard.querySelector('[data-prototype-message]').classList.remove('hidden');
                setTimeout(() => window.location.assign(wizard.dataset.listUrl), 500);
            } catch (error) {
                const message = wizard.querySelector('[data-prototype-message]');
                message.classList.remove('hidden', 'border-emerald-200', 'bg-emerald-50', 'text-emerald-800');
                message.classList.add('border-red-200', 'bg-red-50', 'text-red-800');
                message.textContent = 'No fue posible guardar el caso en este navegador. Revisa si el almacenamiento local está habilitado.';
            }
        });

        showStep(1);
    })();
</script>
