<template>
    <div class="row">
        <div class="col">
            <div class="row">
                <div class="col">
                    <h1 v-html="question.text"></h1>
                </div>
            </div>

            <template v-if="this.question.sessions.length > 0">
            
                <b-form-select v-model="selected" :options="question.sessions.map(el => ({'value':el.id, 'text': el.created_at})).concat({'value':0, 'text':'All'})" class="mb-3" />
                

                <component
                    v-if="selected_session"
                    :responses="selected_session.responses"
                    :question="question"
                    :is="question.question_info.question_type + '_statistics'"
                    >
                </component>
               
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
             // if(this.question.current_session_id) {
                // this.selected = this.question.current_session_id;
            // }
            // else if(this.question.sessions.length > 0) {
                this.selected = 0;
            // }
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
