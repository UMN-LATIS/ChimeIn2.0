<template>
    <div>
        <navbar
        :title="chime.name"
        :user="user"
        :link="'/'">
    </navbar>


    <div class="container">
        <b-card no-body>
        <b-tabs  card class="nav-fill">
            <b-tab title="Current Questions" active >
               <transition-group name="fade">
                <div v-if="sessions.length < 1" key='none' class="text-center">
                    <h3>No Open Questions</h3>
                </div>
                <student-prompt
                v-else
                v-for="s in sessions"
                v-on:updateResponse="updateResponse"
                :session="s"
                :chime="chime"
                :responses="responses"
                :key="s.id">
                    </student-prompt>
                </transition-group>
            </b-tab>
            <b-tab title="Past Responses">
                <div v-if="responses.length < 1" class="text-center">
                    <h3>No Responses Yet!</h3>
                </div>
                <response
                v-else
                v-for="response, i in responses"
                :chime="chime"
                :response="response"
                :key="i">
                </response>
            </b-tab>
        </b-tabs>
    </b-card>
    </div>
</div>

</template>


<script>
export default {
    data() {
        return {
            chime: {},
            sessions: [],
            responses: []
        };
    },
    props: ['user', 'chimeId'],
    methods: {
        updateResponse: function(newResponse) {
            var updateInPlace = false;
            this.responses.forEach((response, index) => {    
                if(response.id == newResponse.id) {
                    updateInPlace = true;
                    Vue.set(this.responses, index, newResponse);
                }
            })
            if(!updateInPlace) {
                this.responses.push(newResponse);
            }

        }
    },
    created: function () {
        const self = this;
        
        axios.get('/api/chime/' + this.chimeId)
        .then(res => {
            console.log('debug', 'chime:', res);
            self.chime = res.data.chime;
            document.title = self.chime.name;
            self.sessions = res.data.sessions;

        })
        .catch(err => {
            console.error('error getting sessions:', err.response);
        });

        axios.get('/api/chime/' + this.chimeId + "/responses")
        .then(res => {
            console.log('debug', 'Response:', res);
            self.responses= res.data;

        })


        Echo.private('session-status.' + this.chimeId)
        .listen('StartSession', m => {
            console.log('debug', 'message:', m);
            self.sessions.push(m.session);
        })
        .listen('EndSession', m => {
            var removeIndex = self.sessions.findIndex(session => session.id == m.session.id);
            self.sessions.splice(removeIndex, 1);
        });

    },
    beforeDestroy: function() {
        Echo.leave('session-status.' + this.chimeId);
    }
};
</script>