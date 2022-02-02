import Vue from "vue";
import VueAnnouncer from "vue-announcer";
import $ from "jquery";
import "bootstrap";
import registerAxios from "./common/axios.js";
import registerEcho from "./common/echo.js";
import registerSocketIOClient from "./common/socketioClient.js";
import registerDevTools from "./common/devtools.js";
import pluralizeFilter from "./common/pluralize.filter.js";
import registerSentry from "./common/registerSentry.js";
import router from "./router.js";
import store from "./store.js";

registerSocketIOClient();
registerAxios();
registerEcho();

if (process.env.MIX_APP_ENV !== "local") registerSentry();
if (process.env.MIX_APP_ENV !== "production") registerDevTools();

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
