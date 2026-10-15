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

document.querySelectorAll('[data-story-quality]').forEach((qualityPanel) => {
    const workspace = qualityPanel.closest('[data-demo-workspace]');
    const story = workspace?.querySelector('#final-investigation-story');
    const criteria = [...qualityPanel.querySelectorAll('[data-story-criterion]')];
    const status = qualityPanel.querySelector('[data-story-quality-status]');
    const warning = qualityPanel.querySelector('[data-story-subjective-warning]');
    const subjectiveTerms = [
        'imprudente',
        'irresponsable',
        'descuidado',
        'negligente',
        'por culpa',
        'obviamente',
        'siempre',
        'nunca',
    ];

    const updateStoryQuality = () => {
        const value = story?.value.toLocaleLowerCase('es-CL') ?? '';
        const completed = criteria.filter((criterion) => criterion.checked).length;
        const hasSubjectiveTerm = subjectiveTerms.some((term) => value.includes(term));
        const isReady = completed === criteria.length && value.trim().length >= 120 && ! hasSubjectiveTerm;

        warning.classList.toggle('hidden', ! hasSubjectiveTerm);
        status.textContent = isReady ? 'Listo para revisión humana' : `${completed}/${criteria.length} criterios confirmados`;
        status.classList.toggle('status-approved', isReady);
        status.classList.toggle('status-pending', ! isReady);
    };

    criteria.forEach((criterion) => criterion.addEventListener('change', updateStoryQuality));
    story?.addEventListener('input', updateStoryQuality);
    updateStoryQuality();
});

document.querySelectorAll('[data-interview-form]').forEach((form) => {
    const witnessType = form.querySelector('[data-witness-type]');
    const applicability = {
        affected: ['chronology', 'task', 'protection', 'training', 'evidence', 'environment'],
        direct: ['chronology', 'task', 'protection', 'training', 'evidence', 'environment'],
        indirect: ['chronology', 'evidence', 'environment'],
        context: ['task', 'protection', 'training', 'evidence', 'environment'],
        supervisor: ['task', 'protection', 'training', 'evidence', 'environment'],
        technical: ['task', 'protection', 'training', 'evidence', 'environment'],
        institutional: ['training', 'evidence'],
        documentary: ['evidence'],
        emergency: ['chronology', 'evidence', 'environment'],
    };

    const adaptQuestions = () => {
        const applicableGroups = new Set(applicability[witnessType.value] || []);

        form.querySelectorAll('[data-question-group]').forEach((group) => {
            const applies = applicableGroups.has(group.dataset.questionGroup);

            if (! applies) {
                group.querySelectorAll('input[type="radio"][value="na"]').forEach((radio) => { radio.checked = true; });
                group.querySelectorAll('textarea').forEach((textarea) => { textarea.value = ''; });
            }
        });
    };

    witnessType.addEventListener('change', adaptQuestions);
    adaptQuestions();

    form.querySelectorAll('[data-conditional-detail]').forEach((row) => {
        const field = row.querySelector('[data-conditional-input]');
        const input = field.querySelector('textarea, input');
        row.querySelectorAll('input[type="radio"]').forEach((radio) => radio.addEventListener('change', () => {
            const show = radio.checked && radio.value === row.dataset.detailWhen;
            field.classList.toggle('hidden', ! show);
            input.disabled = ! show;
            if (! show) input.value = '';
        }));
    });

    form.querySelectorAll('[data-toggle-other]').forEach((checkbox) => checkbox.addEventListener('change', () => {
        const field = form.querySelector(`[data-other-field="${checkbox.dataset.toggleOther}"]`);
        const input = field.querySelector('input, textarea');
        field.classList.toggle('hidden', ! checkbox.checked);
        input.disabled = ! checkbox.checked;
        if (! checkbox.checked) input.value = '';
    }));

    form.querySelectorAll('[data-development-question]').forEach((question) => {
        const notApplicable = question.querySelector('[data-development-na]');
        const answer = question.querySelector('textarea');
        notApplicable.addEventListener('change', () => {
            answer.disabled = notApplicable.checked;
            answer.classList.toggle('hidden', notApplicable.checked);
            if (notApplicable.checked) answer.value = '';
        });
    });

    form.querySelectorAll('[data-yes-options]').forEach((control) => {
        const panel = control.querySelector('[data-yes-options-panel]');
        const radios = [...control.querySelectorAll(':scope > div:first-child input[type="radio"]')];
        const updateOptions = () => {
            const show = radios.some((radio) => radio.checked && radio.value === 'yes');
            panel.classList.toggle('hidden', ! show);
            panel.querySelectorAll('input').forEach((input) => {
                input.disabled = ! show;
                if (! show && input.type === 'checkbox') input.checked = false;
            });
            panel.querySelectorAll('[data-other-field]').forEach((field) => field.classList.add('hidden'));
        };
        radios.forEach((radio) => radio.addEventListener('change', updateOptions));
        updateOptions();
    });
});

