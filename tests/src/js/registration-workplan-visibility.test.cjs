const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const { test } = require('node:test');

const root = process.env.MAPAS_ROOT || process.cwd();
const { reactive, computed } = require(path.join(root, 'src/modules/Components/node_modules/vue'));
const read = (relative) => fs.readFileSync(path.join(root, relative), 'utf8');
const components = {};
const listeners = new Map();
const sandbox = {
    app: { component: (name, options) => { components[name] = options; } },
    $TEMPLATES: {},
    $MAPAS: { EntitiesDescription: { workplan: {} } },
    Entity: function Entity() {},
    window: {
        location: { hash: '' },
        addEventListener() {},
        removeEventListener() {},
    },
    addEventListener: (name, callback) => listeners.set(name, callback),
    removeEventListener: (name, callback) => {
        if (listeners.get(name) === callback) listeners.delete(name);
    },
};
vm.createContext(sandbox);
for (const relative of [
    'src/modules/Opportunities/components/registration-edition/script.js',
    'src/modules/Opportunities/components/registration-form/script.js',
    'src/modules/OpportunityWorkplan/components/registration-workplan/script.js',
]) {
    vm.runInContext(read(relative), sandbox, { filename: relative });
}
const edition = components['registration-edition'];
const workplan = components['registration-workplan'];
const template = read('src/modules/OpportunityWorkplan/components/registration-workplan/template.php');
const expression = template.match(/<mc-card\b[^>]*\bclass="registration-workplan"[^>]*\bv-if="([^"]+)"/);
assert.ok(expression, 'O teste precisa localizar a condição real do cartão do plano');
// Avalia apenas a expressão de um template local e confiável do repositório.
const evaluateVisibility = new Function('scope', `with (scope) { return Boolean(${expression[1]}); }`);

function fixture(conditional = null, baseCount = 3) {
    const steps = Array.from({ length: baseCount }, (_, index) => ({
        id: index + 1, _id: index + 1, displayOrder: index + 1, metadata: {},
    }));
    if (conditional) {
        steps.push({ id: 10, _id: 10, displayOrder: 10, metadata: { conditional } });
    }
    return reactive({
        id: 101,
        category: 'Categoria A', proponentType: 'Pessoa Física', range: 'Faixa A',
        opportunity: { enableWorkplan: true, registrationSteps: steps },
    });
}

function stateFor(registration, stepIndex, hash) {
    sandbox.window.location.hash = hash;
    const navigation = reactive({ stepIndex });
    const steps = computed(() => edition.computed.steps.call({ entity: registration }));
    const last = computed(() => edition.computed.isLastStep.call({
        stepIndex: navigation.stepIndex, steps: steps.value,
    }));
    const context = {
        registration,
        opportunity: registration.opportunity,
        enableWorkplanInStep: false,
        get isLastStep() { return last.value; },
    };
    // Permite demonstrar o defeito na versão antiga e testar o contrato novo.
    // Os testes de bindings abaixo verificam o repasse real entre os templates.
    if (workplan.methods.handleHashChange) {
        workplan.methods.handleHashChange.call(context);
    }
    return { context, navigation, steps, visible: () => evaluateVisibility(context) };
}

for (const [label, conditional] of [
    ['categoria', { categories: ['Categoria B'] }],
    ['tipo de proponente', { proponentTypes: ['Pessoa Jurídica'] }],
    ['faixa', { ranges: ['Faixa B'] }],
]) {
    test(`mostra o plano na última etapa visível com filtro de ${label}`, () => {
        const state = stateFor(fixture(conditional), 2, '#etapa_3');
        assert.equal(state.steps.value.length, 3);
        assert.equal(state.context.isLastStep, true);
        assert.equal(state.visible(), true);
    });
}

test('mostra o plano sem etapas condicionais', () => {
    assert.equal(stateFor(fixture(), 2, '#etapa_3').visible(), true);
});

test('oculta o plano na etapa intermediária', () => {
    assert.equal(stateFor(fixture(), 1, '#etapa_2').visible(), false);
});

test('mostra o plano quando a quarta etapa também se aplica', () => {
    const state = stateFor(fixture({ categories: ['Categoria A'] }), 3, '#etapa_4');
    assert.equal(state.steps.value.length, 4);
    assert.equal(state.visible(), true);
});

test('mostra o plano na única etapa visível, mesmo sem hash', () => {
    const state = stateFor(fixture({ categories: ['Categoria B'] }, 1), 0, '');
    assert.equal(state.steps.value.length, 1);
    assert.equal(state.visible(), true);
});

test('uma etapa única sem condições continua funcionando', () => {
    assert.equal(stateFor(fixture(null, 1), 0, '').visible(), true);
});

