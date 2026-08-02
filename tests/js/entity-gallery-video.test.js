const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const vm = require('node:vm');

let component;
const context = {
    $TEMPLATES: { 'entity-gallery-video': '' },
    Entity: function Entity() {},
    URL,
    Utils: {
        createUrl() {
            return new URL('https://mapas.test/site/videoThumbnail');
        },
        getTexts() {
            return () => '';
        },
    },
    __() {
        return '';
    },
    app: {
        component(name, definition) {
            if (name === 'entity-gallery-video') {
                component = definition;
            }
        },
    },
    console,
    fetch: async () => {
        throw new Error('Unexpected fetch');
    },
};

const source = fs.readFileSync(
    path.resolve(__dirname, '../../src/modules/Entities/components/entity-gallery-video/script.js'),
    'utf8'
);
vm.runInNewContext(source, context);

function createInstance() {
    return Object.assign(component.data(), component.methods);
}

test('hydrates a TikTok thumbnail once', async () => {
    const requests = [];
    context.fetch = async (endpoint) => {
        requests.push(endpoint.toString());
        return {
            ok: true,
            async json() {
                return { thumbnailUrl: 'https://cdn.tiktok.com/cover.jpg' };
            },
        };
    };

    const instance = createInstance();
    const url = 'https://www.tiktok.com/@creator/video/7123456789012345678';
    const data = instance.getVideoBasicData(url);

    assert.equal(data.provider, 'tiktok');
    await new Promise(setImmediate);
    assert.equal(data.thumbnail, 'https://cdn.tiktok.com/cover.jpg');
    assert.equal(instance.getVideoBasicData(url), data);
    assert.equal(requests.length, 1);
    assert.match(requests[0], /videoThumbnail.*url=/);
});

test('keeps Instagram blank when no thumbnail is available', async () => {
    context.fetch = async () => ({
        ok: true,
        async json() {
            return { thumbnailUrl: null };
        },
    });

    const instance = createInstance();
    const data = instance.getVideoBasicData('https://www.instagram.com/reel/Reel123/');

    assert.equal(data.provider, 'instagram');
    await new Promise(setImmediate);
    assert.equal(data.thumbnail, '');
});

test('does not request thumbnails from unlisted provider subdomains', async () => {
    let requests = 0;
    context.fetch = async () => {
        requests++;
        return { ok: true, async json() { return { thumbnailUrl: null }; } };
    };

    const instance = createInstance();
    const instagram = instance.getVideoBasicData('https://unlisted.instagram.com/reel/Reel123/');
    const tiktok = instance.getVideoBasicData('https://unlisted.tiktok.com/@creator/video/123');

    await new Promise(setImmediate);
    assert.equal(instagram.provider, '');
    assert.equal(tiktok.provider, '');
    assert.equal(requests, 0);
});

test('preserves YouTube and Vimeo thumbnail behavior', () => {
    const instance = createInstance();

    assert.equal(
        instance.getVideoBasicData('https://www.youtube.com/watch?v=abcdefghijk').thumbnail,
        'https://img.youtube.com/vi/abcdefghijk/0.jpg'
    );
    assert.equal(
        instance.getVideoBasicData('https://vimeo.com/123456789').thumbnail,
        'https://vumbnail.com/123456789.jpg'
    );
});
