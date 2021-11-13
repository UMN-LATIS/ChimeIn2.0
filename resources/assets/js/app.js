import process from "process";
import Vue from "vue";
import stopword from "stopword";
import queryString from "query-string";
import fullscreen from "vue-fullscreen";
import PrettyCheckbox from "pretty-checkbox-vue";
import VueAnnouncer from "vue-announcer";
import tooltipDirective from "./common/tooltip.directive.js";
import pluralizeFilter from "./common/pluralize.filter.js";
import router from './router.js';
import store from './store.js';
import "./bootstrap.js";


window.sw = stopword;
window.queryString = queryString;

Vue.use(fullscreen);
Vue.use(PrettyCheckbox);
Vue.use(VueAnnouncer);

Vue.filter("pluralize", pluralizeFilter);
Vue.directive("tooltip", tooltipDirective);

new Vue({
  router,
  store,
}).$mount("#app");

if (process.env.MIX_APP_ENV === "development") {
  Vue.config.devtools = true;
  Vue.config.debug = true;
  Vue.config.silent = false;
}
