import Echo from "laravel-echo";


window._ = require('lodash');

/**
 * We'll load jQuery and the Bootstrap jQuery plugin which provides support
 * for JavaScript based Bootstrap features such as modals and tabs. This
 * code may be modified to fit the specific needs of your application.
 */

try {
    window.$ = window.jQuery = require('jquery');
    // redifine console behavior if needed
    /*
    console = (function (oldCons) {
        return {
            log: function () {
                oldCons.log(...arguments);
            },
            info: function () {
                oldCons.info(...arguments);
            },
            warn: function () {
                oldCons.warn(...arguments);
            },
            error: function () {
                oldCons.error(...arguments);
            }
        };
    }(console));
    */
    

    // require('bootstrap-sass');
    require('materialize-css');
} catch (e) {}

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */


window.Vue = require('vue');


window.axios = require('axios');
window.Pusher = require('pusher-js');

// todo: fetch environment variables from .env file
// note: will not send if encryption is true
// when curl permissions are updated for encryption, reset cache (php artisan config:cache)
window.Echo = new Echo({
    broadcaster: 'pusher',
    key: '527f891d84f24cfb2701',
    cluster: 'us2',
    encrypted: false
});

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

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

Vue.mixin({
  methods: {
    getCurrentChime: () => window.location.pathname.split('/')[2]
  }
})


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
