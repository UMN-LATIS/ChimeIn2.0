<template>
    <div class="card">
        <div class="card-content">
            <a
                class="waves-effect waves-light btn"
                v-on:click="start_session">
                <i class="material-icons left">play_arrow</i>
                Start Session
            </a>
            <a
                class="waves-effect waves-light btn"
                v-on:click="stop_session">
                <i class="material-icons left">stop</i>
                Stop Session
            </a>
            <a
                class="waves-effect waves-light btn"
                v-on:click="view_results">
                <i class="material-icons left">zoom_in</i>
                View Results
            </a>
            <a
                class="waves-effect waves-light btn"
                v-on:click="next_question">
                <i class="material-icons left">forward</i>
                Next Question
            </a>
        </div>
    </div>
</template>

<script>
export default {
    methods: {
        start_session: function() {
            this.$emit('startsession');
        },
        stop_session: function() {
            this.$emit('stopsession');
        },
        next_question: function() {
            this.$emit('nextquestion');
        },
        view_results: function() {
            this.$emit('viewresults');
        }
    }
}
</script>


<style>
    a {
        width: 90%;
        margin-bottom: 15px;
    }
    .card-content {
        text-align: center;
    }
</style>

