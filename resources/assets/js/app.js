import Vue from "vue";
import VueAnnouncer from "vue-announcer";
import registerAxios from "./common/axios.js";
import registerEcho from "./common/echo.js";
import registerSocketIOClient from "./common/socketioClient.js";
import registerDevTools from "./common/devtools.js";
import tooltipDirective from "./common/tooltip.directive.js";
import pluralizeFilter from "./common/pluralize.filter.js";
import router from "./router.js";
import store from "./store.js";

registerSocketIOClient();
registerAxios();
registerEcho();
registerDevTools();

Vue.use(VueAnnouncer);

Vue.filter("pluralize", pluralizeFilter);
Vue.directive("tooltip", tooltipDirective);

new Vue({
  router,
  store,
}).$mount("#app");
