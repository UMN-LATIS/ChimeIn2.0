
require('./bootstrap');

window.queryString = require('query-string');

Vue.component('home',
    require('./components/Home.vue'));

Vue.component('chime',
    require('./components/Chime.vue'));

Vue.component('Chimestudent',
    require('./components/Chime_student.vue'));

Vue.component('Present',
    require('./components/Present.vue'));

Vue.component('navbar',
    require('./components/Navbar.vue'));
Vue.component('chime-panel',
    require('./components/home_components/ChimePanel.vue'));
Vue.component('chime-card',
    require('./components/home_components/ChimeCard.vue'));
Vue.component('access-panel',
    require('./components/home_components/AccessPanel.vue'));
Vue.component('info-panel',
    require('./components/home_components/InfoPanel.vue'));

Vue.component('new-folder',
    require('./components/chime_components/NewFolder.vue'));
Vue.component('folder-card',
    require('./components/chime_components/FolderCard.vue'));
Vue.component('question',
    require('./components/chime_components/Question.vue'));
Vue.component('multiple-choice-display',
    require('./components/questions/display/MultipleChoice.vue'));
Vue.component('question-form',
    require('./components/chime_components/QuestionForm.vue'));


Vue.component('presentation-actions',
    require('./components/presentation_components/Presentation_Actions.vue'));

Vue.component('student-actions',
    require('./components/chime_student_components/Student_Actions.vue'));
Vue.component('student-prompt',
    require('./components/chime_student_components/Student_Prompt.vue'));
Vue.component('response',
    require('./components/chime_student_components/Response.vue'));
Vue.component('multiple-choice-question',
    require('./components/questions/response/MultipleChoice.vue'));
Vue.component('image-response-question',
    require('./components/questions/response/ImageResponse.vue'));
Vue.component('free-response-question',
    require('./components/questions/response/FreeResponse.vue'));

Vue.component('presentation-prompt',
    require('./components/presentation_components/Presentation_Prompt.vue'));
Vue.component('results-display',
    require('./components/presentation_components/ResultsDisplay.vue'));
Vue.component('multiple-choice-display',
    require('./components/questions/display/MultipleChoice.vue'));
Vue.component('multiple-choice-statistics',
    require('./components/presentation_components/MultipleChoiceStatistics.vue'));
Vue.component('image-response-statistics',
    require('./components/presentation_components/ImageResponseStatistics.vue'));
Vue.component('free-response-statistics',
    require('./components/presentation_components/FreeResponseStatistics.vue'));




const app = new Vue({
}).$mount('#app')