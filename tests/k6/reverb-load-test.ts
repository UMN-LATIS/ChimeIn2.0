import http from "k6/http";
import { check, sleep } from "k6";
import ws from "k6/ws";
import { Trend } from "k6/metrics";
// @ts-ignore
import { randomIntBetween } from "https://jslib.k6.io/k6-utils/1.2.0/index.js";

const { JOIN_CODE, REVERB_APP_KEY } = __ENV;

if (!JOIN_CODE) {
  throw new Error("JOIN_CODE environment variable is required");
}

if (!REVERB_APP_KEY) {
  throw new Error("REVERB_APP_KEY environment variable is required");
}

const HTTP_BASE_URL = "https://chimein.cla.umn.edu";
const WS_BASE_URL = "wss://chimein.cla.umn.edu";
const SESSION_MIN_MS = 120_000;
const SESSION_MAX_MS = 240_000;
const PING_INTERVAL_MS = 25_000;

export const wsConnectingTime = new Trend("ws_connecting_time");
export const pingPongTime = new Trend("ws_ping_pong_time");
export const subscribePresenceChannelTime = new Trend(
  "ws_subscribe_presence_time"
);

export const options = {
  // stages: [{ iterations: 1, target: 1 }],
  scenarios: {
    spike: {
      executor: "ramping-arrival-rate",
      startRate: 0,
      timeUnit: "1s",
      preAllocatedVUs: 1000, // Increased initial pool
      maxVUs: 3000, // Increased max to handle thousands of users
      stages: [
        { target: 10, duration: "30s" }, //target is vus per second
        { target: 30, duration: "2m" },
        { target: 0, duration: "1m" },
      ],
    },
  },

  thresholds: {
    http_req_failed: ["rate<0.01"],
    ws_connecting_time: ["p(95)<2000"], // custom metric, see below
    ws_ping_pong_time: ["p(95)<1000"], // custom metric for ping-pong time
    ws_subscribe_presence_time: ["p(95)<2000"], // custom metric for presence channel subscription time
  },
};

export default function () {
  const res = http.get(`${HTTP_BASE_URL}/join/${__ENV.JOIN_CODE}`, {
    redirects: 1,
  });

  if (!res || res.status !== 200) {
    throw new Error(
      `Failed to fetch join page: ${res.status} ${res.status_text}`
    );
  }

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

  // now connect to the Reverb WebSocket server
  const wsUrl = `${WS_BASE_URL}/app/${REVERB_APP_KEY}?protocol=7&client=js&version=8.4.0&flash=false`;

  const wsConnectStart = new Date().getTime();
  let pingStart: number | null = null;

  const wsRes = ws.connect(wsUrl, null, (socket) => {
    console.log("=== CONNECTING TO WEBSOCKET ===");

    const socketSend = (msg: string) => {
      socket.send(msg);
      // console.log("[client]", msg);
    };

    socket.on("open", () => {
      // record connection time
      wsConnectingTime.add(new Date().getTime() - wsConnectStart);
      console.log("=== WEBSOCKET OPEN ===");
    });

    socket.on("message", (rawData: string) => {
      const msg = JSON.parse(rawData);

      if (msg.event === "pusher:connection_established") {
        const data = JSON.parse(msg.data);
        const socketId: string | null = data.socket_id || null;

        if (!socketId) {
          throw new Error(
            "Socket ID not found in the connection established message"
          );
        }

        // subscribe to presence change
        const subscribeResponse = http.post(
          `${HTTP_BASE_URL}/broadcasting/auth`,
          {
            socket_id: socketId,
            channel_name: `presence-session-status.${chimeId}`,
          }
        );

        if (!subscribeResponse || subscribeResponse.status !== 200) {
          throw new Error(
            `Failed to subscribe to channel: ${subscribeResponse.status} ${subscribeResponse.status_text}`
          );
        }

        const json = subscribeResponse.json() as Record<string, any>;

        const auth = json?.auth as string | null;
        if (!auth) {
          throw new Error("auth token not found in the response");
        }

        const channelData = JSON.parse(json?.channel_data || "{}");
        const userId = Number.parseInt(channelData.user_id);
        if (!userId || userId <= 0) {
          throw new Error("userId not found or invalid in the response");
        }

        // subscribe to the presence channel
        socketSend(
          JSON.stringify({
            event: "pusher:subscribe",
            data: {
              channel: `presence-session-status.${chimeId}`,
              auth,
              // pusher expects channel_data to be a stringified JSON object
              channel_data: JSON.stringify({
                user_id: userId.toString(), // needs to be string
                user_info: {
                  id: userId, // needs to be int
                },
              }),
            },
          })
        );

        const sendPing = () => {
          pingStart = new Date().getTime();
          socketSend(JSON.stringify({ event: "pusher:ping", data: {} }));
        };

        // send immediate ping to server
        sendPing();

        // set up regular pings to the server with a little jitter
        socket.setInterval(
          sendPing,
          randomIntBetween(PING_INTERVAL_MS - 500, PING_INTERVAL_MS + 500)
        );
        return;
      }

      if (msg.event === "pusher_internal:subscription_succeeded") {
        if (msg.channel.startsWith("presence-")) {
          subscribePresenceChannelTime.add(
            new Date().getTime() - wsConnectStart
          );
        }
        return;
      }

      if (msg.event === "pusher:pong") {
        // parse pongs
        if (!pingStart) {
          // console.warn("[client] pong received but no ping sent yet");
          return;
        }

        // record ping-pong time
        const pongTime = new Date().getTime() - pingStart;
        pingPongTime.add(pongTime);
        // console.log("[client] pong received, time:", pongTime, "ms");
        pingStart = null; // reset for next ping
        return;
      }

      // log other messages
      // console.log("[server]", msg);
    });

    socket.on("close", () => {
      console.log("=== WEBSOCKET CONNECTION CLOSED ===");
    });

    socket.on("error", function (e) {
      console.error("=== WEBSOCKET ERROR ===", e.error());
    });

    socket.setTimeout(
      function () {
        console.log(`=== CLOSING SOCKET ===`);
        socket.close();
      },
      randomIntBetween(SESSION_MIN_MS, SESSION_MAX_MS)
    );
  });

  check(wsRes, {
    "status is 101": (r) => r.status === 101,
  });
  sleep(randomIntBetween(2, 5));
}
