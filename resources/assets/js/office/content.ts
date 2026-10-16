import { createApp } from "vue";
import App from "./App.vue";
import "../../sass/app.scss";
import "../../sass/utils.css";

Office.onReady(() => {
  createApp(App).mount("#office-content");
});
