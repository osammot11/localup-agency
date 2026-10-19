document.addEventListener('DOMContentLoaded', function () {
    if (!document.querySelector('.form-message.error')) {
        return;
    }

    var placement = document.body.dataset.submittedForm === 'hero' ? 'modulo-hero' : 'modulo';
    var formSection = document.getElementById(placement);

    if (formSection) {
        formSection.scrollIntoView();
    }
});
