import Echo from "laravel-echo";

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */
const echoClient = new Echo({
  broadcaster: "socket.io",
  host: `${window.location.hostname}:6001`,
});

export default echoClient;
