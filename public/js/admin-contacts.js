document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-contact-url]').forEach(function (row) {
        row.addEventListener('click', function (event) {
            if (!event.target.closest('a, button')) {
                window.location.href = row.dataset.contactUrl;
            }
        });
        row.addEventListener('keydown', function (event) {
            if (event.target === row && (event.key === 'Enter' || event.key === ' ')) {
                event.preventDefault();
                window.location.href = row.dataset.contactUrl;
            }
        });
    });

    var feedback = document.querySelector('.copy-feedback');
    document.querySelectorAll('[data-copy-target]').forEach(function (button) {
        button.addEventListener('click', async function () {
            var target = document.getElementById(button.dataset.copyTarget);
            var value = target.textContent.trim();

            try {
                var copied = false;
                if (navigator.clipboard && window.isSecureContext) {
                    try {
                        await navigator.clipboard.writeText(value);
                        copied = true;
                    } catch (error) {
                        copied = false;
                    }
                }
                if (!copied) {
                    var temporary = document.createElement('textarea');
                    temporary.value = value;
                    temporary.style.position = 'fixed';
                    temporary.style.opacity = '0';
                    document.body.appendChild(temporary);
                    temporary.select();
                    copied = document.execCommand('copy');
                    temporary.remove();
                    if (!copied) throw new Error('Copy failed');
                }

                button.textContent = 'Copiato ✓';
                if (feedback) feedback.textContent = 'Copiato negli appunti.';
                window.setTimeout(function () { button.textContent = 'Copia'; }, 1800);
            } catch (error) {
                if (feedback) feedback.textContent = 'Copia non riuscita. Seleziona il testo e copialo manualmente.';
            }
        });
    });
});
