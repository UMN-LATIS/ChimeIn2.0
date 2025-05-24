import http from "k6/http";
import { check, sleep, group } from "k6";
import ws from "k6/ws";
import { Trend } from "k6/metrics";
// @ts-ignore
import { randomIntBetween } from "https://jslib.k6.io/k6-utils/1.2.0/index.js";

export const wsHandshake = new Trend("ws_connecting_time");

const JOIN_URL = `http://localhost/join/52204`;
const ECHO_SERVER_URL_HTTP = `http://localhost:6001/socket.io/`;
const ECHO_SERVER_URL_WS = `ws://localhost:6001/socket.io/`;
const SESSION_MIN_MS = 3_000;
const SESSION_MAX_MS = 6_000;

export const options = {
  scenarios: {
    spike: {
      executor: "ramping-arrival-rate", // spike of new connections
      startRate: 0, // start at zero new iterations/sec
      timeUnit: "1s",
      preAllocatedVUs: 400, // initial pool; tune higher if you need more
      maxVUs: 2000, // absolute cap on concurrent VUs
      stages: [
        { target: 50, duration: "10s" },
        { target: 200, duration: "20s" },
        { target: 400, duration: "40s" },
        { target: 0, duration: "20s" }, // ramp down
      ],
    },
  },
  thresholds: {
    http_req_failed: ["rate<0.01"],
    ws_connecting_time: ["p(95)<2000"], // custom metric, see below
  },
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

function getEchoSessionId() {
  const res = http.get(
    `${ECHO_SERVER_URL_HTTP}?EIO=3&transport=polling&t=${crypto.randomUUID()}`,
    {
      redirects: 1,
    }
  );

  if (typeof res.body !== "string") {
    console.error(
      "Response body is not a string:",
      JSON.stringify(res, null, 2)
    );
    throw new Error("Socket.io response body is not a string");
  }

  const sidMatches = res.body?.match(/sid":"(.*?)"/);
  const sid = sidMatches ? sidMatches[1] : null; // first match

  if (!sid) {
    console.error("SID not found in the response body:", res.body);
    throw new Error("Socket.io SID not found in the response body");
  }

  check(res, {
    "socket.io page status is 200": (r) => r.status === 200,
    "socket.io page has cookies": (r) => Object.entries(r.cookies).length > 0,
    "socket.io page has sid": () => !!sid && sid.length > 0,
  });

  return sid;
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
  // console.log("=== TEST STARTING ===");

  // visit join page, which will create a user, return
  // session cookie, and csrf token
  let joinRes, chimeId, csrfToken;
  group("Join page → tokens", () => {
    ({ res: joinRes, chimeId, csrfToken } = getJoinPage());
  });

  // get echo session to get sid and csrf token
  let sid: string;
  group("Polling handshake", () => {
    sid = getEchoSessionId();
  });

  // connect with websockets
  group("Websocket subscribe", () => {
    const wsUrl = `${ECHO_SERVER_URL_WS}?EIO=3&transport=websocket&sid=${sid}`;

    const wsParams = {
      headers: {
        "X-CSRF-TOKEN": csrfToken,
      },
      cookies: joinRes.cookies,
    };

    const start = new Date().getTime();
    const wsRes = ws.connect(wsUrl, wsParams, (socket) => {
      console.log("=== CONNECTING TO WEBSOCKET ===");
      const socketSend = (msg: string) => {
        socket.send(msg);
        // console.log("[client]", msg);
      };

      socket.on("open", () => {
        // record connection time
        wsHandshake.add(new Date().getTime() - start);
        // console.log("=== WEBSOCKET OPEN ===");

        // socket.io has a handshake protocol where it sends `2probe`
        // and expects `3probe` in return
        socketSend("2probe");
      });

      socket.on("message", (data) => {
        if (data === "3probe") {
          // after receiving 3probe, we send 5 to complete the handshake
          socketSend("5");

          // console.log("=== WEBSOCKET HANDSHAKE COMPLETE ===");
          subscribeToChimePresenceChannel(socketSend, chimeId, csrfToken);

          // set up regular pings to the server
          socket.setInterval(
            () => socketSend("2"),
            randomIntBetween(500, 1500)
          );

          return;
        }

        // Parse Socket.IO messages
        if (data.startsWith("42")) {
          // console.log("[server]", parseSocketIoMessage(data));
          return;
        }

        // log other messages
        // console.log("[server]", data);
      });

      socket.on("close", () => {
        console.log("=== WEBSOCKET CONNECTION CLOSED ===");
      });

      socket.on("error", function (e) {
        console.error("=== WEBSOCKET ERROR ===", e.error());
      });

      socket.setTimeout(
        function () {
          // console.log(`=== CLOSING SOCKET ===`);
          socket.close();
        },
        randomIntBetween(SESSION_MIN_MS, SESSION_MAX_MS)
      );
    });

    sleep(randomIntBetween(1, 13));

    check(wsRes, {
      "status is 101": (r) => r.status === 101,
    });
  });
}