test('não exibe o plano desabilitado', () => {
    const registration = fixture();
    registration.opportunity.enableWorkplan = false;
    assert.equal(stateFor(registration, 2, '#etapa_3').visible(), false);
});

test('acompanha a ativação do plano sem mudar de etapa', () => {
    const registration = fixture({ categories: ['Categoria B'] });
    const state = stateFor(registration, 2, '#etapa_3');
    assert.equal(state.visible(), true);
    registration.opportunity.enableWorkplan = false;
    assert.equal(state.visible(), false);
    registration.opportunity.enableWorkplan = true;
    assert.equal(state.visible(), true);
    state.navigation.stepIndex = 1;
    assert.equal(state.visible(), false);
});

for (const source of ['workplanOpportunity', 'parent']) {
    test(`respeita a habilitação herdada de ${source}`, () => {
        const registration = fixture();
        const configuration = reactive({ enableWorkplan: false });
        if (source === 'workplanOpportunity') {
            registration.workplanOpportunity = configuration;
            registration.opportunity.parent = { enableWorkplan: true };
        } else {
            registration.opportunity.parent = configuration;
        }
        const data = workplan.data.call({ registration, getWorkplan() {} });
        const state = stateFor(registration, 2, '#etapa_3');
        state.context.opportunity = data.opportunity;
        assert.equal(data.opportunity, configuration);
        assert.equal(state.visible(), false);
        registration.opportunity.enableWorkplan = false;
        configuration.enableWorkplan = true;
        assert.equal(state.visible(), true);
        state.navigation.stepIndex = 1;
        assert.equal(state.visible(), false);
    });
}

test('não exibe o plano quando não existe etapa visível', () => {
    const registration = fixture({ categories: ['Categoria B'] }, 0);
    const state = stateFor(registration, 0, '');
    assert.equal(state.steps.value.length, 0);
    assert.equal(state.context.isLastStep, false);
    assert.equal(state.visible(), false);
});

test('atualiza quando o filtro muda sem alteração do hash ou índice', () => {
    const registration = fixture({ categories: ['Categoria A'] });
    const state = stateFor(registration, 2, '#etapa_3');
    assert.equal(state.visible(), false); // 3 de 4.
    registration.category = 'Categoria B';
    assert.equal(state.steps.value.length, 3);
    assert.equal(state.visible(), true); // 3 de 3, mesmo hash.
    registration.category = 'Categoria A';
    assert.equal(state.visible(), false); // Volta a ser 3 de 4.
});

test('navegação pelo estado do formulário não depende de hashchange', () => {
    const state = stateFor(fixture({ categories: ['Categoria B'] }), 0, '#etapa_1');
    assert.equal(state.visible(), false);
    state.navigation.stepIndex = 2;
    assert.equal(state.visible(), true);
    state.navigation.stepIndex = 1;
    assert.equal(state.visible(), false);
});

test('os componentes declaram e encaminham o contrato de última etapa', () => {
    assert.ok(components['registration-form'].props.isLastStep);
    assert.ok(workplan.props.isLastStep);
    const expected = [
        ['src/modules/Opportunities/components/registration-edition/template.php', 'registration-form'],
        ['src/modules/Support/components/support-edition/template.php', 'registration-form'],
        ['src/modules/OpportunityWorkplan/layouts/parts/registration-workplan.php', 'registration-workplan'],
    ];
    for (const [file, tag] of expected) {
        const element = read(file).match(new RegExp(`<${tag}\\b[^>]*>`));
        assert.ok(element, file);
        assert.match(element[0], /:is-last-step\s*=/, file);
    }
});

test('preserva o salvamento do plano e remove o listener ao desmontar', async () => {
    listeners.clear();
    let saves = 0;
    const registration = fixture();
    const context = {
        registration,
        handleHashChange() {},
        save_(showMessages, redirect) {
            assert.equal(showMessages, false);
            assert.equal(redirect, false);
            saves++;
            return Promise.resolve();
        },
    };
    workplan.mounted.call(context);
    const listener = listeners.get('registration.beforeSave');
    assert.equal(typeof listener, 'function');
    const unrelated = { detail: { registrationId: 999, promises: [] } };
    listener(unrelated);
    assert.equal(saves, 0);
    assert.equal(unrelated.detail.promises.length, 0);
    const event = { detail: { registrationId: registration.id, promises: [] } };
    listener(event);
    assert.equal(event.detail.promises.length, 1);
    await Promise.all(event.detail.promises);
    assert.equal(saves, 1);
    workplan.beforeUnmount.call(context);
    assert.equal(listeners.has('registration.beforeSave'), false);
});
