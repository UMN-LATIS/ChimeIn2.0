<template>
    <div>
        <navbar
        :title="'Present'"
        :user="user"
        :link="'/chime/' + chime_id">
    </navbar>



    
        <template v-for="(question,index) in questions">
            <present-question :question="question" :chime_id="chime_id" :folder_id="folder_id" v-if="index == current_question" @nextQuestion="next_question" @previousQuestion="previous_question()" @sessionUpdated="load_questions">
            </present-question>
        </template>
</div>

</template>

<script>
export default {
    data() {
        return {
            chime_id: null,
            folder_id: window.location.pathname.split('/')[4],
            questions: [],
            show_results: false,
            current_question: 0,
        };
    },
    props: ['user'],
    methods: {
        next_question: function() {
            var target = 0;


            if(this.questions.length > this.current_question + 1) {
                target = this.current_question + 1;
            }
            this.$router.replace({ name: 'present', params: { id: target }})
        },
        previous_question: function() {
            var target = this.current_question - 1;
            if(this.current_question - 1 < 0) {
                target = this.questions.length - 1;
            }
            this.$router.replace({ name: 'present', params: { id: target }})  
        },
        load_questions: function() {
            const url = (
                '/api/chime/'
                + this.chime_id
                + '/folder/'
                + this.folder_id
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
            this.current_question = parseInt(to.params.id);
        }
    },
    mounted: function () {
        this.chime_id = this.getCurrentChime();
        this.current_question = parseInt(this.$route.params.id) || 0;

        this.load_questions();

        var self=this;

        Echo.private('session-status.' + this.chime_id)
        .listen('StartSession', m => {
            self.load_questions();
        })
         .listen('EndSession', m => {
            this.load_questions();
        });;

        Echo.private('session-response.'+ this.chime_id)
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
                targetSession.responses.forEach(response => {
                    if(response.id == m.response.id) {
                        // response = m.response;
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