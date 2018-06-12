
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

const queryString = require('query-string');

Vue.component('navbar', require('./components/Navbar.vue'));
Vue.component('prompt',
    require('./components/presentation_components/Prompt.vue'));
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
Vue.component('actions',
    require('./components/presentation_components/Actions.vue'));

const app = new Vue({
    el: '#app',
    data: {
        chime_id: null,
        folder_id: window.location.pathname.split('/')[4],
        questions: [],
        sessions: [],
        current_question: null,
        current_session: null,
        show_results: false
    },
    methods: {
        start_session: function() {
            if (!this.current_session || !this.current_session.in_progress) {
                const self = this;
                const url = (
                    '/api/chime/'
                    + this.chime_id
                    + '/folder/'
                    + window.location.pathname.split('/')[4]
                    + '/question/'
                    + this.current_question.id);

                axios.post(url, {})
                .then(res => {
                    console.log(res);
                    self.current_session = res.data;
                    self.add_session_listeners(self.current_session.id);
                })
                .catch(err => {
                    console.log(err.response);
                });
            }
        },
        stop_session: function() {
            console.log(this.current_session.in_progress);
            if (this.current_session && this.current_session.in_progress) {
                const self = this;
                const url = (
                    '/api/chime/'
                    + this.chime_id
                    + '/folder/'
                    + this.folder_id
                    + '/question/'
                    + this.current_question.id
                    + '/session/'
                    + this.current_session.id
                );

                axios.put(url, {})
                .then(res => {
                    console.log(res);
                    self.current_session = res.data;
                    self.add_session_listeners(self.current_session.id);
                })
                .catch(err => {
                    console.log(err.response);
                });
            }
        },
        view_results: function() {
            this.show_results = this.show_results ? false : true;
        },
        next_question: function() {
            const current_index = this.questions.findIndex(
                e => e.id === this.current_question.id);

            this.current_question = (
                this.questions[(current_index + 1) % this.questions.length]);
            
            this.populate_question_sessions();
        },
        populate_question_sessions: function() {
            if (this.current_question) {
                const self = this;
                const url = (
                    '/api/chime/'
                    + this.chime_id
                    + '/folder/'
                    + this.folder_id
                    + '/question/'
                    +  this.current_question.id);
                
                axios.get(url)
                .then(res => {
                    self.sessions = res.data;

                    self.current_session = (
                        self.sessions.find(e => e.in_progress === "1"));
                    
                    self.add_session_listeners(self.current_session.id);
                })
                .catch(err => {
                    console.log(err.response);
                })
            }
        },
        add_session_listeners: function(session_id) {
            const self = this;

            Echo.private('session-status.'+ session_id)
                .listen('ChangeSessionStatus', m => {
                    console.log(m);
                    self.current_session = m.session;
                    self.current_session.in_progress = (
                        m.session.in_progress !== '0' ? true : false
                    );
                });
        }
    },
    created: function () {
        this.chime_id = this.getCurrentChime();
        
        const self = this;
        const url = (
            '/api/chime/'
            + this.chime_id
            + '/folder/'
            + this.folder_id
        );

        axios.get(url)
        .then(res => {
            new Promise((resolve) => {
                res.data.questions.forEach((e) => {
                    e.question_info = JSON.parse(e.question_info);
                });

                resolve(res.data.questions);
            })
            .then((formatted_questions) => {
                const question_id = (
                    queryString.parse(location.search).question_id);
                self.current_question = (
                    formatted_questions.find(
                        e => parseInt(e.id) === parseInt(question_id)));
                
                self.questions = formatted_questions;
                
                if (!self.current_question && self.questions.length > 0) {
                    self.current_question = self.questions[0];
                }

                self.populate_question_sessions();
            });
        })
        .catch(err => {
            console.log(err);
        });
        Echo.private('start-session.' + this.chime_id)
            .listen('StartSession', m => {
                console.log(m);
                self.sessions.push(m.session);

                if (m.session.question_id == self.current_question.id) {
                    console.log('match')
                    self.current_session = m.session;
                    self.add_session_listeners(self.current_session.id);
                }
            });
    }
});
