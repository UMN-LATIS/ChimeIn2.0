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

            <multiple-choice-statistics
                v-if="question.question_info.question_type === 'multiple_choice'"
                :responses="responses"
                :question="question">
            </multiple-choice-statistics>
            <free-response-statistics
                v-else
                :responses="responses"
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
            visible_sessions: this.sessions.slice(0, 10),
            current_session: this.session || this.sessions[0],
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
            
            this.visible_sessions = this.sessions.slice(
                this.starting_index, this.starting_index+10);
        },
        next_session_block: function() {
            if (this.starting_index < this.sessions.length - 10) {
                this.starting_index += 10;
            }

            this.visible_sessions = this.sessions.slice(
                this.starting_index, this.starting_index+10);
        },
        get_session_responses: function() {
            if (this.current_session) {
                const self = this;
                const url = (
                    '/api/chime/'
                    + window.location.pathname.split('/')[2]
                    + '/folder/'
                    + window.location.pathname.split('/')[4]
                    + '/question/'
                    + this.question.id
                    + '/session/'
                    + this.current_session.id);

                axios.get(url)
                .then(res => {
                    console.log('session responses:', res);
                    self.responses = res.data;
                    self.responses.forEach(r => {
                        r.response_info = JSON.parse(r.response_info);
                    })
                })
                .catch(err => {
                    console.log(err.response);
                });
            }
        },
        listen_for_responses: function() {
            if (this.current_session) {
                const self = this;

                Echo.private('session-response.' + this.current_session.id)
                    .listen('SubmitResponse', m => {
                        console.log('response submitted:', m);
                        m.response.response_info = (
                            JSON.parse(m.response.response_info));

                        const response_index = (
                            self.responses.findIndex(
                                e => e.id === m.response.id));

                        if (response_index > -1) {
                            self.responses[response_index] = m.response;
                            self.responses = self.responses.map(
                                e => e);
                        } else {
                            self.responses.push(m.response);
                        }
                    });
            }
        }
    },
    created: function() {
        this.get_session_responses();
        this.listen_for_responses();
    },
    watch: {
        sessions: function() {
            this.visible_sessions = this.sessions.slice(0, 10);
        },
        session: function() {
            if (this.session) {
                const sessions_index = this.sessions.findIndex(
                    e => e.id === this.session.id);

                this.current_session = this.session;
                this.starting_index = sessions_index - 5;
                this.visible_sessions = this.sessions.slice(
                    this.starting_index, this.starting_index+10);
            } else {
                this.current_session = this.sessions[0];
            }
        },
        current_session: function() {
            this.get_session_responses();
            this.listen_for_responses();
        }
    }
}
</script>
