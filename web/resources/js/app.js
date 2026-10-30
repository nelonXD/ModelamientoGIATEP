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
            const nextDepth = depth.get(consequence) + 1;
            if (!depth.has(edge.from) || depth.get(edge.from) < nextDepth) {
                depth.set(edge.from, nextDepth);
                queue.push(edge.from);
            }
        });
    }
    const connectedDepth = Math.max(0, ...depth.values());
    nodes.forEach((node, index) => {
        if (!depth.has(node.id)) {
            depth.set(node.id, connectedDepth + 1);
        }
    });
    const maxDepth = Math.max(0, ...depth.values());
    const levels = Array.from({ length: maxDepth + 1 }, () => []);
    nodes.forEach((node, index) => {
        levels[depth.get(node.id)].push({ ...node, order: index });
    });
    const width = Math.max(680, ...levels.map((level) => level.length * 250 + 80));
    const rowHeight = 160;
    const height = 145 + levels.length * rowHeight;
    const svg = document.createElementNS(svgNs, 'svg');
    svg.setAttribute('viewBox', `0 0 ${width} ${height}`);
    svg.setAttribute('role', 'img');
    svg.setAttribute('aria-label', 'Árbol de causas vertical. El hecho final está arriba y los antecedentes se ramifican hacia abajo.');
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
    const orangeMarker = marker.cloneNode(true);
    orangeMarker.id = `${markerId}-orange`;
    orangeMarker.querySelector('path').classList.add('is-branch');
    defs.append(orangeMarker);
    const positions = new Map();
    levels.forEach((level, levelIndex) => level.forEach((node, index) => {
        positions.set(node.id, { x: width * (index + 1) / (level.length + 1), y: 48 + levelIndex * rowHeight });
    }));

    edges.forEach((edge) => {
        const source = positions.get(edge.to);
        const target = positions.get(edge.from);
        if (!source || !target) return;
        const path = document.createElementNS(svgNs, 'path');
        const sourceY = source.y + 82;
        const targetY = target.y - 24;
        path.setAttribute('d', `M ${source.x} ${sourceY} C ${source.x} ${(sourceY + targetY) / 2}, ${target.x} ${(sourceY + targetY) / 2}, ${target.x} ${targetY}`);
        const isBranch = edge.logic === 'conjuncion' || edge.logic === 'disyuncion';
        path.setAttribute('marker-end', `url(#${edge.link_type === 'aparente' || isBranch ? orangeMarker.id : markerId})`);
        path.classList.add('causal-graph-edge');
        if (edge.link_type === 'aparente') path.classList.add('is-apparent');
        else if (isBranch) path.classList.add('is-branch');
        const title = document.createElementNS(svgNs, 'title');
        title.textContent = `${edge.logic}: ${edge.basis || 'relación causal propuesta'}`;
        path.append(title); svg.append(path);
    });

    const wrap = (value, limit = 25, maxLines = 3) => {
        const words = String(value || '').split(/\s+/);
        const lines = [];
        let line = '';
        words.forEach((word) => {
            if (line && `${line} ${word}`.length > limit) { lines.push(line); line = word; }
            else line = line ? `${line} ${word}` : word;
        });
        if (line) lines.push(line);
        const visible = lines.slice(0, maxLines);
        if (lines.length > maxLines) visible[maxLines - 1] = `${visible[maxLines - 1].replace(/[.,;:]$/, '')}…`;
        return visible;
    };
    levels.forEach((level, levelIndex) => level.forEach((node) => {
        const point = positions.get(node.id);
        const group = document.createElementNS(svgNs, 'g');
        group.classList.add('causal-graph-node', node.type === 'permanente' ? 'is-permanent' : 'is-event');
        if (node.id === finalId) group.classList.add('is-final');
        const isFinal = node.id === finalId;
        const shape = document.createElementNS(svgNs, isFinal ? 'polygon' : (node.type === 'permanente' ? 'rect' : 'circle'));
        if (isFinal) {
            shape.setAttribute('points', `${point.x},${point.y - 22} ${point.x + 21},${point.y + 17} ${point.x - 21},${point.y + 17}`);
        } else if (node.type === 'permanente') {
            shape.setAttribute('x', point.x - 18); shape.setAttribute('y', point.y - 18);
            shape.setAttribute('width', '36'); shape.setAttribute('height', '36'); shape.setAttribute('rx', '1');
        } else {
            shape.setAttribute('cx', point.x); shape.setAttribute('cy', point.y); shape.setAttribute('r', '18');
        }
        group.append(shape);
        const number = document.createElementNS(svgNs, 'text');
        number.setAttribute('x', point.x); number.setAttribute('y', point.y + (isFinal ? 9 : 5));
        number.classList.add('causal-graph-number');
        number.textContent = String(node.id).replace(/^H[-_]?/i, '') || node.id;
        group.append(number);
        const card = document.createElementNS(svgNs, 'rect');
        card.setAttribute('x', point.x - 98); card.setAttribute('y', point.y + 25);
        card.setAttribute('width', '196'); card.setAttribute('height', '57'); card.setAttribute('rx', '7');
        card.classList.add('causal-graph-description-card');
        group.append(card);
        const label = document.createElementNS(svgNs, 'text');
        label.setAttribute('x', point.x); label.setAttribute('y', point.y + 43);
        label.classList.add('causal-graph-label');
        wrap(node.description, 27, 3).forEach((line, lineIndex) => {
            const span = document.createElementNS(svgNs, 'tspan');
            span.setAttribute('x', point.x); span.setAttribute('dy', lineIndex === 0 ? '0' : '14'); span.textContent = line;
            label.append(span);
        });
        group.append(label);
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
    workspace.querySelectorAll('[data-section-target]').forEach((item) => {
        const isActive = item.dataset.sectionTarget === button.dataset.sectionTarget;
        const isProgressStep = item.matches('[data-investigation-progress] [data-section-target]');
        if (! isProgressStep) {
            item.classList.toggle('section-link-active', isActive);
        }
        item.setAttribute('aria-current', isActive ? 'step' : 'false');

        const number = item.querySelector('[data-progress-number]');
        if (number) {
            number.classList.toggle('bg-blue-950', isActive);
            number.classList.toggle('text-white', isActive);
            number.classList.toggle('bg-slate-200', ! isActive);
            number.classList.toggle('text-slate-600', ! isActive);
        }

        if (isProgressStep) {
            item.classList.toggle('border-blue-200', isActive);
            item.classList.toggle('bg-blue-50', isActive);
        }
    });
    workspace.querySelectorAll('[data-section]').forEach((section) => section.classList.toggle('hidden', section.dataset.section !== button.dataset.sectionTarget));
}));

