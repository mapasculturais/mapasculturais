'use strict';

const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const vm = require('node:vm');

const projectRoot = path.resolve(__dirname, '../..');
const entitySourcePath = path.join(
    projectRoot,
    'src/modules/Components/assets/js/components-base/Entity.js'
);
const messagesSourcePath = path.join(
    projectRoot,
    'src/modules/Components/components/mc-messages/script.js'
);
const messagesTemplatePath = path.join(
    projectRoot,
    'src/modules/Components/components/mc-messages/template.php'
);

function loadEntityClass() {
    const context = { console };
    context.globalThis = context;
    vm.createContext(context);

    const source = `${fs.readFileSync(entitySourcePath, 'utf8')}\nglobalThis.TestEntity = Entity;`;
    vm.runInContext(source, context, { filename: entitySourcePath });

    return context.TestEntity;
}

function createEntityDouble(Entity) {
    const entity = Object.create(Entity.prototype);
    entity.__processing = false;
    entity.__validationErrors = {};
    entity.__validationMessages = [];
    entity.sentMessages = [];
    entity.text = (key) => ({
        'erro de validacao': 'Corrija os erros indicados',
        'erro inesperado': 'Houve um erro inesperado',
        'nao foi possivel salvar': 'Não foi possível salvar:',
    })[key] || key;
    entity.sendMessage = function (message, type = 'success') {
        this.sentMessages.push({ message, type });
    };

    return entity;
}

function loadMessagesStore() {
    const scheduledTimers = [];
    const context = {
        console,
        setTimeout(callback, delay) {
            scheduledTimers.push({ callback, delay });
            return scheduledTimers.length;
        },
        Pinia: {
            defineStore(_id, definition) {
                return () => {
                    const store = definition.state();

                    for (const [name, action] of Object.entries(definition.actions)) {
                        store[name] = action.bind(store);
                    }

                    for (const [name, getter] of Object.entries(definition.getters)) {
                        Object.defineProperty(store, name, {
                            get: () => getter.call(store, store),
                        });
                    }

                    return store;
                };
            },
        },
        app: { component() {} },
        $TEMPLATES: { 'mc-messages': '' },
    };
    context.globalThis = context;
    vm.createContext(context);

    const source = `${fs.readFileSync(messagesSourcePath, 'utf8')}\nglobalThis.testUseMessages = useMessages;`;
    vm.runInContext(source, context, { filename: messagesSourcePath });

    return {
        scheduledTimers,
        store: context.testUseMessages(),
    };
}

test('normalizes validation strings from nested arrays and objects', () => {
    const Entity = loadEntityClass();
    const errors = {
        name: [' O nome é obrigatório. ', 'O nome é obrigatório.'],
        customMetadata: {
            configuration: [
                'A configuração está incompleta.',
                { support: 'Entre em contato com o suporte.' },
            ],
        },
        type: 'O tipo é obrigatório.',
    };

    const messages = Entity.normalizeValidationMessages(errors);

    assert.deepEqual(Array.from(messages), [
        'O nome é obrigatório.',
        'A configuração está incompleta.',
        'Entre em contato com o suporte.',
        'O tipo é obrigatório.',
    ]);
});

test('ignores empty and non-string scalar validation values', () => {
    const Entity = loadEntityClass();

    const messages = Entity.normalizeValidationMessages({
        empty: ['', '   ', null, undefined],
        scalar: [false, true, 0, 42],
        valid: 'Mensagem válida.',
    });

    assert.deepEqual(Array.from(messages), ['Mensagem válida.']);
});

test('preserves field errors and sends a structured persistent summary', () => {
    const Entity = loadEntityClass();
    const entity = createEntityDouble(Entity);
    const validationErrors = {
        visibleField: ['O campo visível é obrigatório.'],
        hiddenTechnicalProperty: ['A configuração vinculada está incompleta.'],
    };

    entity.catchErrors(
        { status: 400 },
        { error: true, data: validationErrors }
    );

    assert.equal(entity.__validationErrors, validationErrors);
    assert.deepEqual(Array.from(entity.__validationMessages), [
        'O campo visível é obrigatório.',
        'A configuração vinculada está incompleta.',
    ]);
    assert.equal(entity.sentMessages.length, 1);
    assert.equal(entity.sentMessages[0].type, 'error');
    assert.deepEqual(JSON.parse(JSON.stringify(entity.sentMessages[0].message)), {
        title: 'Não foi possível salvar:',
        messages: [
            'O campo visível é obrigatório.',
            'A configuração vinculada está incompleta.',
        ],
        persistent: true,
    });
    assert.doesNotMatch(
        JSON.stringify(entity.sentMessages[0].message),
        /hiddenTechnicalProperty/
    );
});

