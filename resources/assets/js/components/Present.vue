<template>
    <div>
        <navbar
            :title="'Present'"
            :user="user"
            :link="'/chime/' + chime_id">
        </navbar>

        <br />

        <div class="row">
            <div class="col s12 m8 l8">
                <results-display
                    v-if="show_results"
                    :sessions="sessions"
                    :session="current_session"
                    :question="current_question">
                </results-display>
                <presentation-prompt
                    v-else
                    :question="current_question"
                    :session="current_session">
                </presentation-prompt>
            </div>
            <div class="col s12 m4 l4">
            <presentation-actions
                v-on:nextquestion="next_question"
                v-on:startsession="start_session"
                v-on:stopsession="stop_session"
                v-on:viewresults="view_results">
            </presentation-actions>
            </div>
        </div>
    </div>

</template>

<script>
export default {
    data() {
        return {
        chime_id: null,
        folder_id: window.location.pathname.split('/')[4],
        questions: [],
        sessions: [],
        current_question: null,
        current_session: null,
        show_results: false
        };
    },
    props: ['user'],
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
};
</script>