
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

Vue.component('navbar',
    require('./components/Navbar.vue'));
Vue.component('actions',
    require('./components/chime_student_components/Actions.vue'));
Vue.component('prompt',
    require('./components/chime_student_components/Prompt.vue'));
Vue.component('response',
    require('./components/chime_student_components/Response.vue'));
Vue.component('multiple-choice-question',
    require('./components/questions/response/MultipleChoice.vue'));
Vue.component('image-response-question',
    require('./components/questions/response/ImageResponse.vue'));
Vue.component('free-response-question',
    require('./components/questions/response/FreeResponse.vue'));

const app = new Vue({
    el: '#app',
    data: function() {
        return {
            show: 'current_questions',
            chime: {},
            sessions: [],
            responses: []
        };
    },
    methods: {
        add_session_listeners: function(session_id) {
            const self = this;

            Echo.private('session-status.'+ session_id)
                .listen('ChangeSessionStatus', m => {
                    console.log('debug', 'message:', m)
                    let session = m.session;
                    session.in_progress = (
                        m.session.in_progress !== '0' ? true : false
                    );

                    const i = self.sessions.findIndex(e => e.id === session.id);
                    self.sessions[i] = session;
                    self.sessions = self.sessions.filter(s => s.in_progress);

                    self.get_past_responses();
                });
        },
        get_past_responses: function() {
            const self = this;

            axios.get(
                '/api/chime/'
                + this.getCurrentChime()
                + '/response')
            .then(res => {
                res.data.forEach(e => {
                    e.question.question_info = JSON.parse(e.question.question_info);
                    e.response.response_info = JSON.parse(e.response.response_info);
                });
                
                self.responses = res.data;
                console.log('debug', 'past responses:', self.responses);
            })
            .catch(err => {
                console.error('error getting past responses', err.response);
            });
        }
    },
    created: function () {
        const self = this;
        
        axios.get('/api/chime/' + this.getCurrentChime())
        .then(res => {
            console.log('debug', 'chime:', res);
            self.chime = res.data.chime;
            document.title = self.chime.name;
            self.sessions = res.data.sessions;
            console.log('debug', 'self.sessions', self.sessions);
    
            self.sessions.forEach((e, i) => {
                Echo.private('session-status.'+ e.id)
                .listen('ChangeSessionStatus', m => {
                    e = m.session;
                    e.in_progress = (
                        m.session.in_progress !== '0' ? true : false
                    );
    
                    self.sessions[i] = e;
                    self.sessions = self.sessions.filter(s => s.in_progress);
                });
            });
        })
        .catch(err => {
            console.error('error getting sessions:', err.response);
        });


        Echo.private('start-session.' + this.getCurrentChime())
            .listen('StartSession', m => {
                console.log('debug', 'message:', m);
                self.sessions.push(m.session);
                self.add_session_listeners(m.session.id);
            });

        this.get_past_responses();
    }
});
