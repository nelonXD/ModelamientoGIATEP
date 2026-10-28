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

const renderCausalTree = (container, tree) => {
    if (!container || !tree?.nodes?.length) return;
    const svgNs = 'http://www.w3.org/2000/svg';
    const nodes = tree.nodes;
    const nodeById = new Map(nodes.map((node) => [node.id, node]));
    const edges = (tree.edges || []).filter((edge) => nodeById.has(edge.from) && nodeById.has(edge.to) && edge.from !== edge.to);
    const finalId = nodeById.has(tree.final_node_id) ? tree.final_node_id : nodes[nodes.length - 1].id;
    const depth = new Map([[finalId, 0]]);
    const queue = [finalId];
    while (queue.length) {
        const consequence = queue.shift();
        edges.filter((edge) => edge.to === consequence).forEach((edge) => {
            if (!depth.has(edge.from)) {
                depth.set(edge.from, depth.get(consequence) + 1);
                queue.push(edge.from);
            }
        });
    }
    const maxDepth = Math.max(0, ...depth.values());
    const levels = Array.from({ length: maxDepth + 1 }, () => []);
    nodes.forEach((node, index) => {
        if (!depth.has(node.id)) {
            depth.set(node.id, maxDepth + 1);
            levels[maxDepth + 1] ||= [];
        }
        levels[depth.get(node.id)].push({ ...node, order: index });
    });
    const width = Math.max(820, ...levels.map((level) => level.length * 250 + 80));
    const rowHeight = 150;
    const height = 135 + levels.length * rowHeight;
    const svg = document.createElementNS(svgNs, 'svg');
    svg.setAttribute('viewBox', `0 0 ${width} ${height}`);
    svg.setAttribute('role', 'img');
    svg.setAttribute('aria-label', 'Árbol de causas vertical. El hecho final está arriba y los antecedentes abajo.');
    svg.classList.add('causal-graph-svg');
    const defs = document.createElementNS(svgNs, 'defs');
    const marker = document.createElementNS(svgNs, 'marker');
    marker.setAttribute('id', `causal-arrow-${Math.random().toString(36).slice(2)}`);
    marker.setAttribute('markerWidth', '9'); marker.setAttribute('markerHeight', '9');
    marker.setAttribute('refX', '7'); marker.setAttribute('refY', '4.5'); marker.setAttribute('orient', 'auto');
    const arrow = document.createElementNS(svgNs, 'path');
    arrow.setAttribute('d', 'M0 0 L9 4.5 L0 9 Z'); arrow.setAttribute('class', 'causal-arrow-head');
    marker.append(arrow); defs.append(marker); svg.append(defs);
    const markerId = marker.id;
    const amberMarker = marker.cloneNode(true);
    amberMarker.id = `${markerId}-amber`;
    amberMarker.querySelector('path').classList.add('is-apparent');
    defs.append(amberMarker);
    const positions = new Map();
    levels.forEach((level, levelIndex) => level.forEach((node, index) => {
        positions.set(node.id, { x: width * (index + 1) / (level.length + 1), y: 70 + levelIndex * rowHeight });
    }));

    edges.forEach((edge) => {
        const source = positions.get(edge.from);
        const target = positions.get(edge.to);
        if (!source || !target) return;
        const path = document.createElementNS(svgNs, 'path');
        const sourceY = source.y - 43;
        const targetY = target.y + 43;
        path.setAttribute('d', `M ${source.x} ${sourceY} C ${source.x} ${(sourceY + targetY) / 2}, ${target.x} ${(sourceY + targetY) / 2}, ${target.x} ${targetY}`);
        path.setAttribute('marker-end', `url(#${edge.link_type === 'aparente' ? amberMarker.id : markerId})`);
        path.classList.add('causal-graph-edge');
        if (edge.link_type === 'aparente') path.classList.add('is-apparent');
        const title = document.createElementNS(svgNs, 'title');
        title.textContent = `${edge.logic}: ${edge.basis || 'relación causal propuesta'}`;
        path.append(title); svg.append(path);
    });

    const wrap = (value, limit = 25) => {
        const words = String(value || '').split(/\s+/);
        const lines = [];
        let line = '';
        words.forEach((word) => {
            if (line && `${line} ${word}`.length > limit) { lines.push(line); line = word; }
            else line = line ? `${line} ${word}` : word;
        });
        if (line) lines.push(line);
        const visible = lines.slice(0, 3);
        if (lines.length > 3) visible[2] = `${visible[2].replace(/[.,;:]$/, '')}…`;
        return visible;
    };
    levels.forEach((level, levelIndex) => level.forEach((node) => {
        const point = positions.get(node.id);
        const group = document.createElementNS(svgNs, 'g');
        group.classList.add('causal-graph-node', node.type === 'permanente' ? 'is-permanent' : 'is-event');
        if (node.id === finalId) group.classList.add('is-final');
        const shape = document.createElementNS(svgNs, node.type === 'permanente' ? 'rect' : 'ellipse');
        if (node.type === 'permanente') {
            shape.setAttribute('x', point.x - 112); shape.setAttribute('y', point.y - 42);
            shape.setAttribute('width', '224'); shape.setAttribute('height', '84'); shape.setAttribute('rx', '13');
        } else {
            shape.setAttribute('cx', point.x); shape.setAttribute('cy', point.y);
            shape.setAttribute('rx', '112'); shape.setAttribute('ry', '42');
        }
        group.append(shape);
        const label = document.createElementNS(svgNs, 'text');
        label.setAttribute('x', point.x); label.setAttribute('y', point.y - (wrap(node.description).length - 1) * 7);
        label.classList.add('causal-graph-label');
        wrap(node.description).forEach((line, lineIndex) => {
            const span = document.createElementNS(svgNs, 'tspan');
            span.setAttribute('x', point.x); span.setAttribute('dy', lineIndex === 0 ? '0' : '15'); span.textContent = line;
            label.append(span);
        });
        const kind = document.createElementNS(svgNs, 'text');
        kind.setAttribute('x', point.x); kind.setAttribute('y', point.y + 31);
        kind.classList.add('causal-graph-kind'); kind.textContent = `${node.id} · ${node.type === 'permanente' ? 'PERMANENTE' : (node.id === finalId ? 'HECHO FINAL' : 'HECHO')}`;
        group.append(label, kind);
        const title = document.createElementNS(svgNs, 'title'); title.textContent = node.description; group.append(title);
        svg.append(group);
    }));
    const details = document.createElement('details');
    details.className = 'mt-4 rounded-xl border border-slate-200 bg-white p-4 text-xs text-slate-700';
    const summary = document.createElement('summary'); summary.className = 'cursor-pointer font-semibold'; summary.textContent = 'Leer hechos y fundamento de las conexiones';
    details.append(summary);
    const descriptions = document.createElement('ul'); descriptions.className = 'mt-3 space-y-2';
    nodes.forEach((node) => {
        const row = document.createElement('li'); row.textContent = `${node.id}: ${node.description}`; descriptions.append(row);
    });
    details.append(descriptions);
    const relationships = document.createElement('ul'); relationships.className = 'mt-4 space-y-2 border-t border-slate-200 pt-3';
    const logicLabels = { cadena: 'Cadena', conjuncion: 'Conjunción', disyuncion: 'Disyunción' };
    edges.forEach((edge) => {
        const row = document.createElement('li'); row.textContent = `${edge.from} → ${edge.to} · ${logicLabels[edge.logic] || 'Vínculo'}${edge.link_type === 'aparente' ? ' · aparente, por verificar' : ''}: ${edge.basis || 'Sin fundamento registrado'}`; relationships.append(row);
    });
    details.append(relationships);
    container.replaceChildren(svg, details);
};

