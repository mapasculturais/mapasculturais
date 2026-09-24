const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const vm = require('node:vm');

// Descrição dos metadados como o PHP entrega em $DESCRIPTIONS (getPropertiesMetadata)
const PROPERTIES = {
    allow_proponent_response: { type: 'boolean' },
    showPreviousPhaseEvaluationDetails: { type: 'boolean', default: true },
    appealPhaseAffectsSync: { type: 'boolean', default: false },
};

class Entity {
    constructor(values = {}) {
        Object.assign(this, values);
        this.__originalValues = { ...values };
    }

    get $PROPERTIES() {
        return PROPERTIES;
    }
}

let apiResponse;
class API {
    createApiUrl() {
        return 'https://mapas.test/api/opportunity/findOne';
    }

    async GET() {
        return {
            ok: true,
            async json() {
                return apiResponse;
            },
        };
    }
}

let component;
const context = {
    $TEMPLATES: { 'opportunity-appeal-phase-config': '' },
    Entity,
    API,
    Utils: {
        getTexts() {
            return () => '';
        },
    },
    app: {
        component(name, definition) {
            if (name === 'opportunity-appeal-phase-config') {
                component = definition;
            }
        },
    },
};

const source = fs.readFileSync(
    path.resolve(__dirname, '../../src/modules/OpportunityAppealPhase/components/opportunity-appeal-phase-config/script.js'),
    'utf8'
);
vm.runInNewContext(source, context);

async function initialize(metadata) {
    // O JSON das fases vem do PHP com o default aplicado; a API devolve o que está no banco
    const appealPhase = new Entity({
        id: 8418,
        allow_proponent_response: false,
        showPreviousPhaseEvaluationDetails: true,
        appealPhaseAffectsSync: false,
    });
    apiResponse = { id: 8418, '@entityType': 'opportunity', ...metadata };

    const instance = Object.assign(component.data(), component.methods, {
        phase: { appealPhase },
    });
    await instance.initializeAppealPhase();

    return instance.entity;
}

test('uses the metadata default when the appeal phase has no row in the database', async () => {
    const entity = await initialize({
        allow_proponent_response: null,
        showPreviousPhaseEvaluationDetails: null,
        appealPhaseAffectsSync: null,
    });

    assert.equal(entity.showPreviousPhaseEvaluationDetails, true);
    assert.equal(entity.__originalValues.showPreviousPhaseEvaluationDetails, true);

    assert.equal(entity.appealPhaseAffectsSync, false);
    assert.equal(entity.__originalValues.appealPhaseAffectsSync, false);

    assert.equal(entity.allow_proponent_response, null);
    assert.equal(entity.__originalValues.allow_proponent_response, null);
});

test('keeps the stored value instead of the default', async () => {
    const entity = await initialize({
        allow_proponent_response: true,
        showPreviousPhaseEvaluationDetails: false,
        appealPhaseAffectsSync: true,
    });

    assert.equal(entity.showPreviousPhaseEvaluationDetails, false);
    assert.equal(entity.__originalValues.showPreviousPhaseEvaluationDetails, false);

    assert.equal(entity.appealPhaseAffectsSync, true);
    assert.equal(entity.allow_proponent_response, true);
});
