import { createApp } from "vue";
import VueAnnouncer from "@vue-a11y/announcer";
import "@vue-a11y/announcer/dist/style.css";
import $ from "jquery";
import "bootstrap";
import ltilaunch from "./components/lti/ltiLaunch.vue";
import DefaultLayout from "./layouts/DefaultLayout.vue";
import router from "./router";
import store from "./store";
import axiosClient from "./common/axiosClient";
// import socketIOClient from "./common/socketioClient";
import echoClient from "./common/echoClient";
import "@umn-latis/cla-vue-template/dist/style.css";
import "../sass/app.scss";
import "../sass/utils.css";

declare global {
  interface Window {
    axios: typeof axiosClient;
    Echo: typeof echoClient;
    lti_launch?: unknown;
  }
}

window.axios = axiosClient;
// window.Echo = echoClient;

const app = createApp({});

app.use(VueAnnouncer);

app.directive("tooltip", (el, binding) =>
  $(el).tooltip({
    title: binding.value,
    placement: binding.arg,
    trigger: "hover",
  })
);

app.component("LtiLaunch", ltilaunch);
app.component("DefaultLayout", DefaultLayout);

app.use(router).use(store).mount("#app");
