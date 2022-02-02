import Vue from "vue";

export default function registerDevTools() {
  Vue.config.devtools = true;
  Vue.config.debug = true;
  Vue.config.silent = false;
}
