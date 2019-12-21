import Echo from "laravel-echo";


window._ = {};
window._.sortBy = require('lodash/sortBy');
window._.orderBy = require('lodash/orderBy')
window._.throttle = require('lodash/throttle')

// window._ = require('lodash');

// import _sortBy from 'lodash';

/**
 * We'll load jQuery and the Bootstrap jQuery plugin which provides support
 * for JavaScript based Bootstrap features such as modals and tabs. This
 * code may be modified to fit the specific needs of your application.
 */

try {
    window.$ = window.jQuery = require('jquery');
    require('bootstrap');
} catch (e) {}

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */


window.Vue = require('vue');

window.axios = require('axios');
window.io = require('socket.io-client');
// window.Pusher = require('pusher-js');

// todo: fetch environment variables from .env file
// note: will not send if encryption is true
// when curl permissions are updated for encryption, reset cache (php artisan config:cache)
const env = process.env.NODE_ENV || 'development';
// use our internal hostnames so we can work with docker
var host = window.location.hostname;
if(env == "development") {
    host = "echo.knowfear.net";
}

window.Echo = new Echo({
    broadcaster: 'socket.io',
    host: host + ':6001',
    // auth: {
    //     headers: {
    //         Authorization: 'Bearer ' + "2b54f921c0e9394855626e3641cb91c4",
    //     },
    // },
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