document.querySelectorAll('[data-tree-graph-json]').forEach((container) => {
    try { renderCausalTree(container, JSON.parse(container.dataset.treeGraphJson)); }
    catch { container.textContent = 'No se pudo cargar la visualización guardada del árbol.'; }
});

const loginForm = document.querySelector('[data-login-form]');

if (loginForm) {
    const rutInput = loginForm.querySelector('[data-login-rut]');
    const rutStep = loginForm.querySelector('[data-login-rut-step]');
    const continueButton = loginForm.querySelector('[data-login-continue]');
    const passwordStep = loginForm.querySelector('[data-login-password-step]');
    const passwordInput = loginForm.querySelector('#password');
    const firstAccessSection = loginForm.querySelector('[data-first-access]');
    const existingLoginSection = loginForm.querySelector('[data-existing-login]');
    const submitButton = loginForm.querySelector('[data-submit-button]');
    const firstAccessProfile = loginForm.querySelector('[data-first-access-profile]');
    const changeRutButton = loginForm.querySelector('[data-login-change-rut]');
    const rutSummary = loginForm.querySelector('[data-login-rut-summary]');
    const rutError = loginForm.querySelector('[data-rut-error]');
    const loginUrl = loginForm.action;

    const setAccountMode = (profile = null, setupUrl = null) => {
        const needsSetup = Boolean(profile && setupUrl);
        existingLoginSection.classList.toggle('hidden', needsSetup);
        firstAccessSection.classList.toggle('hidden', ! needsSetup);
        passwordInput.disabled = needsSetup;
        passwordInput.required = ! needsSetup;
        firstAccessSection.querySelectorAll('input').forEach((field) => {
            field.disabled = ! needsSetup;
            field.required = needsSetup;
        });
        loginForm.action = needsSetup ? setupUrl : loginUrl;
        submitButton.textContent = needsSetup ? 'Crear cuenta e ingresar' : 'Ingresar';

        if (needsSetup) {
            rutSummary.textContent = `${rutInput.value} · ${profile.name}`;
            firstAccessProfile.replaceChildren();
            [
                ['Cargo', profile.job_title],
                ['Rol GIATEP', profile.role_name],
                ['Dependencia', profile.dependency_name],
            ].forEach(([label, value]) => {
                const term = document.createElement('dt');
                term.className = 'font-semibold text-slate-600';
                term.textContent = label;
                const description = document.createElement('dd');
                description.className = 'font-bold text-slate-950';
                description.textContent = value || '—';
                firstAccessProfile.append(term, description);
            });
        } else {
            rutSummary.textContent = rutInput.value;
        }
    };

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

            setAccountMode(result.requires_setup ? result.profile : null, result.setup_url || null);
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
        setAccountMode();
        passwordInput.value = '';
        firstAccessSection.querySelectorAll('input').forEach((field) => {
            field.value = '';
        });
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

document.querySelectorAll('[data-interview-form]').forEach((form) => {
    const witnessType = form.querySelector('[data-witness-type]');
    if (! witnessType) return;

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
        if (! field) return;
        const input = field.querySelector('textarea, input');
        row.querySelectorAll('input[type="radio"]').forEach((radio) => radio.addEventListener('change', () => {
            const show = radio.checked && radio.value === row.dataset.detailWhen;
            field.classList.toggle('hidden', ! show);
            if (input) input.disabled = ! show;
            if (! show && input) input.value = '';
        }));
    });

    form.querySelectorAll('[data-toggle-other]').forEach((checkbox) => checkbox.addEventListener('change', () => {
        const field = form.querySelector(`[data-other-field="${checkbox.dataset.toggleOther}"]`);
        if (! field) return;
        const input = field.querySelector('input, textarea');
        field.classList.toggle('hidden', ! checkbox.checked);
        if (input) input.disabled = ! checkbox.checked;
        if (! checkbox.checked && input) input.value = '';
    }));

    form.querySelectorAll('[data-development-question]').forEach((question) => {
        const notApplicable = question.querySelector('[data-development-na]');
        const answer = question.querySelector('textarea');
        if (! notApplicable || ! answer) return;
        notApplicable.addEventListener('change', () => {
            answer.disabled = notApplicable.checked;
            answer.classList.toggle('hidden', notApplicable.checked);
            if (notApplicable.checked) answer.value = '';
        });
    });

    form.querySelectorAll('[data-yes-options]').forEach((control) => {
        const panel = control.querySelector('[data-yes-options-panel]');
        if (! panel) return;
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
        if (! editor || ! list) return;
        editor.dataset.interviewMode = mode;
        const useLegacy = mode === 'legacy';
        if (legacyQuestionnaire) {
            legacyQuestionnaire.classList.toggle('hidden', ! useLegacy);
            legacyQuestionnaire.disabled = ! useLegacy;
            legacyQuestionnaire.setAttribute('aria-hidden', useLegacy ? 'false' : 'true');
        }
        if (adaptedQuestionnaire) {
            adaptedQuestionnaire.classList.toggle('hidden', useLegacy);
            adaptedQuestionnaire.querySelectorAll('input, textarea, select').forEach((field) => { field.disabled = useLegacy; });
        }
        if (! useLegacy) updateInterviewFlow();
        if (formTitle) {
            formTitle.textContent = useLegacy ? 'Formulario de entrevista anterior' : 'Formulario de entrevista';
        }
        list.classList.add('hidden');
        editor.classList.remove('hidden');
    };
    const showList = () => {
        editor?.classList.add('hidden');
        list?.classList.remove('hidden');
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
        shell.dispatchEvent(new CustomEvent('giatep:interview-saved'));
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
    const stageButtons = [...section.querySelectorAll('[data-ai-stage]')];
    const loading = section.querySelector('[data-final-ai-loading]');
    const error = section.querySelector('[data-final-ai-error]');
    const result = section.querySelector('[data-final-ai-result]');
    const story = section.querySelector('[name="final_story"]');
    const draftButton = section.querySelector('[data-generate-final-story]');
    const draftLoading = section.querySelector('[data-story-generate-loading]');
    const storySaveButton = section.querySelector('[data-save-final-story]');
    const storySaved = section.querySelector('[data-final-story-saved]');
    const saveButton = result.querySelector('[data-save-ai-proposal]');
    const modelSelector = section.querySelector('[data-ai-model]');
    const modelStatus = section.querySelector('[data-ai-model-status]');
    const modelRefresh = section.querySelector('[data-ai-model-refresh]');
    const providerButtons = [...section.querySelectorAll('[data-ai-provider]')];
    const providerLabel = section.querySelector('[data-ai-provider-label]');
    const providerLocation = section.querySelector('[data-ai-provider-location]');
    const investigation = section.closest('form');
    let reviewedFacts = [];
    let causalTree = null;
    let measuresReady = false;
    let busy = false;
    let inputRevision = 0;
    let modelUnavailable = true;
    let loadingModels = false;
    let selectedProvider = 'lm_studio';
    let groqAvailable = false;
    let groqModel = 'openai/gpt-oss-120b';
    let proposalMetadata = {};
    const changeNotice = section.querySelector('[data-ai-change-notice]');
    const requestHeaders = { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content };
    const showError = (message) => {
        error.textContent = message;
        error.classList.remove('hidden');
    };
    const showChangeNotice = (message) => {
        changeNotice.textContent = message;
        changeNotice.classList.toggle('hidden', ! message);
    };
    const postJson = async (url, data, fallbackMessage) => {
        const response = await fetch(url, { method: 'POST', headers: requestHeaders, body: JSON.stringify(data) });
        const payload = await response.json().catch(() => null);
        if (response.status === 419) throw new Error('La sesión venció. Copie los cambios sin guardar y vuelva a ingresar antes de intentarlo otra vez.');
        if (! response.ok || ! payload) {
            const details = Object.values(payload?.errors || {}).flat().filter((item) => typeof item === 'string');
            throw new Error(details[0] || payload?.message || fallbackMessage);
        }
        return payload;
    };
    const refreshControls = () => {
        draftButton.disabled = busy || loadingModels || modelUnavailable;
        storySaveButton.disabled = busy;
        modelSelector.disabled = busy || loadingModels || modelUnavailable || selectedProvider === 'groq';
        modelRefresh.disabled = busy || loadingModels;
        providerButtons.forEach((button) => {
            button.disabled = busy || loadingModels || (button.dataset.aiProvider === 'groq' && ! groqAvailable);
            button.setAttribute('aria-pressed', String(button.dataset.aiProvider === selectedProvider));
        });
        stageButtons.forEach((button) => {
            const stage = button.dataset.aiStage;
            const providerUnavailable = selectedProvider === 'groq' ? ! groqAvailable : modelUnavailable;
            button.disabled = busy || loadingModels || providerUnavailable || (stage !== 'facts' && ! factsPayload().length) || (stage === 'measures' && ! causalTree);
        });
        saveButton.disabled = busy || ! measuresReady;
        result.querySelectorAll('input, textarea, select, [data-ai-fact] button').forEach((control) => { control.disabled = busy; });
        investigation.querySelectorAll('[data-save-interview]').forEach((button) => { button.disabled = busy; });
        section.setAttribute('aria-busy', String(busy));
    };
    const renderMetadata = (stage, payload) => {
        if (stage !== 'story') proposalMetadata[stage] = {
            model: payload.model || '', prompt_version: payload.prompt_version || '',
            quality_warnings: (Array.isArray(payload.quality_warnings) ? payload.quality_warnings : []).map((warning) => typeof warning === 'string' ? warning : warning?.message).filter(Boolean),
        };
        const target = section.querySelector(`[data-ai-metadata="${stage}"]`);
        if (! target) return;
        target.replaceChildren();
        const summary = document.createElement('p');
        summary.className = 'font-semibold';
        summary.textContent = [payload.model ? `Modelo: ${payload.model}` : '', payload.prompt_version ? `Versión: ${payload.prompt_version}` : ''].filter(Boolean).join(' · ');
        if (summary.textContent) target.append(summary);
        const warnings = [...(Array.isArray(payload.quality_warnings) ? payload.quality_warnings : []), ...(Array.isArray(payload.quality_adjustments) ? payload.quality_adjustments : [])];
        if (warnings.length) {
            const list = document.createElement('ul'); list.className = 'mt-2 list-disc space-y-1 pl-5 text-amber-900';
            warnings.forEach((warning) => {
                const text = typeof warning === 'string' ? warning : warning?.message;
                if (! text) return;
                const row = document.createElement('li'); row.textContent = text; list.append(row);
            });
            if (list.childElementCount) target.append(list);
        }
        const references = Array.isArray(payload.source_references) ? payload.source_references : [];
        if (references.length) {
            const details = document.createElement('details'); details.className = 'mt-2';
            const title = document.createElement('summary'); title.className = 'cursor-pointer font-semibold'; title.textContent = 'Fuentes utilizadas por párrafo'; details.append(title);
            const catalog = new Map((payload.source_catalog || []).map((source) => [source.id, source.label || source.id]));
            references.forEach((reference, index) => {
                const row = document.createElement('p'); row.className = 'mt-2';
                row.textContent = `Párrafo ${index + 1}: ${(reference.source_ids || []).map((id) => `${id} · ${catalog.get(id) || 'fuente registrada'}`).join('; ')}`;
                details.append(row);
            });
            target.append(details);
        }
        target.classList.toggle('hidden', target.childElementCount === 0);
    };

    const loadAvailableModels = async () => {
        if (! modelSelector) return;
        const workspace = section.closest('[data-demo-workspace]');
        const response = await fetch(workspace.dataset.aiModelsUrl, { headers: { 'Accept': 'application/json' } });
        if (! response.ok) throw new Error('No se pudo consultar la configuración de los proveedores IA.');
        const payload = await response.json();
        groqAvailable = Boolean(payload.groq_available);
        groqModel = payload.groq_model || 'openai/gpt-oss-120b';
        let savedModel = '';
        try { savedModel = localStorage.getItem('giatep.ai.investigation.model') || ''; } catch { /* El almacenamiento del navegador puede estar deshabilitado. */ }
        const preferredModel = savedModel || payload.configured_model || '';
        const models = [...(payload.models || [])];
        if (payload.configured_model && ! models.some((model) => model.id === payload.configured_model)) {
            models.unshift({ id: payload.configured_model, name: `${payload.configured_model} (configurado)` });
        }
        modelSelector.replaceChildren();
        models.forEach((model) => {
            const option = document.createElement('option');
            option.value = model.id;
            option.textContent = model.name || model.id;
            modelSelector.append(option);
        });
        if (! models.length) {
            const option = document.createElement('option');
            option.value = '';
            option.textContent = 'No se encontraron modelos';
            modelSelector.append(option);
            modelUnavailable = true;
            refreshControls();
            modelStatus.textContent = payload.connected ? 'No hay modelos disponibles para la API de LM Studio.' : 'No se pudo conectar con LM Studio. Inícialo y recarga esta página.';
            if (! groqAvailable) modelStatus.textContent += ' Groq no está disponible: configura GROQ_API_KEY en el servidor.';
            return;
        }
        modelUnavailable = false;
        modelSelector.value = models.some((model) => model.id === preferredModel) ? preferredModel : models[0].id;
        modelStatus.textContent = payload.connected ? 'Selecciona un modelo disponible; si uno descargado no aparece, activa la carga dinámica (JIT) en LM Studio. La elección se recordará en este navegador.' : 'Se usa el modelo configurado; no se pudo consultar LM Studio.';
        if (! groqAvailable) modelStatus.textContent += ' Groq no está disponible: configura GROQ_API_KEY en el servidor.';
        refreshControls();
    };
    const refreshModels = async () => {
        if (busy || loadingModels) return;
        loadingModels = true;
        refreshControls();
        const previousModel = modelSelector.value;
        try {
            await loadAvailableModels();
            if (previousModel && modelSelector.value !== previousModel) modelSelector.dispatchEvent(new Event('change'));
        } catch {
            modelStatus.textContent = 'No se pudo consultar LM Studio. Revise que el servidor local esté iniciado y pulse «Actualizar modelos disponibles».';
        } finally {
            loadingModels = false;
            refreshControls();
        }
    };
    modelRefresh.addEventListener('click', refreshModels);
    modelSelector?.addEventListener('change', () => {
        try { localStorage.setItem('giatep.ai.investigation.model', modelSelector.value); } catch { /* La selección sigue vigente en esta página. */ }
        inputRevision++;
        resetLaterStages();
        showChangeNotice('Modelo cambiado. Genere nuevamente los hechos, el árbol y las medidas con el modelo seleccionado. El relato se conservó.');
    });
    providerButtons.forEach((button) => button.addEventListener('click', () => {
        const nextProvider = button.dataset.aiProvider;
        if (busy || nextProvider === selectedProvider || (nextProvider === 'groq' && ! groqAvailable)) return;
        selectedProvider = nextProvider;
        inputRevision++;
        resetLaterStages();
        const usingGroq = selectedProvider === 'groq';
        providerLabel.textContent = usingGroq ? 'Groq · GPT-OSS 120B' : 'LM Studio';
        providerLocation.textContent = usingGroq ? 'API externa; solo texto, sin archivos adjuntos' : 'Procesamiento en el servidor local';
        showChangeNotice(`Motor cambiado a ${usingGroq ? `Groq (${groqModel})` : 'LM Studio'}. Genere nuevamente los hechos, el árbol y las medidas.`);
        refreshControls();
    }));

    const saveFinalStory = async (value = story.value.trim()) => {
        if (! value) throw new Error('Escriba el relato final antes de guardarlo.');
        const workspace = section.closest('[data-demo-workspace]');
        const payload = await postJson(workspace.dataset.storyStoreUrl, { case_folio: workspace.dataset.caseFolio, story: value }, 'No fue posible guardar el relato final.');
        if (story.value.trim() === value) storySaved.classList.remove('hidden');
        return payload;
    };

    draftButton.addEventListener('click', async () => {
        if (busy) return;
        busy = true;
        const revision = inputRevision;
        const selectedModel = modelSelector?.value || '';
        refreshControls();
        const workspace = section.closest('[data-demo-workspace]');
        error.classList.add('hidden');
        draftLoading.textContent = 'Generando un nuevo borrador con LM Studio…';
        draftLoading.classList.remove('hidden');
        draftButton.disabled = true;
        draftButton.textContent = 'Generando borrador…';
        const startedAt = Date.now();
        const timer = setInterval(() => {
            draftLoading.textContent = `Generando un nuevo borrador con LM Studio… (${Math.floor((Date.now() - startedAt) / 1000)}s)`;
        }, 1000);

        try {
            const payload = await postJson(workspace.dataset.storyGenerateUrl, { case_folio: workspace.dataset.caseFolio, model: selectedModel }, 'No fue posible generar el borrador.');
            if (inputRevision !== revision) {
                draftLoading.classList.add('hidden');
                showChangeNotice('El relato se editó durante la generación. Se conservaron sus cambios; genere un nuevo borrador si desea reemplazarlos.');
                return;
            }
            if (typeof payload.story !== 'string' || ! payload.story.trim()) {
                throw new Error('LM Studio respondió sin contenido. El relato anterior se conservó; vuelve a intentarlo.');
            }

            story.value = payload.story.trim();
            story.dispatchEvent(new Event('input', { bubbles: true }));
            renderMetadata('story', payload);
            story.focus();
            draftLoading.textContent = `Borrador generado con ${payload.model}${payload.interviews_used ? `; se consideraron ${payload.interviews_used} entrevistas guardadas` : '; no hay entrevistas guardadas para este caso'}. Revísalo antes de continuar.`;
            storySaved.classList.add('hidden');
        } catch (exception) {
            draftLoading.classList.add('hidden');
            showError(`${exception.message} El relato anterior se conservó.`);
        } finally {
            clearInterval(timer);
            busy = false;
            refreshControls();
            draftButton.textContent = 'Generar borrador con modelo seleccionado';
        }
    });

    storySaveButton.addEventListener('click', async () => {
        if (busy) return;
        busy = true;
        refreshControls();
        error.classList.add('hidden');
        storySaveButton.disabled = true;
        try {
            await saveFinalStory();
        } catch (exception) {
            error.textContent = exception.message;
            error.classList.remove('hidden');
        } finally {
            busy = false;
            refreshControls();
        }
    });

    const factsPayload = () => [...result.querySelectorAll('[name="ai_facts[]"]')].map((input) => ({
        description: input.value.trim(), classification: input.closest('[data-ai-fact]')?.dataset.classification || 'hecho',
        evidence_status: input.closest('[data-ai-fact]')?.dataset.evidenceStatus || 'no_verificado',
        evidence: input.closest('[data-ai-fact]')?.dataset.evidence || '',
        source: input.closest('[data-ai-fact]')?.dataset.source || 'Fuente no identificada',
        source_ids: JSON.parse(input.closest('[data-ai-fact]')?.dataset.sourceIds || '[]'),
    })).filter((fact) => fact.description);

    const clearSavedStatus = () => {
        result.querySelector('[data-ai-saved-status]').classList.add('hidden');
        saveButton.textContent = 'Guardar propuesta';
    };
    const invalidateTree = () => {
        causalTree = null;
        measuresReady = false;
        delete proposalMetadata.tree;
        delete proposalMetadata.measures;
        result.dataset.treeGraph = '';
        result.querySelector('[data-ai-stage-panel="tree"]').classList.add('hidden');
        result.querySelector('[data-ai-stage-panel="measures"]').classList.add('hidden');
        clearSavedStatus();
        refreshControls();
    };
    const resetLaterStages = () => {
        reviewedFacts = []; causalTree = null;
        measuresReady = false;
        proposalMetadata = {};
        result.classList.add('hidden');
        result.querySelector('[data-ai-stage-panel="tree"]').classList.add('hidden');
        result.querySelector('[data-ai-stage-panel="measures"]').classList.add('hidden');
        result.querySelector('[data-ai-facts]').replaceChildren();
        result.querySelector('[data-ai-pending-list]')?.replaceChildren();
        result.querySelector('[data-ai-pending-verifications]')?.classList.add('hidden');
        result.querySelector('[data-ai-tree-diagram]').replaceChildren();
        result.querySelector('[data-ai-factors]').replaceChildren();
        result.querySelector('[data-ai-measures]').replaceChildren();
        result.dataset.treeGraph = '';
        section.querySelectorAll('[data-ai-metadata]:not([data-ai-metadata="story"])').forEach((target) => { target.replaceChildren(); target.classList.add('hidden'); });
        clearSavedStatus();
        refreshControls();
    };

    story.addEventListener('input', () => {
        storySaved.classList.add('hidden');
        inputRevision++;
        const hadResults = reviewedFacts.length > 0 || causalTree;
        resetLaterStages();
        section.querySelector('[data-ai-metadata="story"]').classList.add('hidden');
        if (hadResults) showChangeNotice('El relato cambió. Genere otra vez los hechos, el árbol y las medidas para analizar el texto actualizado.');
    });

    result.querySelector('[data-ai-facts]').addEventListener('input', (event) => {
        const input = event.target.closest('[name="ai_facts[]"], [data-ai-fact-classification]');
        if (! input) return;
        const wrapper = input.closest('[data-ai-fact]');
        inputRevision++;
        if (input.matches('[data-ai-fact-classification]')) {
            wrapper.dataset.classification = input.value;
        } else {
            wrapper.dataset.evidenceStatus = 'no_verificado';
            wrapper.dataset.source = 'Editado por investigador; revisar respaldo';
            wrapper.dataset.sourceIds = '[]';
            wrapper.dataset.evidence = '';
            wrapper.querySelector('[data-fact-evidence]')?.remove();
        }
        const caption = wrapper.querySelector('.label');
        const index = [...wrapper.parentElement.children].indexOf(wrapper) + 1;
        caption.textContent = `${wrapper.dataset.classification === 'permanente' ? 'Condición permanente' : 'Hecho puntual'} ${index} · ${wrapper.dataset.evidenceStatus === 'no_verificado' ? 'pendiente de verificación' : wrapper.dataset.evidenceStatus} · fuente: ${wrapper.dataset.source}`;
        invalidateTree();
        showChangeNotice('Los hechos cambiaron. Revise su respaldo y genere nuevamente el árbol y las medidas.');
    });

    const renderFacts = (items) => {
        const facts = result.querySelector('[data-ai-facts]');
        facts.replaceChildren(...items.map((item, index) => {
            const wrapper = document.createElement('div');
            wrapper.className = 'rounded-xl border border-slate-200 bg-white p-4';
            wrapper.dataset.aiFact = '';
            wrapper.dataset.classification = item.classification || 'hecho';
            wrapper.dataset.evidenceStatus = item.evidence_status || 'no_verificado';
            wrapper.dataset.evidence = item.evidence || '';
            wrapper.dataset.source = item.source || 'Fuente no identificada';
            wrapper.dataset.sourceIds = JSON.stringify(item.source_ids || []);
            const header = document.createElement('div'); header.className = 'mb-2 flex items-center justify-between gap-3';
            const caption = document.createElement('span'); caption.className = 'label';
            const statusLabels = { verificado: 'verificado', declarado: 'declarado · sin corroboración independiente', no_verificado: 'pendiente de verificación' };
            caption.textContent = `${wrapper.dataset.classification === 'permanente' ? 'Condición permanente' : 'Hecho puntual'} ${index + 1} · ${statusLabels[wrapper.dataset.evidenceStatus] || 'pendiente de verificación'} · fuente: ${wrapper.dataset.source}`;
            const remove = document.createElement('button'); remove.type = 'button'; remove.className = 'btn-secondary shrink-0 px-2 py-1 text-xs'; remove.textContent = 'Quitar';
            remove.addEventListener('click', () => {
                wrapper.remove();
                inputRevision++;
                [...facts.querySelectorAll('[data-ai-fact] .label')].forEach((label, factIndex) => {
                    const item = label.closest('[data-ai-fact]');
                    label.textContent = `${item.dataset.classification === 'permanente' ? 'Condición permanente' : 'Hecho puntual'} ${factIndex + 1} · ${statusLabels[item.dataset.evidenceStatus] || 'pendiente de verificación'} · fuente: ${item.dataset.source}`;
                });
                invalidateTree();
                showChangeNotice('Se quitó un hecho. Genere nuevamente el árbol y las medidas con los hechos restantes.');
            });
            header.append(caption, remove);
            const label = document.createElement('label'); label.className = 'block';
            const input = document.createElement('textarea'); input.className = 'input leading-relaxed'; input.name = 'ai_facts[]'; input.value = item.description; input.rows = 2;
            input.setAttribute('aria-label', `Descripción del hecho ${index + 1}`);
            label.append(input); wrapper.append(header, label);
            const classification = document.createElement('select'); classification.className = 'input mt-2 max-w-xs text-xs'; classification.dataset.aiFactClassification = '';
            classification.setAttribute('aria-label', `Tipo del hecho ${index + 1}`);
            [['hecho', 'Hecho puntual'], ['permanente', 'Condición permanente']].forEach(([value, text]) => {
                const option = document.createElement('option'); option.value = value; option.textContent = text; classification.append(option);
            });
            classification.value = wrapper.dataset.classification;
            wrapper.append(classification);
            if (item.evidence) {
                const evidence = document.createElement('p'); evidence.className = 'text-xs text-slate-600';
                evidence.dataset.factEvidence = '';
                evidence.textContent = `Fragmento literal: “${item.evidence}”`; wrapper.append(evidence);
            }
            return wrapper;
        }));
        if (items.length === 0) {
            const empty = document.createElement('p'); empty.className = 'rounded-xl border border-dashed border-amber-300 bg-amber-50 p-4 text-xs text-amber-900';
            empty.textContent = 'No se identificaron hechos utilizables con el relato entregado. Revise las verificaciones pendientes y agregue antecedentes antes de construir el árbol.';
            facts.append(empty);
        }
    };

    const renderPendingVerifications = (items = []) => {
        const section = result.querySelector('[data-ai-pending-verifications]');
        const list = result.querySelector('[data-ai-pending-list]');
        list.replaceChildren(...items.map((item) => {
            const row = document.createElement('li'); row.textContent = item; return row;
        }));
        section.classList.toggle('hidden', items.length === 0);
    };

    const renderMeasures = (payload) => {
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
            row.innerHTML = '<label><span class="label">Medida</span><textarea class="input" name="ai_measure_description[]" rows="2"></textarea></label><label><span class="label">Categoría</span><input class="input" name="ai_measure_category[]"></label><label><span class="label">Responsable</span><input class="input" name="ai_measure_owner[]"></label><label><span class="label">Plazo</span><input type="date" class="input" name="ai_measure_due[]"></label><label class="lg:col-span-2"><span class="label">Criterio de cobertura y estabilidad</span><textarea class="input" name="ai_measure_criterion[]" rows="2"></textarea></label><label class="lg:col-span-2"><span class="label">Causas abordadas (IDs de nodo)</span><input class="input" name="ai_measure_nodes[]"></label>';
            row.querySelector('[name="ai_measure_description[]"]').value = measure.description;
            row.querySelector('[name="ai_measure_category[]"]').value = measure.category;
            row.querySelector('[name="ai_measure_owner[]"]').value = measure.responsible;
            row.querySelector('[name="ai_measure_due[]"]').value = measure.due_date || '';
            row.querySelector('[name="ai_measure_criterion[]"]').value = measure.criterion || '';
            row.querySelector('[name="ai_measure_nodes[]"]').value = (measure.related_node_ids || []).join(', ');
            return row;
        }));
        if (! payload.measures.length) {
            const empty = document.createElement('p');
            empty.className = 'text-sm text-slate-600';
            empty.textContent = 'No se proponen medidas todavía: el análisis no encontró factores causales respaldados que permitan recomendar una acción específica. Revise las verificaciones pendientes y complete la investigación antes de definir medidas.';
            measures.append(empty);
        }
    };

    const runStage = async (stage) => {
        if (busy) return;
        if (! story.value.trim()) { error.textContent = 'Escriba el relato final antes de iniciar el análisis.'; error.classList.remove('hidden'); story.focus(); return; }
        if (stage !== 'facts' && ! factsPayload().length) { error.textContent = 'Genere y revise primero los hechos.'; error.classList.remove('hidden'); return; }
        if (stage === 'measures' && ! causalTree) { error.textContent = 'Genere y revise primero el árbol.'; error.classList.remove('hidden'); return; }

        const revision = inputRevision;
        const storySnapshot = story.value.trim();
        const facts = factsPayload();
        const selectedModel = modelSelector?.value || '';
        const body = { case_folio: section.closest('[data-demo-workspace]').dataset.caseFolio, story: storySnapshot, model: selectedProvider === 'groq' ? '' : selectedModel, provider: selectedProvider, ...(stage !== 'facts' ? { facts } : {}), ...(stage === 'measures' ? { tree: structuredClone(causalTree) } : {}) };
        const assertInputsCurrent = () => {
            if (inputRevision !== revision) throw new Error('Los datos se editaron mientras se procesaba la etapa. El resultado recibido se descartó; vuelva a generar con los datos actuales.');
        };
        busy = true;
        refreshControls();

        error.classList.add('hidden');
        const workspace = section.closest('[data-demo-workspace]');
        const activeButton = stageButtons.find((item) => item.dataset.aiStage === stage);
        const originalLabel = activeButton.textContent;
        activeButton.textContent = 'Procesando…';
        loading.classList.remove('hidden');
        let elapsedSeconds = 0;
        let loadingStatus = `Generando ${stage === 'facts' ? 'hechos' : stage === 'tree' ? 'árbol' : 'medidas'} con ${selectedProvider === 'groq' ? 'Groq' : 'LM Studio'}…`;
        loading.textContent = `${loadingStatus} (0s)`;
        const timer = setInterval(() => {
            elapsedSeconds++;
            loading.textContent = `${loadingStatus} (${elapsedSeconds}s)`;
        }, 1000);
        try {
            await saveFinalStory(storySnapshot);
            assertInputsCurrent();
            let payload;
            if (stage === 'measures') {
                const candidateCodes = [];
                let batchIndex = 0;
                let totalBatches = 1;
                while (batchIndex < totalBatches) {
                    loadingStatus = `Revisando matriz causal: lote ${batchIndex + 1}${totalBatches > 1 ? `/${totalBatches}` : ''}`;
                    const batch = await postJson(workspace.dataset.aiFactorBatchUrl, { ...body, batch_index: batchIndex }, 'No fue posible revisar este lote de factores.');
                    assertInputsCurrent();
                    candidateCodes.push(...(batch.candidate_codes || []));
                    if (! Number.isInteger(batch.total_batches) || batch.total_batches < 1 || batch.total_batches > 21) throw new Error('La revisión de la matriz respondió con un número de lotes inválido.');
                    totalBatches = batch.total_batches;
                    batchIndex++;
                }
                loadingStatus = 'Generando medidas a partir de los factores pertinentes';
                payload = await postJson(workspace.dataset.aiMeasuresUrl, { ...body, candidate_codes: [...new Set(candidateCodes)].slice(0, 64) }, 'No fue posible generar las medidas.');
            } else {
                payload = await postJson(workspace.dataset[`ai${stage[0].toUpperCase()}${stage.slice(1)}Url`], body, `No fue posible generar la etapa de ${stage}.`);
            }
            assertInputsCurrent();
            result.dataset.model = payload.model || result.dataset.model || '';

            if (stage === 'facts') {
                if (! Array.isArray(payload.facts)) throw new Error('La respuesta no contiene una lista de hechos utilizable.');
                resetLaterStages();
                reviewedFacts = payload.facts;
                renderFacts(reviewedFacts);
                renderPendingVerifications(payload.pending_verifications || []);
                result.classList.remove('hidden');
                stageButtons.find((item) => item.dataset.aiStage === 'tree').disabled = reviewedFacts.length === 0;
                if (payload.guide_reference) {
                    const pageLabel = (payload.guide_reference.pages || []).map((page) => `p. ${page}`).join(', ');
                    const guideReference = result.querySelector('[data-ai-guide-reference]');
                    guideReference.textContent = `Referencia local: ${payload.guide_reference.title} (${payload.guide_reference.version}), páginas ${pageLabel || 'no disponibles'}.`;
                    guideReference.classList.remove('hidden');
                }
            } else if (stage === 'tree') {
                if (! payload.tree || ! Array.isArray(payload.tree.nodes) || ! Array.isArray(payload.tree.edges) || ! payload.tree.nodes.some((node) => node.id === payload.tree.final_node_id)) throw new Error('La respuesta no contiene un árbol con un hecho final válido.');
                invalidateTree();
                causalTree = payload.tree;
                result.dataset.treeGraph = JSON.stringify(causalTree);
                renderCausalTree(result.querySelector('[data-ai-tree-diagram]'), causalTree);
                const treeNote = result.querySelector('[data-ai-tree-note]');
                const excludedCount = (causalTree.excluded_node_ids || []).length;
                treeNote.textContent = excludedCount ? `Se omitieron ${excludedCount} nodo(s) sin un vínculo que los conecte con el hecho final; no se inventaron relaciones para incorporarlos.` : '';
                treeNote.classList.toggle('hidden', excludedCount === 0);
                result.querySelector('[data-ai-stage-panel="tree"]').classList.remove('hidden');
            } else {
                if (! Array.isArray(payload.factors) || ! Array.isArray(payload.measures)) throw new Error('La respuesta no contiene factores y medidas utilizables.');
                renderMeasures(payload);
                result.querySelector('[data-ai-stage-panel="measures"]').classList.remove('hidden');
                measuresReady = true;
                clearSavedStatus();
            }
            renderMetadata(stage, payload);
            showChangeNotice('');
        } catch (exception) {
            showError(`${exception.message}${inputRevision === revision ? ' Los resultados anteriores se conservaron.' : ''}`);
        } finally {
            clearInterval(timer);
            loading.classList.add('hidden');
            activeButton.textContent = originalLabel;
            busy = false;
            refreshControls();
        }
    };

    stageButtons.forEach((stageButton) => stageButton.addEventListener('click', () => runStage(stageButton.dataset.aiStage)));

    saveButton.addEventListener('click', async () => {
        if (busy || ! measuresReady || ! causalTree) return;
        busy = true;
        refreshControls();
        error.classList.add('hidden');
        const originalLabel = saveButton.textContent;
        saveButton.textContent = 'Guardando propuesta…';
        const revision = inputRevision;
        const graph = structuredClone(causalTree);
        const nodesById = new Map((graph.nodes || []).map((node) => [node.id, node]));
        const finalNode = nodesById.get(graph.final_node_id);
        const antecedents = new Set((graph.edges || []).map((edge) => edge.from));
        const origins = (graph.nodes || []).filter((node) => antecedents.has(node.id) && node.id !== finalNode?.id);
        const treeValues = {
            final: finalNode?.description || '',
            origin: origins.map((node) => node.description).join('; ') || finalNode?.description || '',
            causes: (graph.nodes || []).filter((node) => node.id !== finalNode?.id).map((node) => node.description),
            graph,
        };
        const workspace = investigation.closest('[data-demo-workspace]');
        const body = {
            case_folio: workspace.dataset.caseFolio, story: story.value.trim(), model: result.dataset.model || '',
            facts: factsPayload().map((fact) => fact.description), fact_details: factsPayload(), tree: treeValues,
            factor_codes: [...result.querySelectorAll('[name="ai_factor_codes[]"]:checked')].map((input) => input.value),
            prompt_version: proposalMetadata.measures?.prompt_version || proposalMetadata.tree?.prompt_version || proposalMetadata.facts?.prompt_version || '',
            quality_warnings: [...new Set(Object.values(proposalMetadata).flatMap((metadata) => metadata.quality_warnings || []))],
            measures: [...result.querySelectorAll('[data-ai-measure]')].map((row) => ({ description: row.querySelector('[name="ai_measure_description[]"]').value, category: row.querySelector('[name="ai_measure_category[]"]').value, responsible: row.querySelector('[name="ai_measure_owner[]"]').value, due_date: row.querySelector('[name="ai_measure_due[]"]').value || null, criterion: row.querySelector('[name="ai_measure_criterion[]"]').value, related_node_ids: row.querySelector('[name="ai_measure_nodes[]"]').value.split(',').map((id) => id.trim()).filter(Boolean) })),
        };
        try {
            await postJson(workspace.dataset.aiStoreUrl, body, 'No fue posible guardar la propuesta.');
            if (inputRevision !== revision) {
                showChangeNotice('Se guardó la versión enviada, pero el relato cambió durante el guardado. Genere nuevamente el análisis para incorporar sus cambios.');
                return;
            }
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

            const diagram = document.createElement('div'); diagram.className = 'causal-graph-viewport';
            renderCausalTree(diagram, graph); treeTarget.replaceChildren(diagram);

            result.querySelectorAll('[data-ai-measure]').forEach((source) => {
                const row = document.createElement('tr');
                ['ai_measure_description[]', 'ai_measure_category[]', 'ai_measure_owner[]', 'ai_measure_due[]'].forEach((name) => {
                    const cell = document.createElement('td');
                    cell.textContent = source.querySelector(`[name="${name}"]`).value;
                    row.append(cell);
                });
                measuresTarget.append(row);
            });

            investigation.closest('[data-demo-workspace]').querySelectorAll('[data-ai-derived-nav]').forEach((nav) => nav.classList.remove('hidden'));
            result.querySelector('[data-ai-saved-status]').classList.remove('hidden');
            saveButton.textContent = 'Propuesta guardada';
            investigation.closest('[data-demo-workspace]').querySelector('[data-section-target="inv-4"]').click();
        } catch (exception) {
            showError(exception.message);
            saveButton.textContent = originalLabel;
        } finally {
            busy = false;
            refreshControls();
        }
    });
    result.querySelector('[data-ai-measures]').addEventListener('input', clearSavedStatus);
    result.querySelector('[data-ai-factors]').addEventListener('change', clearSavedStatus);
    section.closest('[data-demo-workspace]').addEventListener('giatep:interview-saved', () => {
        inputRevision++;
        resetLaterStages();
        section.querySelector('[data-ai-metadata="story"]').classList.add('hidden');
        showChangeNotice('Se guardó una entrevista nueva. Actualice el relato para integrar esa fuente y genere nuevamente los hechos, el árbol y las medidas.');
    });
    investigation.addEventListener('submit', (event) => {
        if (! busy) return;
        event.preventDefault();
        event.stopImmediatePropagation();
        showError('Espere a que termine la solicitud de IA antes de guardar la investigación.');
    }, true);
    refreshControls();
    refreshModels();
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
