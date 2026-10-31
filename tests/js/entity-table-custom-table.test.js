const assert = require('node:assert/strict');
const { readFileSync } = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const vm = require('node:vm');

let component;
const context = vm.createContext({
    app: { component: (name, definition) => { component = definition; } },
    $TEMPLATES: {},
    $MAPAS: { config: { entityTable: { sortOptions: [] } } },
    Entity: class {},
    McDate: class {},
    console,
});
vm.runInContext(readFileSync(path.join(__dirname, '../../src/modules/Entities/components/entity-table/script.js'), 'utf8'), context);

const columns = [
    { name: 'Nome', type: 'text' },
    { name: 'Quantidade', type: 'number' },
];

function render(value, configuredColumns = columns) {
    return component.methods.getEntityData.call({
        $description: {
            field_100: {
                type: 'custom-table',
                registrationFieldConfiguration: { config: { columns: configuredColumns } },
            },
        },
    }, { field_100: value }, 'field_100');
}

test('renders every table row with column names and preserves numeric zero', () => {
    assert.equal(render([
        { col0: 'Maria', col1: 0 },
        { col0: 'João', col1: 2 },
    ]), 'Nome: Maria | Quantidade: 0 || Nome: João | Quantidade: 2');
});

test('preserves string zero', () => {
    assert.equal(render([{ col0: 'Maria', col1: '0' }]), 'Nome: Maria | Quantidade: 0');
});

test('uses configured column order and ignores internal row properties', () => {
    assert.equal(render([{ col1: 3, col0: 'João', $$hashKey: 'internal' }]), 'Nome: João | Quantidade: 3');
});

test('keeps missing cells empty and skips empty placeholder rows', () => {
    assert.equal(render([{}, { col0: '', col1: null }, { col1: 0 }]), 'Nome:  | Quantidade: 0');
});

test('accepts serialized table values', () => {
    assert.equal(render(JSON.stringify([{ col0: 'Maria', col1: 0 }])), 'Nome: Maria | Quantidade: 0');
});

for (const [name, value] of Object.entries({ empty: [], null: null, missing: undefined, invalidJson: '{invalid', invalidShape: {} })) {
    test(`renders an empty cell for ${name} table values`, () => {
        assert.equal(render(value), '');
    });
}

test('does not expose row objects when column configuration is missing', () => {
    assert.equal(render([{ col0: 'Maria' }], []), '');
});

test('preserves values from every supported column type', () => {
    const configuredColumns = [
        { name: 'Nome', type: 'text' },
        { name: 'Valor', type: 'number' },
        { name: 'E-mail', type: 'email' },
        { name: 'CPF', type: 'cpf' },
        { name: 'Data', type: 'date' },
        { name: 'Categoria', type: 'select' },
    ];
    assert.equal(render([{
        col0: 'Ação cultural', col1: 12.5, col2: 'maria@example.org',
        col3: '001.002.003-04', col4: '2026-10-30', col5: 'Música',
    }], configuredColumns), 'Nome: Ação cultural | Valor: 12.5 | E-mail: maria@example.org | CPF: 001.002.003-04 | Data: 2026-10-30 | Categoria: Música');
});

test('escapes column names and cell values rendered through v-html', () => {
    assert.equal(render([{ col0: '<img src=x onerror=alert(1)> & "texto"' }], [
        { name: '<b>Nome</b> & apelido', type: 'text' },
    ]), '&lt;b&gt;Nome&lt;/b&gt; &amp; apelido: &lt;img src=x onerror=alert(1)&gt; &amp; &quot;texto&quot;');
});

test('keeps existing multiselect rendering', () => {
    const value = component.methods.getEntityData.call({
        $description: { field_200: { type: 'multiselect' } },
    }, { field_200: ['Música', 'Teatro'] }, 'field_200');
    assert.equal(value, 'Música, Teatro');
});
