<template>
    <nav class="nav-wrapper" role="navigation">
        <div class="brand-logo center">
            <ul>
                <li>
                    <a
                        v-on:click="select_current_questions">
                        Current Questions
                    </a>
                </li>
                <li>
                    <a
                        v-on:click="select_past_responses">
                        Past Responses
                    </a>
                </li>
            </ul>
        </div>
    </nav>
</template>

<script>
export default {
    methods: {
        select_current_questions: function() {
            this.$emit('selectcurrentquestions');
        },
        select_past_responses: function() {
            this.$emit('selectpastresponses');
        }
    }
}
</script>
