<template>
    <div>
        <navbar
        title="Home"
        :user="user"
        :link="'/'">
    </navbar>

    <div class="alert alert-warning" role="alert" v-if="error">
        {{ error }}
    </div>
    <div class="container">
        <div class="card ">
          <div class="card-header text-center">
            <ul class="nav nav-tabs card-header-tabs">
              <li class="nav-item">
                <a class="nav-link active" data-toggle="tab" href="#currentQuestions">Open Questions</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#pastQuestions">Closed Questions</a>
            </li>
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content">
            <div class="tab-pane container active" id="currentQuestions">
                <div v-if="sessions.length < 1" key='none' class="text-center">
                    <h3>No Open Questions</h3>
                </div>
                <transition-group name="fade">
                    <student-prompt
                    v-if="sessions.length > 0"
                    v-for="s in sessions"
                    v-on:updateResponse="updateResponse"
                    :session="s"
                    :chime="chime"
                    :responses="responses"
                    :key="s.id">
                </student-prompt>
            </transition-group>
        </div>
        <div class="tab-pane container" id="pastQuestions">
            <div v-if="responses.length < 1" class="text-center">
                <h3>No Closed Questions</h3>
            </div>
            <response
            v-else
            v-for="response, i in responses"
            :chime="chime"
            :response="response"
            :key="i">
        </response>
    </div>

</div>
</div>
</div>
</div>
</div>


</template>

<style scoped>

 .nav-item {
     width:50%;
   }
   </style>
<script>
    export default {
        data() {
            return {
                chime: {},
                sessions: [],
                responses: [],
                error: null
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
        mounted: function () {
            axios.get('/api/chime/' + this.chimeId + '/openQuestions')
            .then(res => {
                console.log('debug', 'chime:', res);
                this.chime = res.data.chime;
                document.title = this.chime.name;
                this.sessions = res.data.sessions.reverse();
            })
            .catch(err => {
                if(err.response.data.status == "AttemptAuth") {
                    window.location = "/loginAndRedirect?target=" + window.location.pathname;
                }
                else {
                    if(err.response.data.message) {
                        this.error = err.response.data.message;    
                    }
                    console.log("error getting chime:", err.response);
                    
                }
            });

            axios.get('/api/chime/' + this.chimeId + "/responses")
            .then(res => {
                console.log('debug', 'Response:', res);
                this.responses= res.data;

            })


            Echo.private('session-status.' + this.chimeId)
            .listen('StartSession', m => {
                console.log('debug', 'message:', m);
                this.sessions.unshift(m.session);
            })
            .listen('EndSession', m => {
                var removeIndex = this.sessions.findIndex(session => session.id == m.session.id);
                this.sessions.splice(removeIndex, 1);
            });

        },
        beforeDestroy: function() {
            Echo.leave('session-status.' + this.chimeId);
        }
    };
</script>