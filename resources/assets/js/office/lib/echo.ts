import Echo from "laravel-echo";
import Pusher from "pusher-js";

declare global {
  interface Window {
    Pusher: typeof Pusher;
  }
}

/**
 * The content add-in has no session cookie, so channel auth goes through the
 * bearer-token endpoint registered alongside the normal broadcasting/auth route.
 */
export function createOfficeEchoClient(token: string): Echo<"reverb"> {
  window.Pusher = Pusher;

  return new Echo({
    broadcaster: "reverb",
    key: window.chimeInOffice.reverbKey,
    wsHost: window.location.hostname,
    wsPort: Number(window.chimeInOffice.reverbPort ?? 8080),
    forceTLS: window.location.protocol === "https:",
    enabledTransports: ["ws", "wss"],
    authEndpoint: "/api/office/broadcasting/auth",
    auth: {
      headers: { Authorization: `Bearer ${token}` },
    },
  });
}