document.querySelectorAll('[data-interviews-workspace]').forEach((workspace) => {
    const list = workspace.querySelector('[data-interview-list]');
    const editor = workspace.querySelector('[data-interview-editor]');
    const rows = workspace.querySelector('[data-interview-rows]');
    const legacyQuestionnaire = workspace.querySelector('[data-legacy-questionnaire]');
    const adaptedQuestionnaire = workspace.querySelector('[data-adapted-questionnaire]');
    const formTitle = workspace.querySelector('[data-interview-form-title]');
    const showEditor = (mode = 'adapted') => {
        editor.dataset.interviewMode = mode;
        const useLegacy = mode === 'legacy';
        legacyQuestionnaire.classList.toggle('hidden', ! useLegacy);
        legacyQuestionnaire.disabled = ! useLegacy;
        legacyQuestionnaire.setAttribute('aria-hidden', useLegacy ? 'false' : 'true');
        adaptedQuestionnaire.classList.toggle('hidden', useLegacy);
        adaptedQuestionnaire.querySelectorAll('input, textarea, select').forEach((field) => { field.disabled = useLegacy; });
        if (! useLegacy) updateInterviewFlow();
        formTitle.textContent = useLegacy ? 'Formulario de entrevista anterior' : 'Formulario de entrevista';
        list.classList.add('hidden');
        editor.classList.remove('hidden');
    };
    const showList = () => {
        editor.classList.add('hidden');
        list.classList.remove('hidden');
    };

    const clearFormForNew = () => {
        const nameField = workspace.querySelector('[name="interviewee_name"]');
        if (nameField) nameField.value = '';
        const rutField = workspace.querySelector('[name="interviewee_rut"]');
        if (rutField) rutField.value = '';
        const roleField = workspace.querySelector('[name="interviewee_role"]');
        if (roleField) roleField.value = '';
        const dateField = workspace.querySelector('[name="interview_date"]');
        if (dateField) dateField.value = new Date().toISOString().split('T')[0];
        editor.querySelectorAll('input[type="radio"], input[type="checkbox"]').forEach((input) => { input.checked = false; });
        editor.querySelectorAll('textarea').forEach((textarea) => { textarea.value = ''; });
        const validation = workspace.querySelector('[data-interview-validation]');
        if (validation) validation.classList.add('hidden');
    };

    workspace.querySelectorAll('[data-create-interview]').forEach((button) => button.addEventListener('click', () => {
        if (! button.closest('tr')) clearFormForNew();
        showEditor('adapted');
    }));
    workspace.querySelectorAll('[data-create-legacy-interview]').forEach((button) => button.addEventListener('click', () => {
        clearFormForNew();
        showEditor('legacy');
    }));
    workspace.querySelectorAll('[data-cancel-interview]').forEach((button) => button.addEventListener('click', showList));

    const requiredRadioNames = [
        'task_own_job', 'task_habitual', 'task_same_way', 'task_usual_accident_possible', 'task_frequency',
        'instructions_received', 'followed_instructions', 'ppe_habitual', 'ppe_followup_0', 'ppe_followup_1',
        'ppe_followup_2', 'usual_place', 'usual_place_possible', 'usual_time', 'usual_time_possible',
        'equipment_used', 'equipment_followup_0', 'equipment_followup_1', 'substance_involved', 'substance_habitual',
    ];
    const requiredFieldNames = ['interviewee_name', 'interview_date', 'interviewee_role', 'ppe_description', 'different_place_reason', 'different_time_reason', 'equipment_description', 'equipment_reason', 'substance_reason'];
    const appendRequiredMark = (field) => {
        const isChoice = ['radio', 'checkbox'].includes(field.type);
        const container = isChoice
            ? field.closest('div.flex, div.grid')?.parentElement
            : field.closest('label');
        const label = container?.querySelector(':scope > .label, :scope > p.label, :scope > span.label') || field.closest('label')?.querySelector('.label');
        if (label && ! label.querySelector('[data-required-mark]')) {
            const mark = document.createElement('span');
            mark.dataset.requiredMark = '';
            mark.className = 'ml-1 text-red-700';
            mark.textContent = '*';
            label.append(mark);
        }
    };
    requiredRadioNames.forEach((name) => {
        const fields = workspace.querySelectorAll(`[name="${name}"]`);
        fields.forEach((field) => { field.dataset.interviewRequired = ''; });
        if (fields[0]) appendRequiredMark(fields[0]);
    });
    requiredFieldNames.forEach((name) => {
        const field = workspace.querySelector(`[name="${name}"]`);
        if (field) {
            field.dataset.interviewRequired = '';
            appendRequiredMark(field);
        }
    });

    workspace.querySelectorAll('[data-adapted-questionnaire] input[type="radio"][value="na"]').forEach((radio) => {
        radio.addEventListener('pointerdown', () => {
            radio.dataset.wasChecked = radio.checked ? 'true' : 'false';
        });
        radio.addEventListener('click', () => {
            if (radio.dataset.wasChecked === 'true') {
                radio.checked = false;
                radio.dataset.wasChecked = 'false';
                radio.dispatchEvent(new Event('change', { bubbles: true }));
            }
        });
        radio.addEventListener('keydown', (event) => {
            if (event.key === ' ' && radio.checked) {
                event.preventDefault();
                radio.checked = false;
                radio.dispatchEvent(new Event('change', { bubbles: true }));
            }
        });
        radio.addEventListener('change', () => {
            const group = radio.closest('[data-branch-question]') || radio.closest('div.flex, div.grid')?.parentElement;
            if (! group) return;
            group.dataset.omitQuestion = radio.checked ? 'true' : 'false';
            let notice = group.querySelector(':scope > [data-not-applicable-notice]');
            if (! notice) {
                notice = document.createElement('p');
                notice.dataset.notApplicableNotice = '';
                notice.className = 'mt-2 text-xs font-semibold text-slate-500';
                notice.textContent = 'Esta pregunta y sus datos asociados no se incluirán en el registro.';
                group.append(notice);
            }
            notice.classList.toggle('hidden', ! radio.checked);
        });
    });

    workspace.querySelectorAll('[data-branch-question]').forEach((question) => {
        const panel = question.querySelector(':scope > [data-branch-panel]');
        if (! panel) return;
        const radios = [...question.querySelectorAll('input[type="radio"]')].filter((radio) => radio.closest('[data-branch-question]') === question);
        const updateBranch = () => {
            const selected = radios.find((radio) => radio.checked);
            const show = selected?.value === question.dataset.branchValue;
            panel.classList.toggle('hidden', ! show);
            panel.querySelectorAll('input, textarea, select').forEach((field) => { field.disabled = ! show; });
        };
        radios.forEach((radio) => radio.addEventListener('change', updateBranch));
        updateBranch();
    });

    const selectedValue = (name) => workspace.querySelector(`[name="${name}"]:checked`)?.value || '';
    const hasAnswer = (name) => selectedValue(name) !== '';
    const questionBlock = (name) => {
        const input = workspace.querySelector(`[name="${name}"]`);
        if (! input) return null;
        return input.closest('[data-branch-question]') || input.closest('.grid')?.parentElement || input.parentElement;
    };
    const setFlowVisibility = (element, visible) => {
        if (! element) return;
        element.classList.toggle('hidden', ! visible);
        element.querySelectorAll('input, textarea, select').forEach((field) => {
            field.disabled = ! visible;
            if (! visible && field.type === 'radio') field.checked = false;
            if (! visible && field.type === 'checkbox') field.checked = false;
        });
    };
    const splitBranchPanel = (name, yesName) => {
        const question = workspace.querySelector(`[name="${name}"]`)?.closest('[data-branch-question]');
        const panel = question?.querySelector(':scope > [data-branch-panel]');
        if (! panel) return;
        const children = [...panel.children];
        const value = selectedValue(name);
        panel.classList.toggle('hidden', ! ['yes', 'no'].includes(value));
        setFlowVisibility(children[0], value === 'yes');
        setFlowVisibility(children[1], value === 'no');
        if (value === 'yes' && yesName && ! hasAnswer(yesName)) {
            children[0]?.querySelector('input')?.focus({ preventScroll: true });
        }
    };
    const updateInterviewFlow = () => {
        const ownJob = selectedValue('task_own_job');
        setFlowVisibility(questionBlock('task_habitual'), ownJob === 'yes');
        const habitual = selectedValue('task_habitual');
        const habitualPathComplete = ['no', 'na'].includes(habitual)
            || (habitual === 'yes' && hasAnswer('task_same_way') && hasAnswer('task_usual_accident_possible'));
        setFlowVisibility(questionBlock('task_frequency'), ['no', 'na'].includes(ownJob) || habitualPathComplete);
        setFlowVisibility(questionBlock('instructions_received'), hasAnswer('task_frequency'));
        setFlowVisibility(questionBlock('ppe_habitual'), ['no', 'na'].includes(selectedValue('instructions_received')) || hasAnswer('followed_instructions'));

        const placeFieldset = workspace.querySelector('[name="usual_place"]')?.closest('fieldset');
        setFlowVisibility(placeFieldset, ['no', 'na'].includes(selectedValue('ppe_habitual')) || hasAnswer('ppe_followup_2'));
        splitBranchPanel('usual_place', 'usual_place_possible');

        const timeFieldset = workspace.querySelector('[name="usual_time"]')?.closest('fieldset');
        setFlowVisibility(timeFieldset, Boolean(placeFieldset && ! placeFieldset.classList.contains('hidden')));
        splitBranchPanel('usual_time', 'usual_time_possible');

        const equipmentFieldset = workspace.querySelector('[name="equipment_used"]')?.closest('fieldset');
        const usualTime = selectedValue('usual_time');
        const timePathComplete = ['no', 'na'].includes(usualTime) || (usualTime === 'yes' && hasAnswer('usual_time_possible'));
        setFlowVisibility(equipmentFieldset, Boolean(timeFieldset && ! timeFieldset.classList.contains('hidden') && timePathComplete));

        const equipmentPanel = workspace.querySelector('[name="equipment_used"]')?.closest('[data-branch-question]')?.querySelector(':scope > [data-branch-panel]');
        if (equipmentPanel) {
            const equipmentUsed = selectedValue('equipment_used');
            equipmentPanel.classList.toggle('hidden', equipmentUsed !== 'yes');
            equipmentPanel.querySelectorAll('input, textarea').forEach((field) => { field.disabled = equipmentUsed !== 'yes'; });
            const reason = equipmentPanel.lastElementChild;
            setFlowVisibility(reason, selectedValue('equipment_followup_0') === 'no');
        }

        const materialsFieldset = workspace.querySelector('[name="substance_involved"]')?.closest('fieldset');
        const equipmentUsed = selectedValue('equipment_used');
        const equipmentPathComplete = ['no', 'na'].includes(equipmentUsed)
            || (equipmentUsed === 'yes' && (hasAnswer('equipment_followup_1') || selectedValue('equipment_followup_0') === 'no'));
        setFlowVisibility(materialsFieldset, Boolean(equipmentFieldset && ! equipmentFieldset.classList.contains('hidden') && equipmentPathComplete));
        const substancePanel = workspace.querySelector('[name="substance_involved"]')?.closest('[data-branch-question]')?.querySelector(':scope > [data-branch-panel]');
        if (substancePanel) {
            const involved = selectedValue('substance_involved') === 'yes';
            substancePanel.classList.toggle('hidden', ! involved);
            substancePanel.querySelectorAll('input, textarea').forEach((field) => { field.disabled = ! involved; });
            const reason = substancePanel.lastElementChild;
            setFlowVisibility(reason, involved && selectedValue('substance_habitual') === 'no');
        }

        const substance = selectedValue('substance_involved');
        const substancePathComplete = ['no', 'na'].includes(substance)
            || (substance === 'yes' && hasAnswer('substance_habitual'));
        ['environment_factor_event_0', 'musculoskeletal_factor_event_0', 'organization_factor_event_0'].forEach((name) => {
            setFlowVisibility(workspace.querySelector(`[name="${name}"]`)?.closest('fieldset'), Boolean(materialsFieldset && ! materialsFieldset.classList.contains('hidden') && substancePathComplete));
        });
    };

    workspace.querySelectorAll('[data-adapted-questionnaire] input[type="radio"]').forEach((radio) => radio.addEventListener('change', updateInterviewFlow));
    updateInterviewFlow();

    workspace.querySelector('[data-save-interview]')?.addEventListener('click', async () => {
        const validation = workspace.querySelector('[data-interview-validation]');
        const missing = [];
        if (editor.dataset.interviewMode !== 'legacy') {
            requiredRadioNames.forEach((name) => {
                const enabled = [...workspace.querySelectorAll(`[name="${name}"]`)].filter((field) => ! field.disabled);
                const notApplicable = enabled.some((field) => field.checked && field.value === 'na');
                if (enabled.length && ! notApplicable && ! enabled.some((field) => field.checked)) missing.push(enabled[0]);
            });
            requiredFieldNames.forEach((name) => {
                const field = workspace.querySelector(`[name="${name}"]`);
                if (field && ! field.disabled && ! field.value.trim()) missing.push(field);
            });
        }
        workspace.querySelectorAll('[data-interview-required]').forEach((field) => field.removeAttribute('aria-invalid'));
        if (missing.length) {
            missing.forEach((field) => field.setAttribute('aria-invalid', 'true'));
            validation.classList.remove('hidden');
            missing[0].focus();
            return;
        }
        validation.classList.add('hidden');
        const name = workspace.querySelector('[name="interviewee_name"]').value.trim() || 'Entrevistado sin nombre';
        const witness = workspace.querySelector('[name="witness_type"]');
        const role = workspace.querySelector('[name="interviewee_role"]').value.trim() || 'Sin cargo informado';
        const dateValue = workspace.querySelector('[name="interview_date"]').value;
        const answers = {};
        editor.querySelectorAll('[name]:not([disabled])').forEach((field) => {
            if (['interviewee_name', 'interviewee_rut', 'interviewee_role', 'interviewee_company', 'witness_type', 'interview_date', 'interview_start_time', 'interview_location'].includes(field.name)) return;
            if ((field.type === 'radio' || field.type === 'checkbox') && ! field.checked) return;
            const key = field.name.replace(/\[\]$/, '');
            if (field.name.endsWith('[]')) answers[key] = [...(answers[key] || []), field.value];
            else answers[key] = field.value;
        });
        const shell = workspace.closest('[data-demo-workspace]');
        const response = await fetch(shell.dataset.interviewStoreUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: JSON.stringify({
                case_folio: shell.dataset.caseFolio, questionnaire_type: editor.dataset.interviewMode === 'legacy' ? 'legacy' : 'adapted',
                interviewee_name: name, interviewee_rut: workspace.querySelector('[name="interviewee_rut"]')?.value || null,
                interviewee_role: role, interviewee_company: workspace.querySelector('[name="interviewee_company"]')?.value || null,
                witness_type: witness.value, interview_date: dateValue || null,
                interview_start_time: workspace.querySelector('[name="interview_start_time"]')?.value || null,
                interview_location: workspace.querySelector('[name="interview_location"]')?.value || null, answers,
            }),
        });
        const stored = await response.json();
        if (! response.ok) {
            validation.textContent = stored.message || 'No fue posible guardar la entrevista.';
            validation.classList.remove('hidden');
            return;
        }
        const row = document.createElement('tr');
        [name, witness.options[witness.selectedIndex].text, role, dateValue ? dateValue.split('-').reverse().join('/') : 'Sin fecha'].forEach((value, index) => {
            const cell = document.createElement('td');
            cell.textContent = value;
            if (index === 0) cell.className = 'font-bold';
            row.append(cell);
        });
        const statusCell = document.createElement('td');
        const status = document.createElement('span');
        status.className = 'status status-approved';
        status.textContent = 'Completada';
        statusCell.append(status);
        const actionCell = document.createElement('td');
        const action = document.createElement('button');
        action.type = 'button';
        action.className = 'text-link';
        action.textContent = 'Ver / editar';
        action.addEventListener('click', () => showEditor('adapted'));
        actionCell.append(action);
        const emptyRow = rows.querySelector('[data-empty-interview-row]');
        if (emptyRow) emptyRow.remove();
        rows.prepend(row);
        showList();
    });

    workspace.closest('form')?.addEventListener('submit', () => {
        workspace.querySelectorAll('[data-omit-question="true"]').forEach((question) => {
            question.querySelectorAll('input, textarea, select').forEach((field) => { field.disabled = true; });
        });
    });
});

