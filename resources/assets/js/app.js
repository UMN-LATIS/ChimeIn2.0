
require('./bootstrap');

window.sw = require('stopword')

window.queryString = require('query-string');


import EventBus from './event-bus';


import VueRouter from 'vue-router'
Vue.use(VueRouter)

import fullscreen from 'vue-fullscreen'
Vue.use(fullscreen)

import PrettyCheckbox from 'pretty-checkbox-vue';
Vue.use(PrettyCheckbox);

// filters

Vue.filter('pluralize', (word, amount) => amount > 1 ? `${word}s` : word)

var errorStore = {
    debug: true,
    state: {
        message: null
    },
    setMessageAction(newValue) {
        if (this.debug) console.log('setMessageAction triggered with', newValue)
        this.state.message = newValue
    },
    clearMessageAction() {
        if (this.debug) console.log('clearMessageAction triggered')
        this.state.message = ''
    }
};


Vue.component('modal',
    require('./components/modal.vue'));

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

Vue.component('navbar',
    require('./components/Navbar.vue'));
Vue.component('chime-panel',
    require('./components/home_components/ChimePanel.vue'));
Vue.component('chime-card',
    require('./components/home_components/ChimeCard.vue'));

Vue.component('new-folder',
    require('./components/chime_components/NewFolder.vue'));
Vue.component('folder-card',
    require('./components/chime_components/FolderCard.vue'));
Vue.component('ChimeManagement',
    require('./components/chime_components/ChimeManagement.vue'));
Vue.component('question',
    require('./components/chime_components/Question.vue'));



Vue.component('student-prompt',
    require('./components/chime_student_components/Student_Prompt.vue'));
Vue.component('response',
    require('./components/chime_student_components/Response.vue'));
Vue.component('multiple_choice',
    require('./components/questions/response/MultipleChoice.vue'));
Vue.component('true_false',
    require('./components/questions/response/TrueFalse.vue'));
Vue.component('image_response',
    require('./components/questions/response/ImageResponse.vue'));
Vue.component('free_response',
    require('./components/questions/response/FreeResponse.vue'));
Vue.component('slider_response',
    require('./components/questions/response/Slider.vue'));


Vue.component('present-question',
    require('./components/presentation_components/Present_Question.vue'));

Vue.component('presentation-prompt',
    require('./components/presentation_components/Presentation_Prompt.vue'));
Vue.component('results-display',
    require('./components/presentation_components/ResultsDisplay.vue'));
Vue.component('multiple_choice_display',
    require('./components/questions/display/MultipleChoice.vue'));
Vue.component('true_false_display',
    require('./components/questions/display/TrueFalse.vue'));
Vue.component('slider_response_display',
    require('./components/questions/display/Slider.vue'));
Vue.component('free_response_display',
    require('./components/questions/display/FreeResponse.vue'));
Vue.component('image_response_display',
    require('./components/questions/display/ImageResponse.vue'));


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
    router
}).$mount('#app')