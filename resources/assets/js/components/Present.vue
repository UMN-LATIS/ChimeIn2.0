<template>
    <div>
        <navbar
        title="Back to Folder"
        :user="user"
        :link="{name:'folder', params:{chimeId: chimeId, folderId: folderId}}">
    </navbar>


    <div class="container-fluid">

        <fullscreen ref="fullscreen" @change="fullscreenChange" background="white">
            <template v-for="(question,index) in questions">
                <present-question :question="question" :chimeId="chimeId" :folder="folder" v-if="index == current_question" @nextQuestion="next_question" @previousQuestion="previous_question" @sessionUpdated="load_questions" @toggle="toggle">
                </present-question>
            </template>
        </fullscreen>
    </div>
</div>

</template>

<script>

    import { questionsListener } from './mixins/questionsListener'

    export default {
        mixins: [questionsListener],
        data() {
            return {
                folder: {name:""},
                questions: [],
                show_results: false,
                current_question: 0,
                fullscreen: false
            };
        },
        props: ['user', 'chimeId', 'folderId', 'questionId'],
        methods: {
         toggle () {
            this.$refs['fullscreen'].toggle();
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
        
    },
    beforeDestroy: function() {
        Echo.leave('session-status.' + this.chimeId);
    }

};
</script>