import Vue from "vue";
import stopword from "stopword";
import queryString from "query-string";
import simpleheat from "simpleheat";
import Vuex from "vuex";
import VueRouter from "vue-router";
import fullscreen from "vue-fullscreen";
import PrettyCheckbox from "pretty-checkbox-vue";
import VueAnnouncer from "vue-announcer";
import jQuery from 'jquery';
import "./bootstrap.js";

// COMPONENTS
import Home from "./components/Home.vue";
import Chime from "./components/Chime.vue";
import ChimeStudent from "./components/Chime_student.vue";
import Present from "./components/Present.vue";
import Modal from "./components/modal.vue";
import ErrorDialog from "./components/error_dialog.vue";
import NavBar from "./components/Navbar.vue";
import process from "process";
import ChimePanel from "./components/home_components/ChimePanel.vue";
import ChimeCard from "./components/home_components/ChimeCard.vue";
import NewFolder from "./components/chime_components/NewFolder.vue";
import FolderCard from "./components/chime_components/FolderCard.vue";
import ChimeManagement from "./components/chime_components/ChimeManagement.vue";
import ChimeManagementOptions from "./components/chime_components/ChimeManagementOptions.vue";
import ChimeExport from "./components/chime_components/ChimeExport.vue";
import Question from "./components/chime_components/Question.vue";
import StudentPrompt from "./components/chime_student_components/Student_Prompt.vue";
import Response from "./components/chime_student_components/Response.vue";
import MultipleChoice from "./components/questions/response/MultipleChoice.vue";
import ImageResponse from "./components/questions/response/ImageResponse.vue";
import FreeResponse from "./components/questions/response/FreeResponse.vue";
import TextHeatmapResponse from "./components/questions/response/TextHeatmapResponse.vue";
import NoResponse from "./components/questions/response/NoResponse.vue";
import SliderResponse from "./components/questions/response/Slider.vue";
import HeatmapResponse from "./components/questions/response/HeatmapResponse.vue";
import PresentQuestion from "./components/presentation_components/Present_Question.vue";
import PresentPrompt from "./components/presentation_components/Presentation_Prompt.vue";
import PresentResults from "./components/presentation_components/ResultsDisplay.vue";
import DisplayMultipleChoice from "./components/questions/display/MultipleChoice.vue";
import DisplaySlider from "./components/questions/display/Slider.vue";
import DisplayTextHeatmapResponse from "./components/questions/display/TextHeatmapResponse.vue";
import DisplayFreeResponse from "./components/questions/display/FreeResponse.vue";
import DisplayImageResponse from "./components/questions/display/ImageResponse.vue";
import DisplayHeatmapResponse from "./components/questions/display/HeatmapResponse.vue";
import LtiLaunch from "./components/lti/ltiLaunch.vue";
import LtiImportSelector from "./components/lti/ltiImportSelector.vue";
import LtiSetupPreferences from "./components/lti/ltiSetupPreferences.vue";



window.sw = stopword;
window.queryString = queryString;
window.simpleheat = simpleheat;

Vue.use(Vuex);
Vue.use(VueRouter);
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

Vue.component("modal", Modal);
Vue.component("home", Home);
Vue.component("chime", Chime);
Vue.component("ChimeStudent", ChimeStudent);
Vue.component("Present", Present);

const Folder = () =>
  import(
    /* webpackChunkName: "folder" */
    "./components/Folder.vue"
  );
Vue.component("Folder", Folder);
Vue.component("error-dialog", ErrorDialog);
Vue.component("navbar", NavBar);
Vue.component("chime-panel", ChimePanel);
Vue.component("chime-card", ChimeCard);
Vue.component("new-folder", NewFolder);
Vue.component("folder-card", FolderCard);
Vue.component("ChimeManagement", ChimeManagement);
Vue.component("ChimeManagementOptions", ChimeManagementOptions);
Vue.component("ChimeExport", ChimeExport);
Vue.component("question", Question);
Vue.component("student-prompt", StudentPrompt);
Vue.component("response", Response);
Vue.component("multiple_choice", MultipleChoice);
Vue.component("image_response", ImageResponse);
Vue.component("free_response", FreeResponse);
Vue.component("text_heatmap_response", TextHeatmapResponse);
Vue.component("no_response", NoResponse);
Vue.component("slider_response", SliderResponse);
Vue.component("heatmap_response", HeatmapResponse);
Vue.component("present-question", PresentQuestion);
Vue.component("presentation-prompt", PresentPrompt);
Vue.component("results-display", PresentResults);
Vue.component("multiple_choice_display", DisplayMultipleChoice);
Vue.component("slider_response_display", DisplaySlider);
Vue.component("text_heatmap_response_display", DisplayTextHeatmapResponse);
Vue.component("free_response_display", DisplayFreeResponse);
Vue.component("no_response_display", DisplayFreeResponse);
Vue.component("image_response_display", DisplayImageResponse);
Vue.component("heatmap_response_display", DisplayHeatmapResponse);
Vue.component('lti-launch', LtiLaunch);
Vue.component('lti-import-selector', LtiImportSelector);
Vue.component('lti-setup-preferences', LtiSetupPreferences);

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

const router = new VueRouter({
  mode: "history",
  routes: [
    { path: "/", component: Home },
    { path: "/chime/:chimeId", name: "chime", component: Chime, props: true },
    {
      path: "/chime/:chimeId/folder/:folderId",
      name: "folder",
      component: Folder,
      props: true,
    },
    {
      path: "/chimeParticipant/:chimeId/:folderId?",
      name: "chimeStudent",
      component: ChimeStudent,
      props: true,
    },
    {
      path: "/chime/:chimeId/folder/:folderId/present/:questionId?",
      name: "present",
      component: Present,
      props: true,
    },
  ],
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
