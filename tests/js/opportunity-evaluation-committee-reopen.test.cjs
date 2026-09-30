const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

function createComponent(pages, postResponse = { ok: true, body: { reopened: 1 } }) {
    const requests = { get: [], post: [] };
    let definition;

    class API {
        createUrl(action) {
            return new URL(`http://localhost/${action}`);
        }

        async GET(url) {
            requests.get.push({ action: url.pathname.slice(1), args: Object.fromEntries(url.searchParams) });
            const page = await pages.shift();
            return {
                ok: true,
                json: async () => page,
            };
        }

        async POST(url, body) {
            requests.post.push({ url, body });
            return { ok: postResponse.ok, json: async () => postResponse.body };
        }
    }

    const source = fs.readFileSync(path.resolve(__dirname, '../../src/modules/Opportunities/components/opportunity-evaluation-committee/script.js'), 'utf8');
    vm.runInNewContext(source, {
        app: { component: (_name, component) => { definition = component; } },
        $TEMPLATES: { 'opportunity-evaluation-committee': '' },
        Entity: class Entity {},
        API,
        Utils: { createUrl: (_controller, action) => action },
    });

    const success = [];
    const errors = [];
    const warnings = [];
    const state = {
        ...definition.data.call({ entity: { opportunity: { id: 77 } } }),
        entity: { opportunity: { id: 77 } },
        messages: { success: message => success.push(message), error: message => errors.push(message), warning: message => warnings.push(message) },
        text: key => key,
        loadReviewers: async () => {},
    };
    for (const [name, method] of Object.entries(definition.methods)) {
        state[name] = method.bind(state);
    }
    state.loadReviewers = async () => {};

    return { state, requests, success, errors, warnings };
}

const reviewer = { agentUserId: 9, agent: { name: 'Avaliadora' }, metadata: { summary: { sent: 3 } } };

test('modal loads only sent evaluations for the chosen reviewer and keeps selections across pages', async () => {
    const { state, requests } = createComponent([
        { total: 2, evaluations: [{ id: 11, registrationNumber: 'A-101' }], nextCursor: 11 },
        { total: 2, evaluations: [{ id: 12, registrationNumber: 'A-102' }], nextCursor: null },
    ]);

    await state.openReopenModal(reviewer);
    assert.equal(requests.get[0].action, 'reopenableEvaluations');
    assert.equal(requests.get[0].args.opportunityId, '77');
    assert.equal(requests.get[0].args.uid, '9');
    assert.equal('@filterStatus' in requests.get[0].args, false);

    state.selectedEvaluationIds = [11];
    await state.loadMoreSentEvaluations();
    assert.equal(requests.get[1].args.afterId, '11');
    assert.deepEqual(Array.from(state.selectedEvaluationIds), [11]);
    assert.deepEqual(Array.from(state.sentEvaluations, item => item.id), [11, 12]);
});

test('selected action sends only explicitly selected IDs', async () => {
    const { state, requests, success } = createComponent([]);
    state.activeReopenReviewer = reviewer;
    state.selectedEvaluationIds = [11, 12];
    let closed = false;

    await state.reopenSelectedEvaluations({ close: () => { closed = true; } });

    assert.equal(requests.post[0].url, 'reopenSelectedEvaluations');
    assert.deepEqual(Array.from(requests.post[0].body.evaluationIds), [11, 12]);
    assert.equal(requests.post[0].body.uid, 9);
    assert.equal(closed, true);
    assert.equal(success.length, 1);
});

test('all action uses the server bulk route and is not limited to loaded pages', async () => {
    const { state, requests } = createComponent([]);
    state.activeReopenReviewer = reviewer;
    state.sentEvaluations = [{ id: 11 }];

    await state.reopenAllEvaluations({ close: () => {} });

    assert.equal(requests.post[0].url, 'reopenEvaluations');
    assert.equal('evaluationIds' in requests.post[0].body, false);
    assert.equal(requests.post[0].body.uid, 9);
});

test('opening another reviewer while a page is loading shows only the new reviewers evaluations', async () => {
    let releaseFirstPage;
    const firstPage = new Promise(resolve => { releaseFirstPage = resolve; });
    const otherReviewer = { ...reviewer, agentUserId: 10 };
    const { state, requests } = createComponent([
        firstPage,
        { total: 1, evaluations: [{ id: 22, registrationNumber: 'B-22' }], nextCursor: null },
    ]);

    const first = state.openReopenModal(reviewer);
    const second = state.openReopenModal(otherReviewer);
    assert.equal(requests.get.length, 2);

    releaseFirstPage({ total: 1, evaluations: [{ id: 11, registrationNumber: 'A-11' }], nextCursor: null });
    await Promise.all([first, second]);
    assert.deepEqual(Array.from(state.sentEvaluations, item => item.id), [22]);
});

test('successful reopening stays successful when refreshing the reviewer card fails', async () => {
    const { state, success, errors, warnings } = createComponent([]);
    state.activeReopenReviewer = reviewer;
    state.selectedEvaluationIds = [11];
    state.loadReviewers = async () => { throw new Error('refresh failed'); };
    let closed = false;

    await state.reopenSelectedEvaluations({ close: () => { closed = true; } });

    assert.equal(closed, true);
    assert.equal(success.length, 1);
    assert.equal(errors.length, 0);
    assert.deepEqual(warnings, ['reopenRefreshError']);
});
