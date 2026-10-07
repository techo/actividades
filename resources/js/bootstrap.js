/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

window.axios = require('axios');

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Buffer circular de errores JS recientes, para adjuntarlos a los reportes de problemas
 * del widget "Reportar un problema" (ver components/backoffice/reportes/BugReporter.vue).
 * Convierte un "no me deja" en un stacktrace. Se guarda a lo sumo `MAX` entradas.
 */
(function () {
    var MAX = 15;
    var buffer = (window.__issueErrors = window.__issueErrors || []);
    function push(entry) {
        entry.at = new Date().toISOString();
        buffer.push(entry);
        if (buffer.length > MAX) buffer.shift();
    }
    window.addEventListener('error', function (e) {
        push({
            type: 'error',
            message: e.message,
            source: e.filename,
            line: e.lineno,
            col: e.colno,
            stack: e.error && e.error.stack ? String(e.error.stack).slice(0, 2000) : null,
        });
    });
    window.addEventListener('unhandledrejection', function (e) {
        var reason = e.reason;
        push({
            type: 'unhandledrejection',
            message: reason && reason.message ? reason.message : String(reason),
            stack: reason && reason.stack ? String(reason.stack).slice(0, 2000) : null,
        });
    });
})();

/**
 * Next we will register the CSRF Token as a common header with Axios so that
 * all outgoing HTTP requests automatically have it attached. This is just
 * a simple convenience so we don't have to attach every token manually.
 */

let token = document.head.querySelector('meta[name="csrf-token"]');

if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
}

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

// import Echo from 'laravel-echo'

// window.Pusher = require('pusher-js');

// window.Echo = new Echo({
//     broadcaster: 'pusher',
//     key: 'your-pusher-key',
//     cluster: 'mt1',
//     encrypted: true
// });
