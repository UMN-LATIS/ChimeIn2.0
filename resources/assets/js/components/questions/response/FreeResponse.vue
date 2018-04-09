<template>
    <div>
        <input
            type="text"
            v-bind:id="'question_' + question.id + '_response'"
            class="materialize-textarea"
            v-on:keyup.enter="record_response"
            v-bind:disabled="disabled"
            v-bind:value="
                response.response_info ? response.response_info.text : ''"/>
        <label v-bind:for="'question_' + question.id + '_response'">
            Response
        </label>
    </div>
</template>

<script>
export default {
    props: ['question', 'response', 'disabled'],
    methods: {
        record_response: function() {
            const response_text = document.getElementById(
                'question_' + this.question.id + '_response').value;
            
            const response = {
                question_type: 'free_response',
                text: response_text
            }


            this.$emit('recordresponse', response);
        },
    }
}
</script>
