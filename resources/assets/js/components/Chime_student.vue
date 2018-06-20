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

    },
    created: function () {
        const self = this;
        
        axios.get('/api/chime/' + this.getCurrentChime())
        .then(res => {
            console.log('debug', 'chime:', res);
            self.chime = res.data.chime;
            document.title = self.chime.name;
            self.sessions = res.data.sessions;

        })
        .catch(err => {
            console.error('error getting sessions:', err.response);
        });

        axios.get('/api/chime/' + this.getCurrentChime() + "/responses")
        .then(res => {
            console.log('debug', 'Response:', res);
            self.responses= res.data;

        })


        Echo.private('session-status.' + this.getCurrentChime())
            .listen('StartSession', m => {
                console.log('debug', 'message:', m);
                self.sessions.push(m.session);
            })
            .listen('EndSession', m => {
                var removeIndex = self.sessions.findIndex(session => session.id == m.session.id);
                self.sessions.splice(removeIndex, 1);
            });

    }
};
</script>