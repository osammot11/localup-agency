document.addEventListener('DOMContentLoaded', function () {
    var hero = document.querySelector('.trial-hero-copy, .hero .narrow');
    var firstCallToAction = hero && hero.querySelector('a[href="#modulo"].trial-button, a[href="#modulo"].button');
    var formSection = document.getElementById('modulo');
    var source = formSection && formSection.querySelector('.trial-form-card, .lead-form');

    if (!firstCallToAction || !source) {
        return;
    }

    var wrapper = document.createElement('div');
    wrapper.className = source.classList.contains('trial-form-card') ? 'trial-hero-inline-form' : 'hero-inline-form';
    wrapper.id = firstCallToAction.id;
    wrapper.setAttribute('role', 'region');
    wrapper.setAttribute('aria-label', 'Modulo di richiesta');

    var note = firstCallToAction.nextElementSibling;

    if (note && note.classList.contains('trial-under-cta')) {
        wrapper.appendChild(note);
    }

    if (source.classList.contains('lead-form')) {
        var message = formSection.querySelector('.form-message.success');

        if (!message && document.body.dataset.submittedForm === 'hero') {
            message = formSection.querySelector('.form-message.error');
        }

        if (message) {
            wrapper.appendChild(message.cloneNode(true));
        }
    }

    var copy = source.cloneNode(true);

    copy.querySelectorAll('[id]').forEach(function (element) {
        element.id = 'hero-' + element.id;
    });
    if (copy.id) {
        copy.id = 'hero-' + copy.id;
    }
    copy.querySelectorAll('[for]').forEach(function (element) {
        element.htmlFor = 'hero-' + element.htmlFor;
    });
    copy.querySelectorAll('[aria-describedby]').forEach(function (element) {
        element.setAttribute('aria-describedby', element.getAttribute('aria-describedby').split(' ').map(function (id) {
            return 'hero-' + id;
        }).join(' '));
    });

    var sourceForm = source.matches('form') ? source : source.querySelector('form');
    var heroForm = copy.matches('form') ? copy : copy.querySelector('form');

    if (sourceForm && heroForm) {
        addPlacement(sourceForm, 'bottom');
        addPlacement(heroForm, 'hero');
    }

    wrapper.appendChild(copy);
    firstCallToAction.replaceWith(wrapper);

    if (document.body.dataset.submittedForm === 'hero' && formSection.querySelector('.form-message.error')) {
        wrapper.scrollIntoView();
    } else if (document.body.dataset.submittedForm === 'bottom' && formSection.querySelector('.form-message.error')) {
        formSection.scrollIntoView();
    }

    function addPlacement(form, placement) {
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = '_form_placement';
        input.value = placement;
        form.dataset.formPlacement = placement;
        form.appendChild(input);
    }
});
