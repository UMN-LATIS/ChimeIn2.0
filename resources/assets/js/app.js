
require('./bootstrap');

window.sw = require('stopword')

window.queryString = require('query-string');


import EventBus from './event-bus';

import Vuex from 'vuex'
Vue.use(Vuex)

import VueRouter from 'vue-router'
Vue.use(VueRouter)

import fullscreen from 'vue-fullscreen'
Vue.use(fullscreen)

import PrettyCheckbox from 'pretty-checkbox-vue';
Vue.use(PrettyCheckbox);

import VueAnnouncer from 'vue-announcer';
Vue.use(VueAnnouncer);

// filters

Vue.filter('pluralize', (word, amount) => amount > 1 ? `${word}s` : word)

Vue.component('modal',
    require('./components/modal.vue').default);

import Home from './components/Home.vue';
Vue.component('home', Home);

import Chime from './components/Chime.vue';
Vue.component('chime', Chime);

import ChimeStudent from './components/Chime_student.vue';
Vue.component('ChimeStudent', ChimeStudent);


import Present from './components/Present.vue';
Vue.component('Present', Present);

const Folder = () => import(
    /* webpackChunkName: "folder" */
    './components/Folder.vue'
);
Vue.component('Folder', Folder);

Vue.component('error-dialog',
    require('./components/error_dialog.vue').default);

Vue.component('navbar',
    require('./components/Navbar.vue').default);
Vue.component('chime-panel',
    require('./components/home_components/ChimePanel.vue').default);
Vue.component('chime-card',
    require('./components/home_components/ChimeCard.vue').default);

Vue.component('new-folder',
    require('./components/chime_components/NewFolder.vue').default);
Vue.component('folder-card',
    require('./components/chime_components/FolderCard.vue').default);
Vue.component('ChimeManagement',
    require('./components/chime_components/ChimeManagement.vue').default);
Vue.component('ChimeManagementOptions',
    require('./components/chime_components/ChimeManagementOptions.vue').default);
Vue.component('ChimeExport',
    require('./components/chime_components/ChimeExport.vue').default);
Vue.component('question',
    require('./components/chime_components/Question.vue').default);



Vue.component('student-prompt',
    require('./components/chime_student_components/Student_Prompt.vue').default);
Vue.component('response',
    require('./components/chime_student_components/Response.vue').default);
Vue.component('multiple_choice',
    require('./components/questions/response/MultipleChoice.vue').default);
Vue.component('true_false',
    require('./components/questions/response/TrueFalse.vue').default);
Vue.component('image_response',
    require('./components/questions/response/ImageResponse.vue').default);
Vue.component('free_response',
    require('./components/questions/response/FreeResponse.vue').default);
Vue.component('slider_response',
    require('./components/questions/response/Slider.vue').default);


Vue.component('present-question',
    require('./components/presentation_components/Present_Question.vue').default);

Vue.component('presentation-prompt',
    require('./components/presentation_components/Presentation_Prompt.vue').default);
Vue.component('results-display',
    require('./components/presentation_components/ResultsDisplay.vue').default);
Vue.component('multiple_choice_display',
    require('./components/questions/display/MultipleChoice.vue').default);
Vue.component('true_false_display',
    require('./components/questions/display/TrueFalse.vue').default);
Vue.component('slider_response_display',
    require('./components/questions/display/Slider.vue').default);
Vue.component('free_response_display',
    require('./components/questions/display/FreeResponse.vue').default);
Vue.component('image_response_display',
    require('./components/questions/display/ImageResponse.vue').default);


const store = new Vuex.Store({
    state: {
        message: null
    },
    mutations: {
        message(state, message) {
            state.message = message;
        },
        clearMessage(state) {
            state.message = null;
        }
    },
    getters: {
        message: state => state.message
    }
});


const router = new VueRouter({
    mode: 'history',
  routes: [
    { path: "/", component: Home },
    { path: "/chime/:chimeId", name:'chime', component: Chime, props: true },
    { path: "/chime/:chimeId/folder/:folderId", name:'folder', component: Folder, props: true },
    { path: "/chimeParticipant/:chimeId", name:'chimeStudent', component: ChimeStudent, props: true },
    { path: "/chime/:chimeId/folder/:folderId/present/:questionId?", name:'present', component: Present, props: true }
    // { path: '/:id?', name: "present", component: require('./components/Present.vue')}
  ]
})

const app = new Vue({
    router,
    store
}).$mount('#app');


