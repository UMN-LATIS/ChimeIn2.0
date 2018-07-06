<template>
    <div class="row">
        <div class="col">
            <div class="row">
                <div class="col">
                    <p class="flow-text" v-html="question.text"></p>
                </div>
            </div>

            <template v-if="this.question.sessions.length > 0">
            
                <b-form-select v-model="selected" :options="question.sessions.map(el => ({'value':el.id, 'text': el.created_at})).concat({'value':0, 'text':'All'})" class="mb-3" />
                <multiple-choice-statistics
                    v-if="question.question_info.question_type === 'multiple_choice' && selected_session"
                    :responses="selected_session.responses"
                    :question="question">
                </multiple-choice-statistics>
                <image-response-statistics
                    v-else-if="question.question_info.question_type === 'image_response' && selected_session"
                    :responses="selected_session.responses"
                    :question="question">
                </image-response-statistics>
                <free-response-statistics
                    v-else-if="question.question_info.question_type == 'free_response' && selected_session"
                    :responses="selected_session.responses"
                    :question="question">
                </free-response-statistics>
            </template>
            <template v-else>
                <p>No sessions yet</p>
            </template>
        </div>
    </div>
</template>

<script>
export default {
    props: ['sessions', 'session', 'question'],
    data: function() {
        return {
            selected: null
        }
    },
    methods: {
        updateSelected() {
             if(this.question.current_session_id) {
                this.selected = this.question.current_session_id;
            }
            else if(this.question.sessions.length > 0) {
                this.selected = this.question.sessions.slice(-1).pop().id;
            }
        }
    },
    computed: {
        selected_session: function() {
            if(this.selected === 0) {
                var newSession = {};
                var responses = this.question.sessions.map(s=> s.responses);
                newSession.responses = Array.prototype.concat(...responses);
                return newSession;
            }
            else {
                return this.question.sessions.find(s => s.id == this.selected);    
            }
        }
    },
    mounted() {
       this.updateSelected();
    },
    watch: {
        question: function() {
            this.updateSelected();
        }
    }
}
</script>
