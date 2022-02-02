import Vue from "vue";
import VueAnnouncer from "vue-announcer";
import $ from "jquery";
import "bootstrap";
import * as Sentry from "@sentry/vue";
import { BrowserTracing } from "@sentry/tracing";
import registerAxios from "./common/axios.js";
import registerEcho from "./common/echo.js";
import registerSocketIOClient from "./common/socketioClient.js";
import registerDevTools from "./common/devtools.js";
import pluralizeFilter from "./common/pluralize.filter.js";
import router from "./router.js";
import store from "./store.js";

registerSocketIOClient();
registerAxios();
registerEcho();
registerDevTools();

Sentry.init({
  Vue,
  dsn: "https://07ce91c7268d4a5db1422696422594d2@o59337.ingest.sentry.io/6180515",
  integrations: [
    new BrowserTracing({
      routingInstrumentation: Sentry.vueRouterInstrumentation(router),
      tracingOrigins: ["localhost", "my-site-url.com", /^\//],
    }),
  ],
  debug: true,
  // Set tracesSampleRate to 1.0 to capture 100%
  // of transactions for performance monitoring.
  // We recommend adjusting this value in production
  tracesSampleRate: 1.0,
});

Vue.use(VueAnnouncer);

Vue.filter("pluralize", pluralizeFilter);

// $.tooltip requires jquery and bootstrap
Vue.directive("tooltip", (el, binding) =>
  $(el).tooltip({
    title: binding.value,
    placement: binding.arg,
    trigger: "hover",
  })
);

import ltilaunch from "./components/lti/ltiLaunch.vue";
Vue.component("lti-launch", ltilaunch);

new Vue({
  router,
  store,
}).$mount("#app");
