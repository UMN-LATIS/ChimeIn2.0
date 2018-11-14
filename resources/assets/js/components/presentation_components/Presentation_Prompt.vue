<template>

    <div class="questionContent">
        <div
            v-if="question"
            >
        <h1 v-html="question.text"></h1>
        <multiple-choice-display
                v-if="question.question_info.question_type === 'multiple_choice'"
                :question="question">
            </multiple-choice-display>
            
            <div v-else>
                {{ question.question_info.question_type
                    .split('_')
                    .map(e => e[0]
                    .toUpperCase() + e.slice(1))
                    .join(' ') }}
            </div>
        </div>
        <div v-else>
            <span>
                <h4>No Question Yet!</h4>
            </span>
        </div>

    </div>
</template>

<script>
export default {
    props: ['question', 'session'],
}
</script>

<style>


.questionContent {
    margin-left: 5%;
    margin-top: 2em;
}

</style>