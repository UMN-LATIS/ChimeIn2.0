import { createApp } from "vue";
import VueAnnouncer from "@vue-a11y/announcer";
import "@vue-a11y/announcer/dist/style.css";
import "bootstrap";
import { Tooltip } from "bootstrap";
import ltilaunch from "./components/lti/ltiLaunch.vue";
import DefaultLayout from "./layouts/DefaultLayout.vue";
import router from "./router";
import store from "./store";
import axiosClient from "./common/axiosClient";
import echoClient from "./common/echoClient";
import "../sass/app.css";
import "../sass/utils.css";
import "@umn-latis/cla-vue-template/dist/index.css";

declare global {
  interface Window {
    axios: typeof axiosClient;
    Echo: typeof echoClient;
    lti_launch?: unknown;
  }
}

window.axios = axiosClient;
window.Echo = echoClient;

const app = createApp({});

app.use(VueAnnouncer);

const tooltipInstances = new WeakMap<HTMLElement, Tooltip>();

app.directive("tooltip", {
  mounted(el, binding) {
    tooltipInstances.set(
      el,
      new Tooltip(el, {
        title: binding.value,
        placement: binding.arg,
        trigger: "hover",
      })
    );
  },
  updated(el, binding) {
    tooltipInstances.get(el)?.dispose();
    tooltipInstances.set(
      el,
      new Tooltip(el, {
        title: binding.value,
        placement: binding.arg,
        trigger: "hover",
      })
    );
  },
  unmounted(el) {
    tooltipInstances.get(el)?.dispose();
    tooltipInstances.delete(el);
  },
});

app.component("LtiLaunch", ltilaunch);
app.component("DefaultLayout", DefaultLayout);

app.use(router).use(store).mount("#app");
