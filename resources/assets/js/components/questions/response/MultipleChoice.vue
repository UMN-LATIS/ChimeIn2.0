<template>
    <form>
        <p v-for="(r, i) in question.question_info.question_responses" :key="i">
            <input
                type="radio"
                v-bind:name="'question_' + question.id"
                v-bind:id="'question_' + question.id + '_choice_' + i"
                v-on:click="() => record_response(i)"
                v-bind:disabled="disabled"
                v-bind:checked="
                    response.response_info &&
                    r === response.response_info.choice"/>
            <label
                v-bind:for="'question_' + question.id + '_choice_' + i">
                {{r}}
            </label>
        </p>
    </form>
</template>

<script>
export default {
    props: ['question', 'response', 'disabled'],
    methods: {
        record_response(i) {
            const response = {
                question_type: 'multiple_choice',
                choice: this.question.question_info.question_responses[i]
            }

            this.$emit('recordresponse', response);
        }
    }
}
</script>
