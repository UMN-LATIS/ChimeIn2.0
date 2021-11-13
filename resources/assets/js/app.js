import process from "process";
import Vue from "vue";
import stopword from "stopword";
import queryString from "query-string";
import simpleheat from "simpleheat";
import Vuex from "vuex";
import fullscreen from "vue-fullscreen";
import PrettyCheckbox from "pretty-checkbox-vue";
import VueAnnouncer from "vue-announcer";
import jQuery from "jquery";
import router from './router.js';

import "./bootstrap.js";


window.sw = stopword;
window.queryString = queryString;
window.simpleheat = simpleheat;

Vue.use(Vuex);

Vue.use(fullscreen);
Vue.use(PrettyCheckbox);
Vue.use(VueAnnouncer);

Vue.filter("pluralize", (word, amount) => (amount === 1 ? word : `${word}s`));

Vue.directive("tooltip", function(el, binding) {
  jQuery(el).tooltip({
    title: binding.value,
    placement: binding.arg,
    trigger: "hover",
  });
});

const store = new Vuex.Store({
  state: {
    message: null,
  },
  mutations: {
    message(state, message) {
      state.message = message;
    },
    clearMessage(state) {
      state.message = null;
    },
  },
  getters: {
    message: (state) => state.message,
  },
});



new Vue({
  router,
  store,
}).$mount("#app");

if (process.env.MIX_APP_ENV === "development") {
  Vue.config.devtools = true;
  Vue.config.debug = true;
  Vue.config.silent = false;
}
