<template>
    <div>
        <navbar
            :title="chime.name"
            :user="user"
            :link="'/'">
        </navbar>

        <br />
        
        <div class="container center-align">
            <student-actions
                v-on:selectcurrentquestions="() => {show = 'current_questions'}"
                v-on:selectpastresponses="() => {show = 'past_responses'}"
            ></student-actions>
            
            <br />

            <div v-if="show === 'current_questions'">
                <transition-group name="fade">
                <div v-if="sessions.length < 1" key='none'>
                    <h3>No Open Sessions!</h3>
                </div>
                <student-prompt
                    v-else
                    v-for="s in sessions"
                    :session="s"
                    :key="s.id">
                </student-prompt>
                </transition-group>
            </div>

            <div v-if="show === 'past_responses'">
                <div v-if="responses.length < 1">
                    <h3>No Responses Yet!</h3>
                </div>
                <response
                    v-else
                    v-for="(r, i) in responses"
                    :question="r.question"
                    :response="r.response"
                    :key="i">
                </response>
            </div>
        </div>
    </div>

</template>


<script>
export default {
    data() {
        return {
            show: 'current_questions',
            chime: {},
            sessions: [],
            responses: []
        };
    },
    props: ['user'],
    methods: {
        add_session_listeners: function(session_id) {
            const self = this;

            Echo.private('session-status.'+ session_id)
                .listen('ChangeSessionStatus', m => {
                    console.log('debug', 'ChangeSessionStatus:', m)
                    let session = m.session;

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
                // res.data.forEach(e => {
                //     e.question.question_info = JSON.parse(e.question.question_info);
                //     e.response.response_info = JSON.parse(e.response.response_info);
                // });
                
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
                self.add_session_listeners(e.id);
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
};
</script>