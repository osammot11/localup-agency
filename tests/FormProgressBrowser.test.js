import assert from 'node:assert/strict';
import {readFileSync} from 'node:fs';
import {test} from 'node:test';
import {runInNewContext} from 'node:vm';

const script = readFileSync(new URL('../public/js/restaurants-trial.js', import.meta.url), 'utf8');
const paths = ['/social/ristoranti', '/social/beauty', '/social/antiquariato', '/social/pasticcerie', '/siti'];

function createForm(placement) {
    const listeners = {};
    let firstStepValid = true;
    let validityReports = 0;
    const firstStep = {
        hidden: false,
        querySelectorAll: () => [{checkValidity: () => firstStepValid, reportValidity: () => validityReports++}],
        querySelector: () => ({focus() {}}),
        addEventListener(name, callback) { listeners[`step:${name}`] = callback; }
    };
    const secondStep = {hidden: true, querySelector: selector => selector === '[aria-invalid="true"]' ? null : {focus() {}}};
    const currentStep = {textContent: ''};
    const progressBar = {style: {width: ''}};
    const nextButton = {addEventListener(name, callback) { listeners[`next:${name}`] = callback; }};
    const previousButton = {addEventListener() {}};
    const form = {
        dataset: {hasErrors: 'false', formPlacement: placement},
        addEventListener(name, callback) { listeners[name] = callback; },
        querySelector(selector) {
            return {
                '[data-step="1"]': firstStep,
                '[data-step="2"]': secondStep,
                '[data-current-step]': currentStep,
                '[data-progress-bar]': progressBar,
                '[data-next-step]': nextButton,
                '[data-prev-step]': previousButton,
                'input[name="_token"]': {value: 'test-csrf'}
            }[selector];
        }
    };

    function fire(name, value) {
        listeners[name]?.({target: {
            form,
            value,
            closest: () => firstStep
        }});
    }

    return {
        form,
        fire,
        advance: () => listeners['next:click'](),
        advanceWithEnter: () => listeners['step:keydown']({key: 'Enter', target: {tagName: 'INPUT'}, preventDefault() {}}),
        setFirstStepValid: value => { firstStepValid = value; },
        reports: () => validityReports,
        secondStep
    };
}

function loadPage(path, storage, state) {
    const forms = [createForm('hero'), createForm('bottom')];
    const pixelEvents = [];
    const requests = [];
    state.sequence ||= 0;
    const context = {
        document: {
            addEventListener(name, callback) {
                if (name === 'DOMContentLoaded') {
                    callback();
                }
            },
            querySelectorAll: () => forms.map(({form}) => form),
            body: {dataset: {}}
        },
        window: {
            location: {pathname: path, search: state.search || ''},
            sessionStorage: storage,
            localupMetaFormStartUrl: '/tracking/form-start',
            localupMetaFormStep2Url: '/tracking/form-step-2',
            crypto: {randomUUID: () => `00000000-0000-4000-8000-${String(++state.sequence).padStart(12, '0')}`},
            fbq: (...args) => pixelEvents.push(args)
        },
        Date: {now: () => state.now},
        URLSearchParams,
        fetch: (url, options) => {
            if (state.fetchThrows || (state.failStep2 && url === '/tracking/form-step-2')) {
                throw new Error('network unavailable');
            }
            requests.push({url, options});
            return Promise.resolve({ok: true});
        }
    };

    runInNewContext(script, context);

    return {forms, pixelEvents, requests};
}

test('a tracking network error does not interrupt the form and retries with the same event ID', () => {
    const values = new Map();
    const storage = {
        getItem: key => values.get(key) || null,
        setItem: (key, value) => values.set(key, value),
        removeItem: key => values.delete(key)
    };
    const state = {now: 1_000_000, fetchThrows: true};
    const page = loadPage('/social/ristoranti', storage, state);

    assert.doesNotThrow(() => page.forms[0].fire('input', 'Ristorante'));
    assert.equal(page.pixelEvents.length, 1);
    assert.equal(page.requests.length, 0);

    state.fetchThrows = false;
    page.forms[0].fire('input', 'Ristorante nuovo');
    assert.equal(page.pixelEvents.length, 1);
    assert.equal(page.requests.length, 1);
    assert.equal(page.pixelEvents[0][3].eventID, JSON.parse(page.requests[0].options.body).event_id);
});

test('a step 2 network error does not block progression and retries without another Pixel event', async () => {
    const values = new Map();
    const storage = {
        getItem: key => values.get(key) || null,
        setItem: (key, value) => values.set(key, value),
        removeItem: key => values.delete(key)
    };
    const state = {now: 1_000_000, failStep2: true};
    const page = loadPage('/social/ristoranti', storage, state);

    page.forms[0].fire('input', 'Ristorante');
    assert.doesNotThrow(() => page.forms[0].advance());
    assert.equal(page.forms[0].secondStep.hidden, false);
    assert.deepEqual(page.pixelEvents.map(event => event[1]), ['FormStarted', 'FormStep2Reached']);

    state.failStep2 = false;
    await new Promise(resolve => setImmediate(resolve));
    page.forms[0].advance();
    assert.equal(page.pixelEvents.length, 2);
    assert.equal(page.requests.filter(request => request.url === '/tracking/form-step-2').length, 1);
    assert.equal(page.pixelEvents[1][3].eventID, JSON.parse(page.requests[1].options.body).event_id);
});

