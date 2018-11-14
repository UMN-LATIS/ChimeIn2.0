<template>
    <div>
        <navbar
        :title="this.questions[0].folder.name"
        :user="user"
        :link="{name:'chime', params:{chimeId: chimeId}}">
    </navbar>


        <div class="container-fluid">
    
        <fullscreen ref="fullscreen" @change="fullscreenChange" background="white">
        <template v-for="(question,index) in questions"x>
            <present-question :question="question" :chimeId="chimeId" :folderId="folderId" v-if="index == current_question" @nextQuestion="next_question" @previousQuestion="previous_question" @sessionUpdated="load_questions" @toggle="toggle">
            </present-question>
        </template>
        </fullscreen>
        </div>
</div>

</template>

<script>
export default {
    data() {
        return {
            questions: [],
            show_results: false,
            current_question: 0,
            fullscreen: false
        };
    },
    props: ['user', 'chimeId', 'folderId', 'questionId'],
    methods: {
           toggle () {
        this.$refs['fullscreen'].toggle() // recommended
        // this.fullscreen = !this.fullscreen // deprecated
      },
        fullscreenChange (fullscreen) {
            this.fullscreen = fullscreen
        },
        next_question: function() {
            var target = 0;
            if(this.questions.length > this.current_question + 1) {
                target = this.current_question + 1;
            }
            this.$router.replace({ name: 'present', params: { chimeId: this.chimeId, folderId: this.folderId, questionId: target }})
        },
        previous_question: function() {
            var target = this.current_question - 1;
            if(this.current_question - 1 < 0) {
                target = this.questions.length - 1;
            }
            this.$router.replace({ name: 'present', params: { chimeId: this.chimeId, folderId: this.folderId, questionId: target }});
        },
        load_questions: function() {
            const url = (
                '/api/chime/'
                + this.chimeId
                + '/folder/'
                + this.folderId
                );

            axios.get(url)
            .then(res => {                
                this.questions = res.data;
                console.log(this.questions);
            })
            .catch(err => {
                console.log(err);
            });
        }
    },
    watch: {
        '$route' (to, from) {
            this.current_question = parseInt(to.params.questionId);
        }
    },
    mounted: function () {
        this.current_question = parseInt(this.$route.params.questionId) || 0;

        this.load_questions();

        var self=this;

        Echo.private('session-status.' + this.chimeId)
        .listen('StartSession', m => {
            self.load_questions();
        })
         .listen('EndSession', m => {
            this.load_questions();
        });;

        Echo.private('session-response.'+ this.chimeId)
        .listen('SubmitResponse', m => {


            // none of this code is right but I'm having trouble thinking of the right way to do it.

            var targetSession = null;
            this.questions.forEach(question => {
                question.sessions.forEach(session => {
                    if(session.id == m.session.id) {
                        targetSession = session;
                    }
                })
            });



            if(!targetSession) {
                console.log("Session does not exist.  Weird");
            }

            if(!targetSession.hasOwnProperty('responses')) {
                targetSession.responses = new Array();
            }

            var updateInPlace = false;
            if(m.isEdit) {
                targetSession.responses.forEach((response, index) => {
                    if(response.id == m.response.id) {
                        Vue.set(targetSession.responses, index, m.response);
                        updateInPlace = true;
                    }
                });
    
            }
            
            if(!updateInPlace) {
                targetSession.responses.push(m.response);    
            }
        });
    }
};
</script>