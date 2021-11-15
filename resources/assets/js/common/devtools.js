import process from "process";
import Vue from "vue";

export default function registerDevTools() {
  if (process.env.MIX_APP_ENV === "development") {
    Vue.config.devtools = true;
    Vue.config.debug = true;
    Vue.config.silent = false;
  }
}
