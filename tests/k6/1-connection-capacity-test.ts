import http from "k6/http";
import { check, sleep } from "k6";
import ws from "k6/ws";
// @ts-ignore
import { randomIntBetween } from "https://jslib.k6.io/k6-utils/1.2.0/index.js";

const JOIN_URL = `http://localhost/join/52204`;
const ECHO_SERVER_URL_HTTP = `http://localhost:6001/socket.io/`;
const ECHO_SERVER_URL_WS = `ws://localhost:6001/socket.io/`;

export const options = {
  stages: [{ iterations: 1, target: 1 }],
  thresholds: {
    // 95% of requests must complete below time
    http_req_duration: ["p(95)<300"],
    // http errors should be less than 1%
    http_req_failed: ["rate<0.01"],
  },
  // Add explicit duration to ensure the test ends
  // duration: "15s",
};

function getJoinPage() {
  const res = http.get(JOIN_URL, {
    redirects: 1,
  });

  const chimeIdStr = res.url.match(/\/chimeParticipant\/(\d+)/)?.[1];
  const chimeId = chimeIdStr ? parseInt(chimeIdStr, 10) : null;

  if (!chimeId) {
    throw new Error("Chime ID not found in the response URL");
  }

  const csrfToken = res.cookies["XSRF-TOKEN"]?.[0]?.value;

  check(res, {
    "join page status is 200": (r) => r.status === 200,
    "join page has cookies": (r) => Object.entries(r.cookies).length > 0,
    "join page has chimeId": () => chimeId > 0,
    "join page has csrfToken": () => !!csrfToken && csrfToken.length > 0,
  });

  return { res, chimeId, csrfToken };
}

function getEchoSession() {
  const res = http.get(
    `${ECHO_SERVER_URL_HTTP}?EIO=3&transport=polling&t=${crypto.randomUUID()}`,
    {
      redirects: 1,
    }
  );

  if (typeof res.body !== "string") {
    throw new Error("Socket.io response body is not a string");
  }

  const sidMatches = res.body?.match(/sid":"(.*?)"/);
  const sid = sidMatches ? sidMatches[1] : null; // first match
  const csrfToken = res.cookies["XSRF-TOKEN"]?.[0]?.value;

  check(res, {
    "socket.io page status is 200": (r) => r.status === 200,
    "socket.io page has cookies": (r) => Object.entries(r.cookies).length > 0,
    "socket.io page has sid": () => !!sid && sid.length > 0,
    "socket.io page has csrfToken": () => !!csrfToken && csrfToken.length > 0,
  });

  return { sid, csrfToken };
}

function subscribeToChimePresenceChannel(
  socketSend: (msg: string) => void,
  chimeId: number,
  csrfToken: string
) {
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
  socketSend(`42${subscribeMessage}`);
}

function parseSocketIoMessage(data: string) {
  // Check if the message starts with "42"
  if (!data.startsWith("42")) {
    return null;
  }

  // Extract the JSON part of the message
  const jsonString = data.substring(2);
  try {
    // Parse the JSON string
    const parsedMessage = JSON.parse(jsonString);
    return parsedMessage;
  } catch (e) {
    console.error("Failed to parse JSON message:", e);
  }
}

export default function () {
  console.log("=== TEST STARTING ===");

  const totalSessionDuration = randomIntBetween(3000, 6000); // milliseconds
  const numSleeps = 2;
  const sleepDuration = totalSessionDuration / numSleeps / 1000; // seconds

  // 1: visit join page to get necessary cookies
  const { res: joinRes, chimeId, csrfToken } = getJoinPage();

  // 2: get echo session to get sid and csrf token
  const { sid } = getEchoSession();

  // connect with websockets
  const wsUrl = `${ECHO_SERVER_URL_WS}?EIO=3&transport=websocket&sid=${sid}`;

  const wsParams = {
    headers: {
      "X-CSRF-TOKEN": csrfToken,
    },
    cookies: joinRes.cookies,
  };

  const wsRes = ws.connect(wsUrl, wsParams, (socket) => {
    console.log("=== CONNECTING TO WEBSOCKET ===");
    const socketSend = (msg: string) => {
      socket.send(msg);
      console.log("[client]", msg);
    };

    socket.on("open", () => {
      console.log("=== WEBSOCKET OPEN ===");

      // socket.io has a handshake protocol where it sends `2probe`
      // and expects `3probe` in return
      socketSend("2probe");
    });

    socket.on("message", (data) => {
      if (data === "3probe") {
        // after receiving 3probe, we send 5 to complete the handshake
        socketSend("5");

        console.log("=== WEBSOCKET HANDSHAKE COMPLETE ===");
        subscribeToChimePresenceChannel(socketSend, chimeId, csrfToken);

        // set up regular pings to the server
        socket.setInterval(() => socketSend("2"), randomIntBetween(1000, 2000));

        return;
      }

      // Parse Socket.IO messages
      if (data.startsWith("42")) {
        console.log("[server]", parseSocketIoMessage(data));
        return;
      }

      // log other messages
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
      console.log(`=== CLOSING SOCKET ===`);
      socket.close();
    }, SOCKET_TIMEOUT);
  });

  sleep(sleepDuration);

  check(wsRes, {
    "status is 101": (r) => r.status === 101,
  });
}
