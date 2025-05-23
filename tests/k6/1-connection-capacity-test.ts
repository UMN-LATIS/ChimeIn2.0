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
  // Add explicit duration to ensure the test ends
  duration: "15s",
};

export default function () {
  console.log("=== TEST STARTING ===");

  const totalSessionDuration = randomIntBetween(3000, 6000); // milliseconds
  const numSleeps = 2;
  const sleepDuration = totalSessionDuration / numSleeps / 1000; // seconds

  // 1: visit join page to get necessary cookies
  const joinRes = http.get(JOIN_URL, {
    redirects: 1,
  });

  const chimeId = joinRes.url.match(/\/chimeParticipant\/(\d+)/)?.[1];

  check(joinRes, {
    "join page status is 200": (r) => r.status === 200,
    "join page has cookies": (r) => Object.entries(r.cookies).length > 0,
    "join page has chimeId": () => !!chimeId && chimeId.length > 0,
  });

  // 2. start handshake with Echo server
  const socketIoRes = http.get(
    `${ECHO_SERVER_URL}?EIO=3&transport=polling&t=${Date.now()}`
  );

  if (typeof socketIoRes.body !== "string") {
    throw new Error("Socket.io response body is not a string");
  }

  const sidMatches = socketIoRes.body?.match(/sid":"(.*?)"/);
  const sid = sidMatches ? sidMatches[1] : null; // first match

  check(socketIoRes, {
    "socket.io page status is 200": (r) => r.status === 200,
    "socket.io page has cookies": (r) => Object.entries(r.cookies).length > 0,
    "socket.io page has sid": () => !!sid && sid.length > 0,
  });

  console.log("Socket.IO SID:", sid);

  // connect with websockets
  const wsUrl = `${ECHO_SERVER_URL.replace("http", "ws")}?EIO=3&transport=websocket&sid=${sid}`;

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

  const wsRes = ws.connect(wsUrl, wsParams, (socket) => {
    console.log("=== CONNECTING TO WEBSOCKET ===");

    socket.on("open", () => {
      console.log("[server]: <open event received>");

      // start handshake
      socket.send("2probe");
      console.log("[client]: 2probe");
    });

    socket.on("message", (data) => {
      // complete initial handshake
      if (data === "3probe") {
        console.log("[server]: 3probe");

        socket.send("5");
        console.log("[client]: 5");

        // handshake complete
        // subscribe to chime presence channel
        const subscribeMessage = JSON.stringify([
          "subscribe",
          {
            channel: `presence-session-status.${chimeId}`,
            auth: {
              headers: {
                "X-CSRF-TOKEN": csrfToken,
              },
            },
          },
        ]);

        // Send the subscription message
        // socket.io uses 42 prefix to indicate a message
        socket.send(`42${subscribeMessage}`);
        console.log("[client]: 42", subscribeMessage);

        // now set up ping/pong
        socket.setInterval(
          () => {
            socket.send("2");
            console.log("[client]: 2 (ping)");
          },
          randomIntBetween(1000, 2000)
        );

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
        return;
      }

      // Check for pong responses (server responds to our pings)
      if (data === "3") {
        console.log("[server] 3 (pong)");
        return;
      }

      console.log("[server]", data);
    });

    socket.on("close", () => {
      console.log("=== WEBSOCKET CONNECTION CLOSED ===");
    });

    socket.on("error", function (e) {
      console.error("=== WEBSOCKET ERROR ===", e.error());
    });

    const SOCKET_TIMEOUT = totalSessionDuration + 2000;
    socket.setTimeout(function () {
      console.log(`${SOCKET_TIMEOUT}ms passed, closing the socket`);
      socket.close();
    }, SOCKET_TIMEOUT);
  });

  sleep(sleepDuration);

  check(wsRes, {
    "status is 101": (r) => r.status === 101,
  });
}
