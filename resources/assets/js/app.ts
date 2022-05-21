import { createApp } from "vue";
import VueAnnouncer from "@vue-a11y/announcer";
import "@vue-a11y/announcer/dist/style.css";
import $ from "jquery";
import "bootstrap";
import ltilaunch from "./components/lti/ltiLaunch.vue";
import registerAxios from "./common/axios.js";
import registerEcho from "./common/echo.js";
import registerSocketIOClient from "./common/socketioClient.js";
import router from "./router.js";
import store from "./store.js";

registerSocketIOClient();
registerAxios();
registerEcho();

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

app.use(router).use(store).mount("#app");
