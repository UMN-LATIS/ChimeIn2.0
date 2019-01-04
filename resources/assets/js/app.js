
require('./bootstrap');

window.sw = require('stopword')

window.queryString = require('query-string');


import EventBus from './event-bus';


import VueRouter from 'vue-router'
Vue.use(VueRouter)

import fullscreen from 'vue-fullscreen'
Vue.use(fullscreen)


// import ReactiveBarChart from "./ReactiveBarChart.js";

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

import Folder from './components/Folder.vue';
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
Vue.component('multiple-choice-display',
    require('./components/questions/display/MultipleChoice.vue'));
Vue.component('question-form',
    require('./components/chime_components/QuestionForm.vue'));


Vue.component('student-prompt',
    require('./components/chime_student_components/Student_Prompt.vue'));
Vue.component('response',
    require('./components/chime_student_components/Response.vue'));
Vue.component('multiple_choice',
    require('./components/questions/response/MultipleChoice.vue'));
Vue.component('image_response',
    require('./components/questions/response/ImageResponse.vue'));
Vue.component('free_response',
    require('./components/questions/response/FreeResponse.vue'));


Vue.component('present-question',
    require('./components/presentation_components/Present_Question.vue'));

Vue.component('presentation-prompt',
    require('./components/presentation_components/Presentation_Prompt.vue'));
Vue.component('results-display',
    require('./components/presentation_components/ResultsDisplay.vue'));
Vue.component('multiple-choice-display',
    require('./components/questions/display/MultipleChoice.vue'));
Vue.component('multiple_choice_statistics',
    require('./components/presentation_components/MultipleChoiceStatistics.vue'));
Vue.component('image_response_statistics',
    require('./components/presentation_components/ImageResponseStatistics.vue'));
Vue.component('free_response_statistics',
    require('./components/presentation_components/FreeResponseStatistics.vue'));


const router = new VueRouter({
    mode: 'history',
  routes: [
    { path: "/", component: Home },
    { path: "/chime/:chimeId", name:'chime', component: Chime, props: true },
    { path: "/chime/:chimeId/folder/:folderId", name:'folder', component: Folder, props: true },
    { path: "/chimeStudent/:chimeId", name:'chimeStudent', component: ChimeStudent, props: true },
    { path: "/chime/:chimeId/folder/:folderId/present/:questionId?", name:'present', component: Present, props: true }
    // { path: '/:id?', name: "present", component: require('./components/Present.vue')}
  ]
})

const app = new Vue({
    router
}).$mount('#app')