test('pressing Enter on step 1 tracks the valid transition', () => {
    const values = new Map();
    const storage = {
        getItem: key => values.get(key) || null,
        setItem: (key, value) => values.set(key, value),
        removeItem: key => values.delete(key)
    };
    const page = loadPage('/siti', storage, {now: 1_000_000});

    page.forms[0].fire('input', 'Attività');
    page.forms[0].advanceWithEnter();

    assert.equal(page.forms[0].secondStep.hidden, false);
    assert.deepEqual(page.pixelEvents.map(event => event[1]), ['FormStarted', 'FormStep2Reached']);
});

for (const path of paths) {
    test(`${path}: valid transition to step 2 is tracked once per attempt`, async () => {
        const values = new Map();
        const storage = {
            getItem: key => values.get(key) || null,
            setItem: (key, value) => values.set(key, value),
            removeItem: key => values.delete(key)
        };
        const state = {now: 1_000_000};
        const page = loadPage(path, storage, state);

        page.forms[0].setFirstStepValid(false);
        page.forms[0].advance();
        assert.equal(page.forms[0].reports(), 1);
        assert.equal(page.forms[0].secondStep.hidden, true);
        assert.equal(page.pixelEvents.length, 0);
        assert.equal(page.requests.length, 0);

        page.forms[0].setFirstStepValid(true);
        page.forms[0].fire('input', 'Attività');
        page.forms[0].advance();
        await new Promise(resolve => setImmediate(resolve));
        assert.equal(page.forms[0].secondStep.hidden, false);
        assert.deepEqual(page.pixelEvents.map(event => event[1]), ['FormStarted', 'FormStep2Reached']);
        assert.equal(page.requests.length, 2);
        assert.equal(page.requests[1].url, '/tracking/form-step-2');
        assert.equal(page.pixelEvents[1][3].eventID, JSON.parse(page.requests[1].options.body).event_id);
        assert.notEqual(page.pixelEvents[0][3].eventID, page.pixelEvents[1][3].eventID);
        assert.deepEqual(Object.keys(JSON.parse(page.requests[1].options.body)).sort(), ['event_id', 'landing']);

        page.forms[0].advance();
        page.forms[1].advance();
        assert.equal(page.pixelEvents.length, 2);
        assert.equal(page.requests.length, 2);

        const afterValidation = loadPage(path, storage, state);
        afterValidation.forms[1].advance();
        assert.equal(afterValidation.pixelEvents.length, 0);
        assert.equal(afterValidation.requests.length, 0);

        state.now += 30 * 60 * 1000;
        afterValidation.forms[1].advance();
        assert.deepEqual(afterValidation.pixelEvents.map(event => event[1]), ['FormStarted', 'FormStep2Reached']);
        assert.equal(afterValidation.requests.length, 2);

        state.search = '?success=1';
        const afterSuccess = loadPage(path, storage, state);
        afterSuccess.forms[0].advance();
        assert.deepEqual(afterSuccess.pixelEvents.map(event => event[1]), ['FormStarted', 'FormStep2Reached']);
        assert.equal(afterSuccess.requests.length, 2);
    });

    test(`${path}: first value starts one shared Pixel and CAPI event`, async () => {
        const values = new Map();
        const storage = {
            getItem: key => values.get(key) || null,
            setItem: (key, value) => values.set(key, value),
            removeItem: key => values.delete(key)
        };
        const state = {now: 1_000_000};
        const first = loadPage(path, storage, state);

        first.forms[0].fire('focus', 'Ristorante');
        first.forms[0].fire('input', '   ');
        assert.equal(first.pixelEvents.length, 0);
        assert.equal(first.requests.length, 0);

        first.forms[0].fire('input', 'Ristorante');
        await Promise.resolve();
        first.forms[1].fire('change', 'Firenze');
        assert.equal(first.pixelEvents.length, 1);
        assert.equal(first.requests.length, 1);
        assert.equal(first.pixelEvents[0][0], 'trackCustom');
        assert.equal(first.pixelEvents[0][1], 'FormStarted');
        assert.equal(first.pixelEvents[0][3].eventID, JSON.parse(first.requests[0].options.body).event_id);
        assert.deepEqual(Object.keys(JSON.parse(first.requests[0].options.body)).sort(), ['event_id', 'landing']);

        const afterValidation = loadPage(path, storage, state);
        afterValidation.forms[1].fire('input', 'Altra attività');
        assert.equal(afterValidation.pixelEvents.length, 0);
        assert.equal(afterValidation.requests.length, 0);

        state.now += 30 * 60 * 1000;
        afterValidation.forms[1].fire('input', 'Nuovo tentativo');
        assert.equal(afterValidation.pixelEvents.length, 1);
        assert.equal(afterValidation.requests.length, 1);

        state.search = '?success=1';
        const afterSuccess = loadPage(path, storage, state);
        afterSuccess.forms[0].fire('input', 'Ancora');
        assert.equal(afterSuccess.pixelEvents.length, 1);
        assert.equal(afterSuccess.requests.length, 1);
    });
}
