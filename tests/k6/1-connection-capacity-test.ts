import http from "k6/http";
import { check, sleep } from "k6";
import ws from "k6/ws";
// @ts-ignore
import { randomIntBetween } from "https://jslib.k6.io/k6-utils/1.2.0/index.js";

const JOIN_URL = `http://localhost/join/52204`;
const ECHO_SERVER_URL = `http://localhost:6001/socket.io/`;

export const options = {
  stages: [{ iterations: 1, target: 1 }],
  thresholds: {
    // 95% of requests must complete below time
    http_req_duration: ["p(95)<300"],
    // http errors should be less than 1%
    http_req_failed: ["rate<0.01"],
  },
};

export default async function () {
  // 1: visit join page to get necessary cookies
  // cookies should be automatically handled by k6
  const joinRes = http.get(JOIN_URL, {
    // we'll need to follow a redirect
    redirects: 1,
  });

  const chimeId = joinRes.url.match(/\/chimeParticipant\/(\d+)/)?.[1];

  check(joinRes, {
    "join page status is 200": (r) => r.status === 200,
    "join page has cookies": (r) => Object.entries(r.cookies).length > 0,
    "join page has chimeId": () => !!chimeId && chimeId.length > 0,
  });

  console.log("Chime ID:", chimeId);
  console.log("Cookies:", Object.keys(joinRes.cookies));

  // 2. start handshake with Echo server
  const socketIoRes = http.get(
    `${ECHO_SERVER_URL}?EIO=3&transport=polling&t=${Date.now()}`
  );

  if (typeof socketIoRes.body !== "string") {
    throw new Error("Socket.io response body is not a string");
  }

  console.log(
    "Socket.IO handshake response:",
    socketIoRes.body.substring(0, 100) + "..."
  );

  const sidMatches = socketIoRes.body?.match(/sid":"(.*?)"/);
  const sid = sidMatches ? sidMatches[1] : null; // first match

  check(socketIoRes, {
    "socket.io page status is 200": (r) => r.status === 200,
    "socket.io page has cookies": (r) => Object.entries(r.cookies).length > 0,
    "socket.io page has sid": () => !!sid && sid.length > 0,
  });

  console.log("Socket.IO SID:", sid);

  // connect with websockets rather than a long-polling POST request
  const wsUrl = `${ECHO_SERVER_URL.replace("http", "ws")}?EIO=3&transport=websocket&sid=${sid}`;
  console.log("WebSocket URL:", wsUrl);

  // Get CSRF token from cookies
  let csrfToken = joinRes.cookies["XSRF-TOKEN"]?.[0]?.value;

  // If the CSRF token is URL encoded, decode it
  if (csrfToken) {
    try {
      csrfToken = decodeURIComponent(csrfToken);
    } catch (e) {
      console.error("Error decoding CSRF token:", e);
    }
  }

  if (typeof csrfToken !== "string") {
    throw new Error("CSRF token not found in the cookies");
  }

  console.log("CSRF Token:", csrfToken);

  const wsParams = {
    headers: {
      "X-CSRF-TOKEN": csrfToken,
    },
    cookies: joinRes.cookies,
  };

  let hasError = false;
  let wsSocket = null as ws.Socket | null;
  let socketConnected = false;
  let timeoutId = null as null | NodeJS.Timeout;

  const wsRes = ws.connect(wsUrl, wsParams, (socket) => {
    wsSocket = socket;

    socket.on("open", () => {
      console.log("WebSocket connection opened");
      // Send Socket.IO engine.io probe
      socket.send("2probe");
    });

    socket.on("message", (data) => {
      console.log("Message received:", data);

      // respond to probe message
      if (data === "3probe") {
        // Send upgrade packet
        socket.send("5");
        console.log("Sent upgrade packet");

        // Send Socket.IO connection packet
        socket.send("40");
        console.log("Sent connection packet");

        socketConnected = true;

        // After connection is established, subscribe to the channel
        const channelName = `presence-session-status.${chimeId}`;
        const subscribeMessage = JSON.stringify([
          "subscribe",
          {
            channel: channelName,
            auth: {
              headers: {
                "X-CSRF-TOKEN": csrfToken,
              },
            },
          },
        ]);

        // Send the subscription message with the proper Socket.IO format (42 prefix)
        socket.send(`42${subscribeMessage}`);
        console.log("Sent subscription message for channel:", channelName);

        // Start sending regular pings to keep the connection alive
        // This is the corrected part - client sends pings (2), server responds with pongs (3)
        const setPingTimeout = () =>
          setTimeout(() => {
            socket.send("2");
            console.log("[client] PING!");
            timeoutId = setPingTimeout();
          }, 1000);

        timeoutId = setPingTimeout();
        return;
      }

      // Check for pong responses (server responds to our pings)
      if (data === "3") {
        console.log("[server] PONG!");
        return;
      }

      // Parse Socket.IO messages
      if (data.startsWith("42")) {
        try {
          const message = data.substring(2);
          const parsedMessage = JSON.parse(message);
          console.log("[server]", parsedMessage);
        } catch (e) {
          console.error("‼️ [error]", e);
        }
      }
    });

    socket.on("close", (arg) => {
      console.log("[server]: websocket close", arg);
      socketConnected = false;
    });

    socket.on("error", function (e) {
      hasError = true;
      console.error("[server]:", e.error());
    });
  });

  check(wsRes, {
    "status is 101": (r) => r.status === 101,
  });

  sleep(10);

  // Report final status
  check(null, {
    "socket.io connection established": () => socketConnected,
    "no websocket errors": () => !hasError,
  });

  // C
  if (timeoutId) {
    // Clear the ping timeout
    clearTimeout(timeoutId);
  }

  if (wsSocket) {
    try {
      wsSocket.close();
      console.log("WebSocket closed by client");
    } catch (e) {
      console.error("Error closing WebSocket:", e);
    }
  }
}
