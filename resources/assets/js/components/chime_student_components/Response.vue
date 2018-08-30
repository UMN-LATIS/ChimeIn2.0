<template>
    <div class="row">
        <div class="col-12">
            <div class="card mt-2 bg-light">
            <div
                class="card-body"
                v-if="response.session.question">
                <p class="card-title" v-html="response.session.question.text"></p>
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
        </div>
    </div>
</template>

<style>
</style>
<script>
export default {
  props: ["response"]
};
</script>