test('falls back to the generic validation message when no text can be normalized', () => {
    const Entity = loadEntityClass();
    const entity = createEntityDouble(Entity);

    entity.catchErrors(
        { status: 400 },
        { error: true, data: { invalid: [null, false, 10] } }
    );

    assert.deepEqual(Array.from(entity.__validationMessages), []);
    assert.deepEqual(entity.sentMessages, [{
        message: 'Corrija os erros indicados',
        type: 'error',
    }]);
});

test('clears grouped and normalized validation errors together', () => {
    const Entity = loadEntityClass();
    const entity = createEntityDouble(Entity);
    entity.__validationErrors = { name: ['O nome é obrigatório.'] };
    entity.__validationMessages = ['O nome é obrigatório.'];

    entity.cleanErrors();

    assert.deepEqual(JSON.parse(JSON.stringify(entity.__validationErrors)), {});
    assert.deepEqual(Array.from(entity.__validationMessages), []);
});

test('does not turn a processed validation rejection into an unexpected error', async () => {
    const Entity = loadEntityClass();
    const entity = createEntityDouble(Entity);
    const backendPayload = {
        error: true,
        data: { hidden: ['Mensagem específica do backend.'] },
    };
    const response = new Response(JSON.stringify(backendPayload), {
        status: 400,
        headers: { 'Content-Type': 'application/json' },
    });

    let processedError;
    await assert.rejects(
        entity.doPromise(response, (data) => data),
        (error) => {
            processedError = error;
            return error.data.hidden[0] === 'Mensagem específica do backend.';
        }
    );

    const messagesBeforeCatch = entity.sentMessages.length;
    await assert.rejects(
        entity.doCatch(processedError),
        (error) => error === processedError
    );

    assert.equal(entity.sentMessages.length, messagesBeforeCatch);
    assert.doesNotMatch(
        JSON.stringify(entity.sentMessages),
        /Houve um erro inesperado/
    );
});

test('keeps the unexpected error treatment for runtime failures', async () => {
    const Entity = loadEntityClass();
    const entity = createEntityDouble(Entity);
    const runtimeError = new Error('Network parser failed');

    await assert.rejects(
        entity.doCatch(runtimeError),
        (error) => error.exception === runtimeError
    );

    assert.deepEqual(entity.sentMessages, [{
        message: 'Houve um erro inesperado',
        type: 'error',
    }]);
});

test('keeps structured persistent errors until explicit dismissal', () => {
    const { scheduledTimers, store } = loadMessagesStore();

    store.error({
        title: 'Não foi possível salvar:',
        messages: ['Mensagem A.', 'Mensagem B.'],
        persistent: true,
    });

    assert.equal(scheduledTimers.length, 0);
    assert.deepEqual(JSON.parse(JSON.stringify(store.messages[0])), {
        active: true,
        type: 'error',
        title: 'Não foi possível salvar:',
        messages: ['Mensagem A.', 'Mensagem B.'],
        persistent: true,
    });

    const [message] = store.messages;
    store.dismiss(message);
    assert.equal(store.messages.length, 0);
});

test('keeps timeout behavior for legacy string messages', () => {
    const { scheduledTimers, store } = loadMessagesStore();

    store.error('Falha temporária.');

    assert.equal(scheduledTimers.length, 1);
    assert.equal(scheduledTimers[0].delay, 3000);
    assert.equal(store.messages[0].text, 'Falha temporária.');

    scheduledTimers[0].callback();
    assert.equal(store.messages.length, 0);
});

test('renders the validation summary as an accessible alert with escaped list items', () => {
    const template = fs.readFileSync(messagesTemplatePath, 'utf8');

    assert.match(template, /role="alert"/);
    assert.match(template, /aria-live="assertive"/);
    assert.match(template, /<ul[^>]*>/);
    assert.match(template, /\{\{\s*message\.title\s*\}\}/);
    assert.match(template, /\{\{\s*validationMessage\s*\}\}/);
    assert.match(template, /<button[^>]*aria-label=/);
});
