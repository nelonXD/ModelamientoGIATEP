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

        const fieldValue = (name) => {
            const fields = [...form.querySelectorAll(`[name="${name}"]`)].filter((field) => ! field.disabled);
            const field = fields.find((item) => ! ['radio', 'checkbox'].includes(item.type) || item.checked);
            return field?.value?.trim() || '';
        };
        const classificationFields = [...form.querySelectorAll('[data-case-classification]')];
        const accidentReport = form.querySelector('[data-accident-report]');
        const diseaseReport = form.querySelector('[data-disease-report]');
        const classificationError = form.querySelector('[data-case-classification-error]');
        const routeType = form.querySelector('[data-route-type]');
        const setReportState = (report, visible) => {
            report.classList.toggle('hidden', ! visible);
            report.setAttribute('aria-hidden', visible ? 'false' : 'true');
            report.querySelectorAll('input, textarea, select').forEach((field) => { field.disabled = ! visible; });
        };
        const updateCaseQuestions = () => {
            const type = form.querySelector('[name="case_type"]:checked')?.value || '';
            const isDisease = type === 'Enfermedad profesional';
            setReportState(accidentReport, Boolean(type) && ! isDisease);
            setReportState(diseaseReport, isDisease);
            classificationError.classList.add('hidden');
            if (type === 'Accidente de trayecto') {
                const route = form.querySelector('[name="accident_scope"][value="Trayecto"]');
                route.checked = true;
            }
            const showRoute = type === 'Accidente de trayecto' || form.querySelector('[name="accident_scope"]:checked')?.value === 'Trayecto';
            routeType.classList.toggle('hidden', ! showRoute);
            routeType.querySelectorAll('input').forEach((field) => { field.disabled = ! showRoute; });
        };
        classificationFields.forEach((field) => field.addEventListener('change', updateCaseQuestions));
        form.querySelectorAll('[name="accident_scope"]').forEach((field) => field.addEventListener('change', updateCaseQuestions));
        updateCaseQuestions();

        const birthDate = form.querySelector('[data-worker-birth-date]');
        const workerAge = form.querySelector('[data-worker-age]');
        birthDate?.addEventListener('change', () => {
            if (! birthDate.value) {
                workerAge.value = '';
                return;
            }
            const today = new Date();
            const birth = new Date(`${birthDate.value}T00:00:00`);
            let age = today.getFullYear() - birth.getFullYear();
            const beforeBirthday = today.getMonth() < birth.getMonth() || (today.getMonth() === birth.getMonth() && today.getDate() < birth.getDate());
            if (beforeBirthday) age -= 1;
            workerAge.value = Math.max(0, age);
        });

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
                    output.textContent = fieldValue(output.dataset.review) || 'Sin informar';
                });

                const evidenceFiles = [...(form.querySelector('[data-evidence-files]')?.files || [])];
                wizard.querySelector('[data-review-evidence]').textContent = evidenceFiles.length
                    ? evidenceFiles.map((file) => file.name).join(', ')
                    : 'Sin archivos adjuntos.';
            }

            wizard.scrollIntoView({ behavior: 'smooth', block: 'start' });
        };

        wizard.addEventListener('click', (event) => {
            const trigger = event.target.closest('[data-step-trigger]');
            const next = event.target.closest('[data-next-step]');
            const previous = event.target.closest('[data-previous-step]');
            if (trigger) {
                showStep(Number(trigger.dataset.stepTrigger));
            } else if (next) {
                if (currentStep === 1) {
                    const required = ['employer_name', 'employer_rut', 'employer_address', 'employer_commune', 'economic_activity', 'company_type', 'administrator', 'establishment'];
                    const missingName = required.find((name) => ! fieldValue(name));
                    if (missingName) {
                        form.querySelector(`[name="${missingName}"]`)?.focus();
                        return;
                    }
                }
                if (currentStep === 2) {
                    const required = ['worker_name', 'worker_rut', 'job_title', 'workplace'];
                    const missingName = required.find((name) => ! fieldValue(name));
                    if (missingName) {
                        form.querySelector(`[name="${missingName}"]`)?.focus();
                        return;
                    }
                }
                if (currentStep === 3) {
                    const type = form.querySelector('[name="case_type"]:checked');
                    const activeReport = type?.value === 'Enfermedad profesional' ? diseaseReport : accidentReport;
                    const requiredNames = type?.value === 'Enfermedad profesional'
                        ? ['event_date', 'narrative', 'activity_when_symptoms_began', 'suspected_work_agents']
                        : ['event_date', 'event_time', 'event_location', 'narrative'];
                    const missing = ! type || requiredNames.some((name) => ! fieldValue(name));
                    if (missing || activeReport.classList.contains('hidden')) {
                        classificationError.classList.remove('hidden');
                        (! type ? classificationFields[0] : activeReport.querySelector(`[name="${requiredNames.find((name) => ! fieldValue(name))}"]`))?.focus();
                        return;
                    }
                }
                showStep(currentStep + 1);
            } else if (previous) {
                showStep(currentStep - 1);
            }
        });

        const confirmation = wizard.querySelector('[data-confirm-review]');
        const finishButton = wizard.querySelector('[data-finish]');
        confirmation?.addEventListener('change', () => {
            finishButton.disabled = !confirmation.checked;
        });

        const evidenceInput = form.querySelector('[data-evidence-files]');
        const evidenceList = form.querySelector('[data-evidence-list]');
        const evidencePanel = form.querySelector('[data-evidence-panel]');
        const evidenceCount = form.querySelector('[data-evidence-count]');
        const evidenceDropzone = form.querySelector('[data-evidence-dropzone]');
        let evidenceFiles = [];
        const formatFileSize = (bytes) => bytes >= 1048576 ? `${(bytes / 1048576).toFixed(1)} MB` : `${Math.max(1, Math.round(bytes / 1024))} KB`;
        const syncEvidenceInput = () => {
            const transfer = new DataTransfer();
            evidenceFiles.forEach((file) => transfer.items.add(file));
            evidenceInput.files = transfer.files;
        };
        const renderEvidenceFiles = () => {
            evidencePanel.classList.toggle('hidden', evidenceFiles.length === 0);
            evidenceCount.textContent = `${evidenceFiles.length} ${evidenceFiles.length === 1 ? 'archivo' : 'archivos'}`;
            evidenceList.replaceChildren(...evidenceFiles.map((file, index) => {
                const card = document.createElement('article');
                card.className = 'overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm';
                const preview = document.createElement('div');
                preview.className = 'flex h-36 items-center justify-center overflow-hidden bg-slate-100';
                const objectUrl = URL.createObjectURL(file);
                if (file.type.startsWith('image/')) {
                    preview.innerHTML = `<img class="h-full w-full object-cover" alt="Vista previa de evidencia">`;
                    preview.firstElementChild.src = objectUrl;
                } else if (file.type.startsWith('video/')) {
                    preview.innerHTML = `<video class="h-full w-full bg-black object-contain" controls preload="metadata"></video>`;
                    preview.firstElementChild.src = objectUrl;
                } else if (file.type.startsWith('audio/')) {
                    preview.innerHTML = `<div class="w-full px-4 text-center"><span class="mb-3 block text-3xl" aria-hidden="true">♫</span><audio class="w-full" controls preload="metadata"></audio></div>`;
                    preview.querySelector('audio').src = objectUrl;
                } else if (file.type === 'application/pdf') {
                    preview.innerHTML = `<iframe class="h-full w-full" title="Vista previa de PDF"></iframe>`;
                    preview.firstElementChild.src = objectUrl;
                } else {
                    URL.revokeObjectURL(objectUrl);
                    const extension = file.name.split('.').pop()?.toUpperCase() || 'ARCHIVO';
                    preview.innerHTML = `<div class="text-center"><span class="block text-3xl" aria-hidden="true">▤</span><strong class="mt-2 block text-sm text-slate-600"></strong></div>`;
                    preview.querySelector('strong').textContent = extension;
                }
                const details = document.createElement('div');
                details.className = 'flex items-start justify-between gap-3 p-3';
                details.innerHTML = `<div class="min-w-0"><p class="truncate text-sm font-bold text-slate-900"></p><p class="mt-1 text-xs text-slate-500"></p></div><button type="button" class="shrink-0 rounded-lg px-2 py-1 text-xs font-bold text-red-700 hover:bg-red-50" aria-label="Quitar archivo">Quitar</button>`;
                details.querySelector('p').textContent = file.name;
                details.querySelectorAll('p')[1].textContent = formatFileSize(file.size);
                details.querySelector('button').addEventListener('click', () => {
                    evidenceFiles.splice(index, 1);
                    syncEvidenceInput();
                    renderEvidenceFiles();
                });
                card.append(preview, details);
                return card;
            }));
        };
        const addEvidenceFiles = (files) => {
            [...files].forEach((file) => {
                const duplicate = evidenceFiles.some((current) => current.name === file.name && current.size === file.size);
                if (! duplicate) evidenceFiles.push(file);
            });
            syncEvidenceInput();
            renderEvidenceFiles();
        };
        evidenceInput?.addEventListener('change', () => addEvidenceFiles(evidenceInput.files));
        ['dragenter', 'dragover'].forEach((eventName) => evidenceDropzone?.addEventListener(eventName, (event) => {
            event.preventDefault();
            evidenceDropzone.classList.add('border-sky-600', 'bg-sky-50');
        }));
        ['dragleave', 'drop'].forEach((eventName) => evidenceDropzone?.addEventListener(eventName, (event) => {
            event.preventDefault();
            evidenceDropzone.classList.remove('border-sky-600', 'bg-sky-50');
        }));
        evidenceDropzone?.addEventListener('drop', (event) => addEvidenceFiles(event.dataTransfer.files));

        form.addEventListener('submit', (event) => {
            event.preventDefault();

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
                employerCommune: fieldValue('employer_commune'),
                economicActivity: fieldValue('economic_activity'),
                nationality: fieldValue('nationality'),
                workerCommune: fieldValue('worker_commune'),
                date: fieldValue('event_date'),
                time: fieldValue('event_time'),
                location: fieldValue('event_location'),
                status: 'Borrador',
                summary: fieldValue('narrative'),
                witnesses: fieldValue('witnesses'),
                evidenceFiles: evidenceFiles.map((file) => ({ name: file.name, size: file.size, type: file.type })),
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
