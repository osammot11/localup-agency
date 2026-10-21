document.addEventListener('DOMContentLoaded', function () {
    var forms = document.querySelectorAll('.trial-form-card form');
    var landingByPath = {
        '/social/ristoranti': {key: 'restaurants', source: 'social/ristoranti-v2'},
        '/social/beauty': {key: 'beauty', source: 'social/beauty-v2'},
        '/social/antiquariato': {key: 'antiques', source: 'social/antiquariato-v2'},
        '/social/pasticcerie': {key: 'pastry', source: 'social/pasticcerie-v2'},
        '/siti': {key: 'sites', source: 'siti-v2'}
    };
    var landing = landingByPath[window.location.pathname.replace(/\/$/, '')];
    var storageKey = landing ? 'localup-form-started:' + landing.key : null;
    var attempt = null;
    var capiInFlight = {started: false, step2: false};

    function saveAttempt() {
        if (!storageKey) {
            return;
        }

        try {
            window.sessionStorage.setItem(storageKey, JSON.stringify(attempt));
        } catch (error) {
            // The in-memory attempt still prevents duplicate events on this page.
        }
    }

    if (storageKey) {
        try {
            if (new URLSearchParams(window.location.search).get('success') === '1') {
                window.sessionStorage.removeItem(storageKey);
            } else {
                attempt = JSON.parse(window.sessionStorage.getItem(storageKey));
            }
        } catch (error) {
            attempt = null;
        }

        if (attempt && (!attempt.id || !attempt.startedAt || Date.now() - attempt.startedAt >= 30 * 60 * 1000)) {
            attempt = null;
        }
    }

    function sendTrackingToServer(form, eventId, url, confirmedKey, inFlightKey) {
        if (attempt[confirmedKey] || capiInFlight[inFlightKey]) {
            return;
        }

        var token = form.querySelector('input[name="_token"]');

        if (!token) {
            return;
        }

        var currentAttempt = attempt;
        capiInFlight[inFlightKey] = true;

        try {
            fetch(url, {
                method: 'POST',
                credentials: 'same-origin',
                keepalive: true,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token.value
                },
                body: JSON.stringify({event_id: eventId, landing: landing.key})
            }).then(function (response) {
                if (response.ok && attempt === currentAttempt) {
                    attempt[confirmedKey] = true;
                    saveAttempt();
                }
            }).catch(function () {
                // A later input retries the same event ID without firing Pixel again.
            }).finally(function () {
                capiInFlight[inFlightKey] = false;
            });
        } catch (error) {
            capiInFlight[inFlightKey] = false;
        }
    }

    function ensureStartedAttempt(form) {
        if (!landing || !window.localupMetaFormStartUrl || typeof window.fbq !== 'function') {
            return false;
        }

        if (attempt && Date.now() - attempt.startedAt >= 30 * 60 * 1000) {
            attempt = null;
        }

        if (!attempt) {
            if (!window.crypto || typeof window.crypto.randomUUID !== 'function') {
                return false;
            }

            attempt = {id: window.crypto.randomUUID(), startedAt: Date.now(), serverConfirmed: false};
            saveAttempt();
            try {
                window.fbq('trackCustom', 'FormStarted', {content_name: landing.source}, {eventID: attempt.id});
            } catch (error) {
                // Pixel errors must not interrupt typing in the form.
            }
        }

        sendTrackingToServer(form, attempt.id, window.localupMetaFormStartUrl, 'serverConfirmed', 'started');

        return true;
    }

    function trackFormStarted(event) {
        var field = event.target;

        if (!field.closest('[data-step="1"]') || !field.value || !field.value.trim()) {
            return;
        }

        ensureStartedAttempt(field.form);
    }

    function trackStepTwo(form) {
        if (!window.localupMetaFormStep2Url || !ensureStartedAttempt(form)) {
            return;
        }

        if (!attempt.step2Id) {
            attempt.step2Id = window.crypto.randomUUID();
            attempt.step2ServerConfirmed = false;
            saveAttempt();

            try {
                window.fbq('trackCustom', 'FormStep2Reached', {content_name: landing.source}, {eventID: attempt.step2Id});
            } catch (error) {
                // Pixel errors must not interrupt the form transition.
            }
        }

        sendTrackingToServer(form, attempt.step2Id, window.localupMetaFormStep2Url, 'step2ServerConfirmed', 'step2');
    }

    if (!forms.length) {
        return;
    }

    forms.forEach(function (form) {
        form.addEventListener('input', trackFormStarted);
        form.addEventListener('change', trackFormStarted);

        var firstStep = form.querySelector('[data-step="1"]');
        var secondStep = form.querySelector('[data-step="2"]');
        var currentStep = form.querySelector('[data-current-step]');
        var progressBar = form.querySelector('[data-progress-bar]');
        var nextButton = form.querySelector('[data-next-step]');
        var previousButton = form.querySelector('[data-prev-step]');

        function showStep(step, focusField) {
            firstStep.hidden = step !== 1;
            secondStep.hidden = step !== 2;
            currentStep.textContent = String(step);
            progressBar.style.width = step === 1 ? '50%' : '100%';

            if (focusField) {
                (step === 1 ? firstStep : secondStep).querySelector('input:not([tabindex="-1"])').focus();
            }
        }

        function continueToContacts() {
            var invalidField = Array.from(firstStep.querySelectorAll('input')).find(function (field) {
                return !field.checkValidity();
            });

            if (invalidField) {
                invalidField.reportValidity();
                return;
            }

            showStep(2, true);
            trackStepTwo(form);
        }

        nextButton.addEventListener('click', continueToContacts);
        previousButton.addEventListener('click', function () {
            showStep(1, true);
        });
        firstStep.addEventListener('keydown', function (event) {
            if (event.key === 'Enter' && event.target.tagName === 'INPUT') {
                event.preventDefault();
                continueToContacts();
            }
        });

        var hasErrors = form.dataset.hasErrors === 'true';
        var isSubmittedForm = form.dataset.formPlacement === (document.body.dataset.submittedForm || 'bottom');
        var errorStep = secondStep.querySelector('[aria-invalid="true"]') ? 2 : 1;
        showStep(hasErrors && isSubmittedForm ? errorStep : 1, hasErrors && isSubmittedForm);

        if (hasErrors && isSubmittedForm) {
            form.scrollIntoView();
        }
    });
});
