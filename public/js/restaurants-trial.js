document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('trial-lead-form');

    if (!form) {
        return;
    }

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

    var errorStep = secondStep.querySelector('[aria-invalid="true"]') ? 2 : 1;
    showStep(form.dataset.hasErrors === 'true' ? errorStep : 1, form.dataset.hasErrors === 'true');

    if (form.dataset.hasErrors === 'true') {
        document.getElementById('modulo').scrollIntoView();
    }
});
