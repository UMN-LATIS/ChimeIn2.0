<template>
    <div class="row responseContainer">
        <div
            class="col-12"
            v-if="response.session.question">
            <p class="questionText" v-html="response.session.question.text"></p>
            <multiple-choice-question
                v-if="response.session.question.question_info.question_type === 'multiple_choice'"
                :question="response.session.question"
                :disabled="true"
                :response="response">
            </multiple-choice-question>
            <image-response-question
                v-else-if="response.session.question.question_info.question_type === 'image_response'"
                :question="response.session.question"
                :response="response"
                :disabled="true"
            ></image-response-question>
            <free-response-question
                v-else-if="response.session.question.question_info.question_type === 'free_response'"
                :question="response.session.question"
                :response="response"
                :disabled="true"
            ></free-response-question>
        </div>
    </div>
</template>


<style>
.responseContainer {
    border: 1px solid black;
    border-radius: 5px;
    margin: 5px;
}

</style>

<script>
export default {
    props: ['response']
};
</script>
