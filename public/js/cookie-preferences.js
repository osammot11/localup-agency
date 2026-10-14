document.addEventListener('DOMContentLoaded', function () {
    var panel = document.getElementById('cookie-panel');

    document.querySelectorAll('.cookie-preferences-open').forEach(function (button) {
        button.addEventListener('click', function () {
            panel.hidden = false;
            panel.querySelector('button').focus();
        });
    });

    if (panel.hidden && !document.querySelector('script[src="https://connect.facebook.net/en_US/fbevents.js"]')) {
        ['_fbp', '_fbc'].forEach(function (name) {
            var hostname = window.location.hostname;
            var domains = ['', hostname, '.' + hostname];
            var parts = hostname.split('.');

            if (parts.length > 2) {
                domains.push('.' + parts.slice(-2).join('.'));
            }

            domains.forEach(function (domain) {
                document.cookie = name + '=; Max-Age=0; Path=/; SameSite=Lax' + (domain ? '; Domain=' + domain : '');
            });
        });
    }
});