document.querySelectorAll('[data-external-analysis]').forEach((panel) => {
    const workspace = panel.closest('[data-demo-workspace]');
    const story = panel.querySelector('[data-analysis-story]');
    const promptOutput = panel.querySelector('[data-external-prompt]');
    const externalResponse = panel.querySelector('[data-external-response]');
    const modelSelect = panel.querySelector('[data-local-model]');
    const status = panel.querySelector('[data-analysis-status]');
    const resultPanel = panel.querySelector('[data-analysis-result]');
    const saveButton = panel.querySelector('[data-save-external-analysis]');
    if (! workspace || ! story || ! promptOutput || ! externalResponse || ! modelSelect) return;

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    let importedAnalysis = null;
    let questionnaireOptions = [];

    const requestJson = async (url, payload) => {
        const response = await fetch(url, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify(payload),
        });
        const data = await response.json().catch(() => ({}));
        if (! response.ok) throw new Error(data.message || Object.values(data.errors || {}).flat().join(' ') || `Error HTTP ${response.status}`);
        return data;
    };

    const collectQuestionnaireOptions = () => {
        const form = workspace.querySelector('[data-adapted-questionnaire]');
        if (! form) return [];
        const fields = new Map();
        form.querySelectorAll('input[type="radio"][name], input[type="checkbox"][name]').forEach((input) => {
            const name = input.name;
            if (! name || name === '_token') return;
            const label = input.closest('label')?.innerText?.trim() || input.value;
            if (! fields.has(name)) {
                const section = input.closest('fieldset');
                const question = input.closest('[data-branch-question]')?.querySelector(':scope > p')?.innerText?.trim()
                    || input.closest('tr')?.querySelector('td:first-child')?.innerText?.trim()
                    || input.closest('[data-development-question]')?.querySelector('label')?.innerText?.trim()
                    || section?.querySelector('legend')?.innerText?.trim() || '';
                fields.set(name, { field: name, type: input.type, question, options: [] });
            }
            if (! fields.get(name).options.includes(label)) fields.get(name).options.push(label);
        });
        // Solo enviar opciones para preguntas con respuestas guardadas. El formulario
        // puede mostrar decenas de campos vacíos que no aportan contexto a la importación.
        return [...fields.values()].filter((field) => {
            const selected = [...form.querySelectorAll(`input[name="${CSS.escape(field.field)}"]:checked`)];
            return field.options.length > 0 && selected.length > 0;
        }).map((field) => ({ ...field, selected_values: [...form.querySelectorAll(`input[name="${CSS.escape(field.field)}"]:checked`)].map((input) => input.closest('label')?.innerText?.trim() || input.value) }));
    };

    const setBusy = (busy, message) => {
        panel.querySelectorAll('[data-create-external-prompt], [data-copy-external-prompt], [data-import-external]').forEach((button) => { button.disabled = busy; });
        if (message) status.textContent = message;
    };

    const textNode = (tag, text, classes = '') => {
        const element = document.createElement(tag);
        element.className = classes;
        element.textContent = text || '';
        return element;
    };

    const showAnalysis = (analysis) => {
        const factsHost = workspace.querySelector('[data-derived-facts]');
        const treeHost = workspace.querySelector('[data-derived-tree]');
        const measuresHost = workspace.querySelector('[data-derived-measures]');
        const facts = Array.isArray(analysis.facts) ? analysis.facts : [];
        const tree = analysis.tree || { nodes: [], edges: [] };
        const nodes = Array.isArray(tree.nodes) ? tree.nodes : [];
        const edges = Array.isArray(tree.edges) ? tree.edges : [];
        const measures = Array.isArray(analysis.measures) ? analysis.measures : [];

        if (factsHost) {
            factsHost.replaceChildren();
            facts.forEach((fact, index) => {
                const card = document.createElement('div');
                card.className = 'rounded-xl border border-slate-200 bg-white p-4';
                card.append(textNode('strong', `H-${String(index + 1).padStart(2, '0')} · ${fact.classification || 'hecho'} · ${fact.evidence_status || 'pendiente'}`, 'mr-2 rounded bg-sky-100 px-2 py-1 text-xs text-sky-900'));
                card.append(textNode('p', fact.description, 'mt-2 font-semibold text-slate-800'));
                if (fact.evidence) card.append(textNode('p', `Respaldo: “${fact.evidence}”`, 'mt-2 text-xs text-slate-600'));
                if (fact.source) card.append(textNode('p', `Fuente: ${fact.source}`, 'mt-1 text-xs text-slate-500'));
                factsHost.append(card);
            });
            if (facts.length === 0) factsHost.append(textNode('p', 'No se validaron hechos con respaldo suficiente. Revisa las verificaciones pendientes.', 'empty-state p-4 text-center'));
        }

        if (treeHost) {
            treeHost.replaceChildren();
            if (nodes.length > 0 && nodes.some((node) => node.id === tree.final_node_id)) {
                const viewport = document.createElement('div');
                viewport.className = 'causal-graph-viewport';
                treeHost.append(viewport);
                renderCausalTree(viewport, tree);
            } else {
                treeHost.append(textNode('p', 'No se pudo identificar un hecho final con respaldo suficiente.', 'rounded-xl border border-amber-200 bg-amber-50 p-4 text-center text-amber-900'));
            }
        }

        if (measuresHost) {
            measuresHost.replaceChildren();
            measures.forEach((measure) => {
                const row = document.createElement('tr');
                const related = (measure.related_node_ids || []).join(', ');
                [measure.description, measure.category, measure.responsible, measure.due_date || 'Por definir', related || ''].forEach((value) => row.append(textNode('td', value, '')));
                row.append(textNode('td', 'Pendiente de revisión', ''));
                measuresHost.append(row);
            });
            if (measures.length === 0) {
                const row = document.createElement('tr');
                const cell = textNode('td', 'No se propusieron medidas vinculadas a causas revisadas.', 'py-4 text-center text-slate-600');
                cell.colSpan = 6;
                row.append(cell);
                measuresHost.append(row);
            }
        }

        panel.querySelector('[data-result-fact-count]').textContent = String(facts.length);
        panel.querySelector('[data-result-node-count]').textContent = String(nodes.length);
        panel.querySelector('[data-result-measure-count]').textContent = String(measures.length);
        const warnings = [...(analysis.quality_warnings || []), ...(analysis.pending_verifications || []).map((item) => `Por verificar: ${item}`)];
        const warningsList = panel.querySelector('[data-analysis-warnings]');
        warningsList.replaceChildren();
        warnings.forEach((warning) => warningsList.append(textNode('li', warning)));
        if (warningsList.childElementCount === 0) warningsList.append(textNode('li', 'Sin advertencias ni verificaciones pendientes reportadas.'));
        const questionnaireSelections = analysis.questionnaire_selections || [];
        const questionnaireBox = panel.querySelector('[data-questionnaire-proposals]');
        const questionnaireList = panel.querySelector('[data-questionnaire-selections]');
        questionnaireList.replaceChildren();
        questionnaireSelections.forEach((selection) => {
            questionnaireList.append(textNode('li', `${selection.field}: ${(selection.selected_values || []).join(', ')}${selection.evidence ? ` · Respaldo: “${selection.evidence}”` : ''}`));
        });
        questionnaireBox.classList.toggle('hidden', questionnaireSelections.length === 0);
        resultPanel.classList.remove('hidden');
        saveButton.disabled = facts.length === 0;
        if (facts.length > 0) {
            workspace.querySelectorAll('[data-ai-derived-nav]').forEach((button) => {
                button.disabled = false;
                button.removeAttribute('title');
                button.classList.remove('cursor-not-allowed', 'opacity-45');
            });
        }
        importedAnalysis = analysis;
    };

    const loadModels = async () => {
        if (! workspace.dataset.modelsUrl) return;
        try {
            const response = await fetch(workspace.dataset.modelsUrl, { headers: { 'Accept': 'application/json' } });
            if (! response.ok) return;
            const data = await response.json();
            (data.models || []).forEach((model) => {
                const option = document.createElement('option');
                option.value = model.id;
                option.textContent = model.name || model.id;
                modelSelect.append(option);
                if (model.id === data.configured_model) option.selected = true;
            });
            if (! data.connected) status.textContent = 'No se pudo consultar LM Studio. Se usará el modelo configurado si está disponible.';
        } catch {
            status.textContent = 'No se pudo consultar la lista de modelos. Puedes intentar con el modelo configurado.';
        }
    };
    loadModels();

    panel.querySelector('[data-create-external-prompt]')?.addEventListener('click', async () => {
        if (story.value.trim().length < 30) { status.textContent = 'El relato debe tener al menos 30 caracteres.'; story.focus(); return; }
        setBusy(true, 'Preparando prompt con las fuentes del expediente…');
        try {
            questionnaireOptions = collectQuestionnaireOptions();
            const data = await requestJson(workspace.dataset.externalPromptUrl, {
                case_folio: workspace.dataset.caseFolio,
                story: story.value.trim(),
                questionnaire_options: questionnaireOptions,
            });
            promptOutput.value = data.prompt || '';
            panel.querySelector('[data-copy-external-prompt]').disabled = ! promptOutput.value;
            status.textContent = 'Prompt listo. Cópialo en la IA externa y pega aquí su respuesta.';
        } catch (error) {
            status.textContent = error.message;
        } finally { setBusy(false); }
    });

    panel.querySelector('[data-copy-external-prompt]')?.addEventListener('click', async () => {
        try {
            await navigator.clipboard.writeText(promptOutput.value);
            status.textContent = 'Prompt copiado.';
        } catch { status.textContent = 'No se pudo copiar automáticamente; selecciona y copia el texto.'; }
    });

    panel.querySelector('[data-import-external]')?.addEventListener('click', async () => {
        if (story.value.trim().length < 30 || externalResponse.value.trim().length < 10) { status.textContent = 'Completa el relato y pega la respuesta de la IA externa.'; return; }
        setBusy(true, 'LM Studio está contrastando la respuesta con las fuentes…');
        try {
            const data = await requestJson(workspace.dataset.externalImportUrl, {
                case_folio: workspace.dataset.caseFolio,
                story: story.value.trim(),
                external_response: externalResponse.value.trim(),
                model: modelSelect.value || null,
                questionnaire_options: questionnaireOptions,
            });
            showAnalysis(data);
            status.textContent = 'Revisión terminada. Los resultados están disponibles en Hechos, Árbol y Medidas.';
            panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
        } catch (error) {
            status.textContent = error.message;
        } finally { setBusy(false); }
    });

    saveButton?.addEventListener('click', async () => {
        if (! importedAnalysis?.facts?.length) return;
        saveButton.disabled = true;
        status.textContent = 'Guardando la propuesta revisada…';
        const tree = importedAnalysis.tree || { final_node_id: '', nodes: [], edges: [] };
        const nodes = tree.nodes || [];
        const finalNode = nodes.find((node) => node.id === tree.final_node_id);
        const payload = {
            case_folio: workspace.dataset.caseFolio,
            story: story.value.trim(),
            model: importedAnalysis.model || modelSelect.value || null,
            facts: importedAnalysis.facts.map((fact) => fact.description),
            fact_details: importedAnalysis.facts,
            tree: {
                final: finalNode?.description || '',
                origin: '',
                causes: nodes.filter((node) => node.id !== tree.final_node_id).map((node) => node.description),
                graph: tree,
            },
            factor_codes: [],
            measures: (importedAnalysis.measures || []).map((measure) => ({
                description: measure.description,
                category: measure.category,
                responsible: measure.responsible || 'Pendiente de asignar',
                due_date: measure.due_date || null,
                criterion: measure.criterion || '',
                related_node_ids: measure.related_node_ids || [],
            })),
            quality_warnings: importedAnalysis.quality_warnings || [],
            prompt_version: importedAnalysis.prompt_version || null,
        };
        try {
            const data = await requestJson(workspace.dataset.analysisStoreUrl, payload);
            status.textContent = data.message || 'Análisis guardado correctamente.';
        } catch (error) {
            status.textContent = error.message;
            saveButton.disabled = false;
        }
    });

    const editor = workspace.querySelector('[data-results-editor]');
    const editorBody = editor?.querySelector('[data-results-editor-body]');
    if (editor && editorBody && workspace.dataset.analysisStoreUrl) {
        let activeKind = 'facts';
        const graphElement = workspace.querySelector('[data-tree-graph-json]');
        let graph = {};
        try { graph = JSON.parse(graphElement?.dataset.treeGraphJson || '{}'); } catch { graph = {}; }
        let facts = [...workspace.querySelectorAll('[data-derived-facts] > div')]
            .map((row) => row.querySelector('p')?.innerText.trim() || [...row.childNodes].filter((node) => node.nodeType === Node.TEXT_NODE).map((node) => node.textContent).join(' ').replace(/^\s*/, '').trim())
            .filter((value) => value && !value.includes('Aún no se han constatado'))
            .map((description) => ({ description, classification: 'hecho', evidence_status: 'declarado', evidence: '', source: 'Relato de la investigación' }));
        let nodes = Array.isArray(graph.nodes) ? graph.nodes.map((node) => ({ ...node })) : [];
        let edges = Array.isArray(graph.edges) ? graph.edges.map((edge) => ({ ...edge })) : [];
        let finalId = graph.final_node_id || '';
        if (!nodes.length) {
            const legacy = [...workspace.querySelectorAll('[data-derived-tree] .cause-node')].map((node, index) => ({
                id: `n${index + 1}`, type: node.classList.contains('cause-final') ? 'hecho' : 'permanente', description: node.querySelector('p')?.innerText.trim() || '',
            })).filter((node) => node.description);
            nodes = legacy;
            finalId = nodes.find((node) => node.type === 'hecho')?.id || nodes[0]?.id || '';
            edges = nodes.filter((node) => node.id !== finalId).map((node) => ({ from: node.id, to: finalId, link_type: 'causal', logic: 'cadena', basis: 'Relación revisada por el investigador', evidence: '' }));
        }
        let measures = [...workspace.querySelectorAll('[data-derived-measures] tr')].map((row) => {
            const cells = [...row.querySelectorAll('td')];
            const fullDescription = cells[0]?.innerText.trim() || '';
            if (!fullDescription || fullDescription.includes('Aún no hay medidas')) return null;
            const description = fullDescription.split(/\nCriterio de cobertura y estabilidad:/i)[0].split(/\nFactores relacionados:/i)[0].split(/\nCausas abordadas:/i)[0].trim();
            const criterion = fullDescription.match(/Criterio de cobertura y estabilidad:\s*([^\n]+)/i)?.[1]?.trim() || '';
            const references = fullDescription.match(/(?:Factores relacionados|Causas abordadas):\s*([^\n]+)/i)?.[1] || '';
            const related_node_ids = references.split(/[;,]/).map((value) => value.trim()).filter((value) => nodes.some((node) => node.id === value));
            const displayedDate = cells[3]?.innerText.trim() || '';
            const dateParts = displayedDate.match(/^(\d{2})-(\d{2})-(\d{4})$/);
            const due_date = dateParts ? `${dateParts[3]}-${dateParts[2]}-${dateParts[1]}` : '';
            return { description, category: cells[1]?.innerText.trim() || 'Administrativa', responsible: cells[2]?.innerText.trim() || 'Pendiente de asignar', due_date, criterion, related_node_ids };
        }).filter(Boolean);
        const safe = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[char]));
        const input = (label, value, key, type = 'text') => `<label class="block text-xs font-bold text-slate-600">${label}<input class="input mt-1" type="${type}" data-key="${key}" value="${safe(value)}"></label>`;
        const area = (label, value, key) => `<label class="block text-xs font-bold text-slate-600">${label}<textarea class="input mt-1" rows="2" data-key="${key}">${safe(value)}</textarea></label>`;
        const section = (title, key, rows) => `<section class="rounded-xl border border-slate-200 p-4"><div class="mb-3 flex items-center justify-between"><h4 class="font-black">${title}</h4><button type="button" class="btn-secondary" data-add="${key}">Agregar</button></div><div class="space-y-3" data-list="${key}">${rows}</div></section>`;
        const renderEditor = () => {
            const nodeRows = nodes.map((node, index) => `<div class="rounded-lg bg-slate-50 p-3"><div class="mb-2 flex justify-between"><strong>Nodo ${safe(node.id)} ${node.id === finalId ? '· Hecho final' : ''}</strong><button type="button" class="text-sm font-bold text-red-700" data-delete="node:${index}">Eliminar</button></div><div class="grid gap-2 sm:grid-cols-3"><label class="text-xs font-bold text-slate-600">Tipo<select class="input mt-1" data-key="node:${index}:type"><option value="hecho" ${node.type === 'hecho' ? 'selected' : ''}>Hecho</option><option value="permanente" ${node.type === 'permanente' ? 'selected' : ''}>Hecho permanente</option></select></label>${node.id === finalId ? '<span class="self-center text-xs text-red-700">El hecho final se muestra arriba</span>' : `<label class="text-xs font-bold text-slate-600">Hecho final<select class="input mt-1" data-final-select="${index}"><option value="0">No</option><option value="1" ${node.id === finalId ? 'selected' : ''}>Sí</option></select></label>`}</div>${area('Descripción', node.description, `node:${index}:description`)}</div>`).join('');
            const edgeRows = edges.map((edge, index) => `<div class="rounded-lg bg-slate-50 p-3"><div class="mb-2 flex justify-between"><strong>Vínculo ${index + 1}</strong><button type="button" class="text-sm font-bold text-red-700" data-delete="edge:${index}">Eliminar</button></div><div class="grid gap-2 sm:grid-cols-2"><label class="text-xs font-bold">Antecedente<select class="input mt-1" data-key="edge:${index}:from">${nodes.map((node) => `<option value="${safe(node.id)}" ${node.id === edge.from ? 'selected' : ''}>${safe(node.id)} · ${safe(node.description)}</option>`).join('')}</select></label><label class="text-xs font-bold">Consecuencia<select class="input mt-1" data-key="edge:${index}:to">${nodes.map((node) => `<option value="${safe(node.id)}" ${node.id === edge.to ? 'selected' : ''}>${safe(node.id)} · ${safe(node.description)}</option>`).join('')}</select></label></div><div class="mt-2 grid gap-2 sm:grid-cols-2"><label class="text-xs font-bold">Vínculo<select class="input mt-1" data-key="edge:${index}:link_type"><option value="causal" ${edge.link_type === 'causal' ? 'selected' : ''}>Causal</option><option value="aparente" ${edge.link_type === 'aparente' ? 'selected' : ''}>Aparente</option></select></label><label class="text-xs font-bold">Lógica<select class="input mt-1" data-key="edge:${index}:logic"><option value="cadena">Cadena</option><option value="conjuncion" ${edge.logic === 'conjuncion' ? 'selected' : ''}>Conjunción</option><option value="disyuncion" ${edge.logic === 'disyuncion' ? 'selected' : ''}>Disyunción</option></select></label></div>${area('Fundamento', edge.basis, `edge:${index}:basis`)}</div>`).join('');
            const measureRows = measures.map((measure, index) => `<div class="rounded-lg bg-slate-50 p-3"><div class="mb-2 flex justify-between"><strong>Medida ${index + 1}</strong><button type="button" class="text-sm font-bold text-red-700" data-delete="measure:${index}">Eliminar</button></div>${area('Descripción', measure.description, `measure:${index}:description`)}<div class="mt-2 grid gap-2 sm:grid-cols-2">${input('Jerarquía de control', measure.category, `measure:${index}:category`)}${input('Responsable', measure.responsible, `measure:${index}:responsible`)}${input('Fecha límite', measure.due_date, `measure:${index}:due_date`, 'date')}${input('Criterio de cobertura y estabilidad', measure.criterion, `measure:${index}:criterion`)}</div><label class="mt-2 block text-xs font-bold">Causas relacionadas<select multiple class="input mt-1 min-h-24" data-key="measure:${index}:related_node_ids">${nodes.filter((node) => node.id !== finalId).map((node) => `<option value="${safe(node.id)}" ${(measure.related_node_ids || []).includes(node.id) ? 'selected' : ''}>${safe(node.id)} · ${safe(node.description)}</option>`).join('')}</select></label></div>`).join('');
            const editorContent = activeKind === 'tree'
                ? section('Nodos del árbol causal', 'nodes', nodeRows) + section('Vínculos causales', 'edges', edgeRows)
                : section('Medidas de control', 'measures', measureRows);
            editorBody.innerHTML = editorContent;
            editor.querySelector('[data-results-editor-title]').textContent = activeKind === 'tree' ? 'Editar árbol causal' : 'Editar medidas de control';
            editor.querySelector('[data-save-results-editor]').textContent = `Guardar ${activeKind === 'tree' ? 'árbol' : 'medidas'}`;
        };
        const factsHost = workspace.querySelector('[data-derived-facts]');
        const factsEditButton = workspace.querySelector('[data-facts-edit]');
        const factsAddButton = workspace.querySelector('[data-fact-add]');
        const factsSaveButton = workspace.querySelector('[data-facts-save]');
        const factsCancelButton = workspace.querySelector('[data-facts-cancel]');
        const factsStatus = workspace.querySelector('[data-facts-status]');
        let factsEditing = false;
        let factsOriginal = null;
        const renderFacts = () => {
            if (!factsHost) return;
            factsHost.replaceChildren();
            if (!facts.length) {
                const empty = document.createElement('p');
                empty.className = 'empty-state py-6 text-center';
                empty.textContent = 'Aún no se han constatado hechos para esta investigación.';
                factsHost.append(empty);
            }
            facts.forEach((fact, index) => {
                const row = document.createElement('div');
                row.className = 'rounded-xl border border-slate-200 bg-slate-50 p-4 font-semibold text-slate-800';
                const badge = document.createElement('span');
                badge.className = 'mr-2 rounded bg-sky-700 px-2 py-0.5 text-xs text-white';
                badge.textContent = `H-${String(index + 1).padStart(2, '0')}`;
                if (factsEditing) {
                    const heading = document.createElement('div');
                    heading.className = 'mb-2 flex items-center justify-between gap-3';
                    const label = document.createElement('div');
                    label.append(badge);
                    const remove = document.createElement('button');
                    remove.type = 'button'; remove.className = 'text-sm font-bold text-red-700 hover:text-red-900'; remove.textContent = 'Eliminar';
                    remove.addEventListener('click', () => {
                        if (!window.confirm(`¿Eliminar el hecho H-${String(index + 1).padStart(2, '0')} de la lista?`)) return;
                        facts.splice(index, 1); renderFacts();
                    });
                    heading.append(label, remove);
                    row.append(heading);
                    const textarea = document.createElement('textarea');
                    textarea.className = 'input mt-2 min-h-20 w-full bg-white';
                    textarea.setAttribute('aria-label', `Editar hecho H-${String(index + 1).padStart(2, '0')}`);
                    textarea.value = fact.description;
                    textarea.addEventListener('input', () => { facts[index].description = textarea.value; });
                    row.append(textarea);
                } else {
                    row.append(badge);
                    row.append(document.createTextNode(` ${fact.description}`));
                }
                factsHost.append(row);
            });
        };
        factsEditButton?.addEventListener('click', () => {
            factsOriginal = JSON.parse(JSON.stringify(facts)); factsEditing = true;
            factsEditButton.classList.add('hidden'); factsAddButton?.classList.remove('hidden'); factsSaveButton?.classList.remove('hidden'); factsCancelButton?.classList.remove('hidden'); factsStatus?.classList.remove('hidden');
            if (factsStatus) factsStatus.textContent = 'Edita los textos directamente en la lista. Agregar y eliminar solo está disponible en este modo.';
            renderFacts();
        });
        factsAddButton?.addEventListener('click', () => {
            facts.push({ description: '', classification: 'hecho', evidence_status: 'declarado', evidence: '', source: 'Relato de la investigación' });
            renderFacts();
            const fields = factsHost?.querySelectorAll('textarea'); fields?.[fields.length - 1]?.focus();
        });
        factsCancelButton?.addEventListener('click', () => {
            facts = factsOriginal || facts; factsEditing = false;
            factsEditButton?.classList.remove('hidden'); factsAddButton?.classList.add('hidden'); factsSaveButton?.classList.add('hidden'); factsCancelButton?.classList.add('hidden'); factsStatus?.classList.add('hidden');
            renderFacts();
        });
        factsSaveButton?.addEventListener('click', async () => {
            if (!facts.length || facts.some((fact) => !fact.description.trim())) { if (factsStatus) factsStatus.textContent = 'Completa cada hecho y deja al menos uno en la lista.'; return; }
            if (!nodes.length || !nodes.some((node) => node.id === finalId) || nodes.some((node) => !node.description.trim()) || edges.some((edge) => edge.from === edge.to || !nodes.some((node) => node.id === edge.from) || !nodes.some((node) => node.id === edge.to))) { if (factsStatus) factsStatus.textContent = 'No se puede guardar hasta que el árbol causal tenga nodos y vínculos válidos.'; return; }
            if (measures.some((measure) => !measure.description.trim() || !measure.category.trim() || !measure.responsible.trim())) { if (factsStatus) factsStatus.textContent = 'Completa descripción, jerarquía y responsable de todas las medidas.'; return; }
            factsSaveButton.disabled = true; if (factsStatus) factsStatus.textContent = 'Guardando hechos…';
            const finalNode = nodes.find((node) => node.id === finalId);
            const payload = { case_folio: workspace.dataset.caseFolio, story: workspace.dataset.investigationStory || story.value.trim() || facts.map((fact) => fact.description).join(' '), model: 'edicion-manual', manual_entry: true,
                facts: facts.map((fact) => fact.description.trim()), fact_details: facts.map((fact) => ({ ...fact, description: fact.description.trim() })),
                tree: { final: finalNode.description, origin: '', causes: nodes.filter((node) => node.id !== finalId).map((node) => node.description), graph: { final_node_id: finalId, nodes, edges } }, factor_codes: [],
                measures: measures.map((measure) => ({ ...measure, due_date: measure.due_date || null, related_node_ids: measure.related_node_ids || [] })) };
            try { await requestJson(workspace.dataset.analysisStoreUrl, payload); window.location.reload(); }
            catch (error) { if (factsStatus) factsStatus.textContent = error.message || 'No se pudieron guardar los hechos.'; factsSaveButton.disabled = false; }
        });
        editorBody.addEventListener('input', (event) => {
            const key = event.target.dataset.key;
            if (!key) return;
            const [kind, rawIndex, field] = key.split(':'); const index = Number(rawIndex); const collection = { fact: facts, node: nodes, edge: edges, measure: measures }[kind];
            if (!collection?.[index]) return;
            collection[index][field] = field === 'related_node_ids' ? [...event.target.selectedOptions].map((option) => option.value) : event.target.value;
        });
        editorBody.addEventListener('change', (event) => {
            if (event.target.dataset.finalSelect !== undefined) { finalId = nodes[Number(event.target.dataset.finalSelect)]?.id || finalId; renderEditor(); }
        });
        editorBody.addEventListener('click', (event) => {
            const add = event.target.closest('[data-add]')?.dataset.add;
            if (add === 'facts') facts.push({ description: '', classification: 'hecho', evidence_status: 'declarado', evidence: '', source: 'Relato de la investigación' });
            if (add === 'nodes') { const id = `n${Date.now().toString(36)}`; nodes.push({ id, type: 'hecho', description: '' }); if (!finalId) finalId = id; }
            if (add === 'edges' && nodes.length > 1) edges.push({ from: nodes.find((node) => node.id !== finalId)?.id || nodes[0].id, to: finalId, link_type: 'causal', logic: 'cadena', basis: 'Relación causal revisada', evidence: '' });
            if (add === 'measures') measures.push({ description: '', category: 'Administrativa', responsible: '', due_date: '', criterion: '', related_node_ids: [] });
            const remove = event.target.closest('[data-delete]')?.dataset.delete;
            if (remove) { const [kind, rawIndex] = remove.split(':'); const index = Number(rawIndex); const collection = { fact: facts, node: nodes, edge: edges, measure: measures }[kind]; const deleted = collection?.[index]; collection?.splice(index, 1); if (kind === 'node' && deleted) { edges = edges.filter((edge) => edge.from !== deleted.id && edge.to !== deleted.id); if (deleted.id === finalId) finalId = nodes[0]?.id || ''; } }
            if (add || remove) renderEditor();
        });
        workspace.querySelectorAll('[data-open-results-editor]').forEach((button) => button.addEventListener('click', () => {
            activeKind = button.dataset.openResultsEditor || 'tree';
            button.closest('[data-section]')?.append(editor);
            renderEditor(); editor.classList.remove('hidden'); editor.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }));
        editor.querySelector('[data-close-results-editor]')?.addEventListener('click', () => editor.classList.add('hidden'));
        editor.querySelector('[data-save-results-editor]')?.addEventListener('click', async (event) => {
            const button = event.currentTarget; const status = editor.querySelector('[data-results-editor-status]');
            if (!facts.length || facts.some((fact) => !fact.description.trim())) { status.textContent = 'Agrega al menos un hecho y completa sus descripciones.'; return; }
            if (!nodes.length || !nodes.some((node) => node.id === finalId) || nodes.some((node) => !node.description.trim())) { status.textContent = 'El árbol necesita nodos descritos y un hecho final seleccionado.'; return; }
            if (edges.some((edge) => edge.from === edge.to || !nodes.some((node) => node.id === edge.from) || !nodes.some((node) => node.id === edge.to))) { status.textContent = 'Revisa los vínculos: antecedente y consecuencia deben ser distintos y existir.'; return; }
            if (measures.some((measure) => !measure.description.trim() || !measure.category.trim() || !measure.responsible.trim())) { status.textContent = 'Completa descripción, jerarquía y responsable de cada medida.'; return; }
            button.disabled = true; status.textContent = 'Guardando los resultados…';
            const finalNode = nodes.find((node) => node.id === finalId);
            const payload = { case_folio: workspace.dataset.caseFolio, story: workspace.dataset.investigationStory || story.value.trim() || facts.map((fact) => fact.description).join(' '), model: 'edicion-manual', manual_entry: true,
                facts: facts.map((fact) => fact.description.trim()), fact_details: facts.map((fact) => ({ ...fact, description: fact.description.trim() })),
                tree: { final: finalNode.description, origin: '', causes: nodes.filter((node) => node.id !== finalId).map((node) => node.description), graph: { final_node_id: finalId, nodes, edges } }, factor_codes: [],
                measures: measures.map((measure) => ({ ...measure, due_date: measure.due_date || null, related_node_ids: measure.related_node_ids || [] })) };
            try { const result = await requestJson(workspace.dataset.analysisStoreUrl, payload); status.textContent = result.message || 'Cambios guardados.'; window.location.reload(); }
            catch (error) { status.textContent = error.message || 'No se pudieron guardar los cambios.'; button.disabled = false; }
        });
    }
});

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

const investigationForm = document.querySelector('[data-investigation-form]');
const linkedCaseId = new URLSearchParams(window.location.search).get('case');
if (investigationForm?.dataset.investigationReadonly === 'true') {
    investigationForm.querySelectorAll('input:not([name="workflow_action"]):not([name="_token"]), textarea, select, button:not([name="workflow_action"])').forEach((control) => { control.disabled = true; });
}
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
        const serverDraft = JSON.parse(investigationForm.dataset.serverDraft || '{}');
        const draft = Object.keys(serverDraft).length ? { fields: serverDraft } : JSON.parse(localStorage.getItem(draftStorageKey) || '{}');
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
