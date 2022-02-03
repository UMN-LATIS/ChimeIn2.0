import router from "../router.js";
import * as Sentry from "@sentry/vue";
import { BrowserTracing } from "@sentry/tracing";
import Vue from "vue";

export default () =>
  Sentry.init({
    Vue,
    dsn: process.env.MIX_SENTRY_DSN,
    integrations: [
      new BrowserTracing({
        routingInstrumentation: Sentry.vueRouterInstrumentation(router),
        tracingOrigins: ["localhost", /.*chimein.*\.umn\.edu/, /^\//],
      }),
    ],
    environment: process.env.MIX_APP_ENV,
    debug: process.env.MIX_APP_ENV !== "production",
    // Set tracesSampleRate to 1.0 to capture 100%
    // of transactions for performance monitoring.
    // We recommend adjusting this value in production
    tracesSampleRate: 1.0,
  });
