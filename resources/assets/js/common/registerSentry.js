import router from "../router.js";
import * as Sentry from "@sentry/vue";
import { BrowserTracing } from "@sentry/tracing";
import Vue from "vue";

const debug = process.env.NODE_ENV !== "production";
console.log(process.env.MIX_SENTRY_DSN);

export default () =>
  Sentry.init({
    Vue,
    dsn: process.env.MIX_SENTRY_DSN,
    integrations: [
      new BrowserTracing({
        routingInstrumentation: Sentry.vueRouterInstrumentation(router),
        tracingOrigins: ["localhost", /^.*chimein.*.umn.edu/, /^\//],
      }),
    ],
    environment: process.env.MIX_APP_ENV,
    debug,
    // Set tracesSampleRate to 1.0 to capture 100%
    // of transactions for performance monitoring.
    // We recommend adjusting this value in production
    tracesSampleRate: 1.0,
  });