document.querySelectorAll('[data-final-story-ai]').forEach((section) => {
    const button = section.querySelector('[data-final-ai-generate]');
    const loading = section.querySelector('[data-final-ai-loading]');
    const error = section.querySelector('[data-final-ai-error]');
    const result = section.querySelector('[data-final-ai-result]');
    const story = section.querySelector('[name="final_story"]');
    const storySaveButton = section.querySelector('[data-save-final-story]');
    const storySaved = section.querySelector('[data-final-story-saved]');
    const saveButton = result.querySelector('[data-save-ai-proposal]');
    const investigation = section.closest('form');

    const saveFinalStory = async () => {
        if (! story.value.trim()) throw new Error('Escriba el relato final antes de guardarlo.');
        const workspace = section.closest('[data-demo-workspace]');
        const response = await fetch(workspace.dataset.storyStoreUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: JSON.stringify({ case_folio: workspace.dataset.caseFolio, story: story.value.trim() }),
        });
        const payload = await response.json();
        if (! response.ok) throw new Error(payload.message || 'No fue posible guardar el relato final.');
        storySaved.classList.remove('hidden');
        return payload;
    };

    story.addEventListener('input', () => storySaved.classList.add('hidden'));
    storySaveButton.addEventListener('click', async () => {
        error.classList.add('hidden');
        storySaveButton.disabled = true;
        try {
            await saveFinalStory();
        } catch (exception) {
            error.textContent = exception.message;
            error.classList.remove('hidden');
        } finally {
            storySaveButton.disabled = false;
        }
    });

    button.addEventListener('click', async () => {
        if (! story.value.trim()) {
            error.classList.remove('hidden');
            story.focus();
            return;
        }

        error.classList.add('hidden');
        button.disabled = true;
        button.textContent = 'Analizando…';
        loading.classList.remove('hidden');
        let elapsedSeconds = 0;
        loading.textContent = 'Analizando hechos y factores causales con LM Studio… (0s)';
        const timer = setInterval(() => {
            elapsedSeconds++;
            loading.textContent = `Analizando hechos y factores causales con LM Studio… (${elapsedSeconds}s)`;
        }, 1000);
        result.classList.add('hidden');
        result.querySelectorAll('input, textarea').forEach((field) => { field.disabled = true; });

        const workspace = section.closest('[data-demo-workspace]');
        try {
            await saveFinalStory();
        } catch (exception) {
            clearInterval(timer);
            loading.classList.add('hidden'); error.textContent = exception.message; error.classList.remove('hidden');
            button.disabled = false; button.textContent = 'Analizar relato final';
            return;
        }
        fetch(workspace.dataset.aiAnalyzeUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: JSON.stringify({ story: story.value.trim() }),
        }).then(async (response) => {
            clearInterval(timer);
            const payload = await response.json();
            if (! response.ok) throw new Error(payload.message || 'No fue posible generar el análisis.');

            result.dataset.model = payload.model;
            const facts = result.querySelector('[data-ai-facts]');
            facts.replaceChildren(...payload.facts.map((value) => {
                const input = document.createElement('input');
                input.className = 'input'; input.name = 'ai_facts[]'; input.value = value;
                return input;
            }));
            result.querySelector('[name="ai_tree_final"]').value = payload.tree.final;
            result.querySelector('[name="ai_tree_origin"]').value = payload.tree.origin;
            const treeGrid = result.querySelector('[name="ai_tree_final"]').closest('.grid');
            treeGrid.querySelectorAll('[name="ai_tree_cause[]"]').forEach((input) => input.closest('label').remove());
            payload.tree.causes.forEach((value, index) => {
                const label = document.createElement('label');
                label.innerHTML = `<span class="label">Causa ${index + 1}</span><input class="input" name="ai_tree_cause[]">`;
                label.querySelector('input').value = value; treeGrid.append(label);
            });
            const factors = result.querySelector('[data-ai-factors]');
            factors.replaceChildren(...payload.factors.map((factor) => {
                const label = document.createElement('label');
                label.className = 'flex items-start gap-3 rounded-xl border border-sky-200 bg-white p-4';
                label.innerHTML = '<input type="checkbox" class="mt-1 size-4 accent-blue-800" name="ai_factor_codes[]" checked><span><strong class="block text-blue-950"></strong><span class="mt-1 block text-sm text-slate-700"></span><small class="mt-1 block text-xs text-slate-500"></small></span>';
                label.querySelector('input').value = factor.code;
                label.querySelector('strong').textContent = `${factor.code} · ${factor.category}`;
                label.querySelector('span span').textContent = factor.label;
                label.querySelector('small').textContent = factor.explanation;
                return label;
            }));
            if (! payload.factors.length) {
                const empty = document.createElement('p'); empty.className = 'text-sm text-slate-600';
                empty.textContent = 'El relato no respalda con claridad ningún factor de la matriz disponible.'; factors.append(empty);
            }
            const measures = result.querySelector('[data-ai-measures]');
            measures.replaceChildren(...payload.measures.map((measure) => {
                const row = document.createElement('div');
                row.className = 'grid gap-3 rounded-xl bg-slate-50 p-4 lg:grid-cols-[1fr_11rem_12rem_11rem]'; row.dataset.aiMeasure = '';
                row.innerHTML = '<label><span class="label">Medida</span><textarea class="input" name="ai_measure_description[]" rows="2"></textarea></label><label><span class="label">Categoría</span><input class="input" name="ai_measure_category[]"></label><label><span class="label">Responsable</span><input class="input" name="ai_measure_owner[]"></label><label><span class="label">Plazo</span><input type="date" class="input" name="ai_measure_due[]"></label>';
                row.querySelector('[name="ai_measure_description[]"]').value = measure.description;
                row.querySelector('[name="ai_measure_category[]"]').value = measure.category;
                row.querySelector('[name="ai_measure_owner[]"]').value = measure.responsible;
                row.querySelector('[name="ai_measure_due[]"]').value = measure.due_date || '';
                return row;
            }));
            loading.classList.add('hidden'); result.classList.remove('hidden');
            result.querySelectorAll('input, textarea').forEach((field) => { field.disabled = false; });
            button.disabled = false; button.textContent = 'Volver a analizar'; result.querySelector('input, textarea')?.focus();
        }).catch((exception) => {
            clearInterval(timer);
            loading.classList.add('hidden'); error.textContent = exception.message; error.classList.remove('hidden');
            button.disabled = false; button.textContent = 'Analizar relato final';
        });
    });

    saveButton.addEventListener('click', async () => {
        const factsTarget = investigation.querySelector('[data-derived-facts]');
        const treeTarget = investigation.querySelector('[data-derived-tree]');
        const measuresTarget = investigation.querySelector('[data-derived-measures]');
        factsTarget.replaceChildren();
        treeTarget.replaceChildren();
        measuresTarget.replaceChildren();

        result.querySelectorAll('[name="ai_facts[]"]').forEach((source) => {
            const row = document.createElement('div');
            row.className = 'repeat-row';
            const handle = document.createElement('span');
            handle.className = 'drag-handle';
            handle.textContent = '⋮⋮';
            const input = document.createElement('input');
            input.className = 'input';
            input.name = 'facts[]';
            input.value = source.value;
            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'btn-secondary';
            remove.dataset.removeRow = '';
            remove.textContent = 'Quitar';
            row.append(handle, input, remove);
            factsTarget.append(row);
        });

        const treeValues = {
            final: result.querySelector('[name="ai_tree_final"]').value,
            origin: result.querySelector('[name="ai_tree_origin"]').value,
            causes: [...result.querySelectorAll('[name="ai_tree_cause[]"]')].map((input) => input.value),
        };
        const finalNode = document.createElement('div');
        finalNode.className = 'cause-node cause-final';
        finalNode.textContent = treeValues.final;
        const connector = document.createElement('div');
        connector.className = 'cause-connector';
        connector.textContent = '↑ Y ↑';
        const causes = document.createElement('div');
        causes.className = 'grid gap-4 md:grid-cols-2';
        treeValues.causes.forEach((value) => {
            const node = document.createElement('div');
            node.className = 'cause-node';
            node.textContent = value;
            causes.append(node);
        });
        const originConnector = connector.cloneNode(true);
        originConnector.textContent = '↑';
        const originNode = document.createElement('div');
        originNode.className = 'cause-node mx-auto';
        originNode.textContent = treeValues.origin;
        treeTarget.append(finalNode, connector, causes, originConnector, originNode);

        result.querySelectorAll('[data-ai-measure]').forEach((source) => {
            const row = document.createElement('tr');
            ['ai_measure_description[]', 'ai_measure_category[]', 'ai_measure_owner[]', 'ai_measure_due[]'].forEach((name) => {
                const cell = document.createElement('td');
                cell.textContent = source.querySelector(`[name="${name}"]`).value;
                row.append(cell);
            });
            measuresTarget.append(row);
        });

        const workspace = investigation.closest('[data-demo-workspace]');
        const response = await fetch(workspace.dataset.aiStoreUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: JSON.stringify({
                case_folio: workspace.dataset.caseFolio, story: story.value.trim(), model: result.dataset.model || '',
                facts: [...result.querySelectorAll('[name="ai_facts[]"]')].map((input) => input.value), tree: treeValues,
                factor_codes: [...result.querySelectorAll('[name="ai_factor_codes[]"]:checked')].map((input) => input.value),
                measures: [...result.querySelectorAll('[data-ai-measure]')].map((row) => ({ description: row.querySelector('[name="ai_measure_description[]"]').value, category: row.querySelector('[name="ai_measure_category[]"]').value, responsible: row.querySelector('[name="ai_measure_owner[]"]').value, due_date: row.querySelector('[name="ai_measure_due[]"]').value || null })),
            }),
        });
        const payload = await response.json();
        if (! response.ok) { error.textContent = payload.message || 'No fue posible guardar la propuesta.'; error.classList.remove('hidden'); return; }

        investigation.closest('[data-demo-workspace]').querySelectorAll('[data-ai-derived-nav]').forEach((nav) => nav.classList.remove('hidden'));
        result.querySelector('[data-ai-saved-status]').classList.remove('hidden');
        saveButton.textContent = 'Propuesta guardada';
        investigation.closest('[data-demo-workspace]').querySelector('[data-section-target="inv-4"]').click();
    });
});

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

