<template>
    <div>
        <navbar
        title="Back to Folder"
        :user="user"
        :access_code="hyphenatedCode"
        :host="host"
        :link="{name:'folder', params:{chimeId: chimeId, folderId: folderId}}"
        v-if="!folder.student_view">
    </navbar>
    <error-dialog />

    <div class="container-fluid presentContainer">
        <fullscreen ref="fullscreen" @change="fullscreenChange" background="white">
            <present-question v-if="current_question_item" :usersCount="usersCount" :question="current_question_item" :chimeId="chimeId" :folder="folder" @nextQuestion="next_question" @previousQuestion="previous_question" @sessionUpdated="load_questions" @toggle="toggle" @reload="reload">
            </present-question>
            <div v-if="fullscreen && host" class="alert alert-info text-center fixed-bottom joinbox" role="alert">
                <span class="text-center">Go to <strong>{{ host }}</strong> and enter code <strong>{{ hyphenatedCode }}</strong></span>
            </div>
        </fullscreen>
    </div>
</div>

</template>

<style>

.presentContainer {
    min-height: 90%;
}
.joinbox {
    margin: 10px;
}

</style>

<script>
    import { questionsListener } from './mixins/questionsListener'
    import toHyphenatedCode from '../helpers/toHyphenatedCode.mjs';

    export default {
        mixins: [questionsListener],
        data() {
            return {
                folder: {name:""},
                questions: [],
                show_results: false,
                current_question: 0,
                fullscreen: false,
                chime: null
            };
        },
        props: ['user', 'chimeId', 'folderId', 'questionId'],
        methods: {
         toggle () {
            this.$refs['fullscreen'].toggle();
        },
        reload () {
            this.load_questions();
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
    computed: {
        current_question_item: function() {
            if(!this.questions || this.questions.length == 0)  {
                return false;
            }
            return this.questions[this.current_question];
        },
        host: function() {
            if(this.chime && this.chime.join_instructions) {
                return window.location.host;
            }
            return null;
            
        },
        hyphenatedCode: function () {
            if(!this.chime || !this.chime.join_instructions) {
                return "";
            }
            return toHyphenatedCode(this.chime.access_code);
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
        axios.get('/api/chime/' + this.chimeId)
        .then(res => {
            console.log(res);
            this.chime = res.data;
        }); 
    },
    beforeDestroy: function() {
        Echo.leave('session-status.' + this.chimeId);
    }

};
</script>