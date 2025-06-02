import Echo from "laravel-echo";
import Pusher from "pusher-js";

declare global {
  interface Window {
    Pusher: typeof Pusher;
  }
}

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */
const echoClient = new Echo({
  broadcaster: "reverb",
  key: import.meta.env.VITE_REVERB_APP_KEY,
  wsHost: window.location.hostname,
  wsPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
  forceTLS: false,
  enabledTransports: ["ws", "wss"],
});

window.Pusher = Pusher;
export default echoClient; 