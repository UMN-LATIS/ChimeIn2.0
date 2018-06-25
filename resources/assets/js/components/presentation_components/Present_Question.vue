<template>
    <b-row>
        <b-col sm="12" md="10">

            <results-display
            v-if="show_results"
            :question="question">
        </results-display>
        <presentation-prompt
        v-if="!show_results"
        :question="question">
    </presentation-prompt>
</b-col>
<b-col sm="12" md="2">
<p>Responses: {{ current_session()?current_session().responses.length:0 }}</p>
    <button
    class="btn btn-outline-primary"
    v-on:click="start_session" v-if="!current_session()">
    <i class="material-icons left">play_arrow</i>
    Start Session
</button>
<button
class="btn btn-outline-primary"
v-on:click="stop_session" v-else>
<i class="material-icons left">stop</i>
Stop Session
</button>
<button
class="btn btn-outline-primary"
v-on:click="show_results = !show_results">
<i class="material-icons left">zoom_in</i>
View Results
</button>
<button
class="btn btn-outline-primary" @click="$emit('nextQuestion')">
<i class="material-icons left">arrow_right</i>
Next Question
</button>
<button
class="btn btn-outline-primary" @click="$emit('previousQuestion')">
<i class="material-icons left">arrow_left</i>
Previous Question
</button>
</b-col>
</b-row>
</template>

<script>
export default {
    props: ['question', 'chime_id', 'folder_id'],
    data() {
        return {
            show_results: false,
        };
    },
    methods: {
        start_session: function() {
            const url = (
                '/api/chime/'
                + this.chime_id
                + '/folder/'
                + window.location.pathname.split('/')[4]
                + '/question/'
                + this.question.id);

            axios.post(url, {})
            .then(res => {

            })
            .catch(err => {
                console.log(err.response);
            });
        },
        stop_session: function() {
            const url = (
                '/api/chime/'
                + this.chime_id
                + '/folder/'
                + this.folder_id
                + '/question/'
                + this.question.id
                + '/stopSession/'
                );

            axios.put(url, {})
            .then(res => {

            })
            .catch(err => {
                console.log(err.response);
            });
        },
        current_session: function() {
            if(this.question.current_session_id) {

                var session = this.question.sessions.find(s => s.id == this.question.current_session_id);
                console.log(this.question.sessions);
                console.log("current session:", this.question.current_session_id);
                console.log("session", session);
                return session;

            }
            else {
                return false;
            }
            

        }
    }
};
</script>

<style>

</style>

