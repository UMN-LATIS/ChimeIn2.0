<template>
    <div class="card hoverable">
        <div
            class="card-content"
            v-if="question">
            <div class="card">
                <div class="card-content">
                    <p class="flow-text" v-html="question.text"></p>
                </div>
            </div>

            <multiple-choice-question
                v-if="question.question_info.question_type === 'multiple_choice'"
                :question="question"
                :disabled="true"
                :response="response">
            </multiple-choice-question>
            <image-response-question
                v-else-if="question.question_info.question_type === 'image_response'"
                :question="question"
                :response="response"
                :disabled="true"
            ></image-response-question>
            <free-response-question
                v-else
                :question="question"
                :response="response"
                :disabled="true"
            ></free-response-question>
        </div>
        <div class="card-content" v-else>
            <span class="card-title">
                <h4>'No Question Yet!'</h4>
            </span>
        </div>
    </div>
</template>

<script>
export default {
    props: ['response', 'question']
}
</script>
