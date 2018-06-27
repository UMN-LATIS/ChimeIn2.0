<template>
    <div class="card">
        <div
            class="card-content"
            v-bind:class="{
                in_progress: (
                    session
                    && (session.in_progress && session.in_progress != '0')
                    && session.question_id == question.id)
            }">
            <div class="card">
                <div class="card-content">
                    <p class="flow-text" v-html="question.text"></p>
                </div>
            </div>

            <span class="card-title truncate" v-bind:title="question.text">
                <h6>Session:</h6>
            </span>
<!-- 
            <ul class="pagination center">
                <li v-bind:class="
                    [starting_index === 0 ? 'disabled' : 'waves-effect']">
                    <a v-on:click="previous_session_block">
                        <i class="material-icons">chevron_left</i>
                    </a>
                </li>
                <li
                    v-for="s in visible_sessions"
                    :key="s.id"
                    v-bind:class="[current_session.id === s.id ? 'active' : 'waves-effect']">
                    <a href="#!" v-on:click="() => {current_session = s;}">
                        {{ sessions.indexOf(s) + 1 }}
                    </a>
                </li>
                <li v-bind:class="
                    [starting_index >= sessions.length - 10
                        ? 'disabled'
                        : 'waves-effect']">
                    <a v-on:click="next_session_block">
                        <i class="material-icons">chevron_right</i>
                    </a>
                </li>
            </ul>
 -->
            <multiple-choice-statistics
                v-if="question.question_info.question_type === 'multiple_choice'"
                :responses="current_session.responses"
                :question="question">
            </multiple-choice-statistics>
            <image-response-statistics
                v-else-if="question.question_info.question_type === 'image_response'"
                :responses="responses"
                :question="question">
            </image-response-statistics>
            <free-response-statistics
                v-else
                :responses="current_session.responses"
                :question="question">
            </free-response-statistics>
        </div>
    </div>
</template>

<script>
export default {
    props: ['sessions', 'session', 'question'],
    data: function() {
        return {
            starting_index: 0,
            visible_sessions: this.question.sessions.slice(0, 10),
            // current_session: this.question.session || this.question.sessions[0],
            responses: []
        }
    },
    methods: {
        previous_session_block: function() {
            if (this.starting_index >= 10) {
                this.starting_index -= 10;
            } else {
                this.starting_index = 0;
            }
            
            this.visible_sessions = this.question.sessions.slice(
                this.starting_index, this.starting_index+10);
        },
        next_session_block: function() {
            if (this.starting_index < this.question.sessions.length - 10) {
                this.starting_index += 10;
            }

            this.visible_sessions = this.question.sessions.slice(
                this.starting_index, this.starting_index+10);
        },
        
    },
    computed: {
        current_session: function() {
            if(this.question.current_session_id) {

                var session = this.question.sessions.find(s => s.id == this.question.current_session_id);
                console.log(this.question.sessions);
                return session;

            }
            else {
                return false;
            }
        }
    },
    created: function() {

    },
    watch: {
        sessions: function() {
            this.visible_sessions = this.question.sessions.slice(0, 10);
        },
        session: function() {
            if (this.question.session) {
                const sessions_index = this.question.sessions.findIndex(
                    e => e.id === this.question.session.id);

                this.current_session = this.question.session;
                this.starting_index = sessions_index - 5;
                this.visible_sessions = this.question.sessions.slice(
                    this.starting_index, this.starting_index+10);
            } else {
                this.current_session = this.question.sessions[0];
            }
        }
    }
}
</script>