const investigationForm = document.querySelector('[data-final-story-ai]')?.closest('form');
const linkedCaseId = new URLSearchParams(window.location.search).get('case');
if (investigationForm && linkedCaseId) {
    const casesStorageKey = 'giatep.demo.cases';
    const investigationsStorageKey = 'giatep.demo.investigations';
    const draftStorageKey = `giatep.demo.investigation.${linkedCaseId}`;
    const ignoredNames = new Set(['_token', 'action', 'return_to']);
    const fieldsByName = (name) => [...investigationForm.querySelectorAll(`[name="${CSS.escape(name)}"]`)];
    const setFieldValue = (name, value) => {
        const fields = fieldsByName(name);
        if (! fields.length || value === undefined || value === null) return;
        fields.forEach((field) => {
            if (field.type === 'checkbox' || field.type === 'radio') {
                field.checked = Array.isArray(value) ? value.includes(field.value) : field.value === value;
            } else if (field.type !== 'file') {
                field.value = Array.isArray(value) ? (value[0] || '') : value;
            }
        });
    };
    const readStoredCases = () => {
        try {
            const cases = JSON.parse(localStorage.getItem(casesStorageKey) || '[]');
            return Array.isArray(cases) ? cases : [];
        } catch (error) {
            return [];
        }
    };
    const linkedCase = readStoredCases().find((item) => item.folio === linkedCaseId);
    if (linkedCase) {
        const caseMapping = {
            employer_name: linkedCase.employerName,
            employer_rut: linkedCase.employerRut,
            administrator: linkedCase.administrator,
            economic_activity: linkedCase.economicActivity,
            employer_address: linkedCase.employerAddress,
            worker_name: linkedCase.person,
            worker_rut: linkedCase.workerRut,
            job_title: linkedCase.jobTitle,
            worker_phone: linkedCase.phone,
            event_date: linkedCase.date,
            event_time: linkedCase.time,
            event_location: linkedCase.location,
            event_type: linkedCase.type,
            initial_story: linkedCase.summary,
        };
        Object.entries(caseMapping).forEach(([name, value]) => setFieldValue(name, value));
    }
    try {
        const draft = JSON.parse(localStorage.getItem(draftStorageKey) || '{}');
        Object.entries(draft.fields || {}).forEach(([name, value]) => setFieldValue(name, value));
    } catch (error) {
        localStorage.removeItem(draftStorageKey);
    }
    const serializeInvestigation = () => {
        const fields = {};
        const names = new Set([...investigationForm.elements]
            .filter((field) => field.name && ! ignoredNames.has(field.name) && field.type !== 'file')
            .map((field) => field.name));
        names.forEach((name) => {
            const controls = fieldsByName(name).filter((field) => ! field.disabled);
            const selectable = controls.filter((field) => field.type === 'checkbox' || field.type === 'radio');
            fields[name] = selectable.length
                ? selectable.filter((field) => field.checked).map((field) => field.value)
                : controls[0]?.value || '';
        });
        const evidence = [...investigationForm.querySelectorAll('input[type="file"]')].flatMap((input) => [...input.files].map((file) => ({
            name: file.name,
            type: file.type,
            size: file.size,
        })));
        const draft = { caseId: linkedCaseId, fields, evidence, updatedAt: new Date().toISOString() };
        localStorage.setItem(draftStorageKey, JSON.stringify(draft));
        let investigations = [];
        try {
            const parsed = JSON.parse(localStorage.getItem(investigationsStorageKey) || '[]');
            investigations = Array.isArray(parsed) ? parsed : [];
        } catch (error) {
            investigations = [];
        }
        const relation = investigations.find((item) => item.caseId === linkedCaseId);
        const record = relation || { id: `INV-${linkedCaseId}`, caseId: linkedCaseId, status: 'Borrador' };
        record.caseType = fields.event_type || linkedCase?.type || '';
        record.updatedAt = draft.updatedAt;
        record.hasFinalStory = Boolean(fields.final_story?.trim());
        record.evidenceCount = evidence.length + (linkedCase?.evidenceFiles?.length || 0);
        if (! relation) investigations.unshift(record);
        localStorage.setItem(investigationsStorageKey, JSON.stringify(investigations));
    };
    let investigationSaveTimer;
    const scheduleInvestigationSave = () => {
        window.clearTimeout(investigationSaveTimer);
        investigationSaveTimer = window.setTimeout(serializeInvestigation, 250);
    };
    investigationForm.addEventListener('input', scheduleInvestigationSave);
    investigationForm.addEventListener('change', scheduleInvestigationSave);
    investigationForm.addEventListener('submit', serializeInvestigation);
}
