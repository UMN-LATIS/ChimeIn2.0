<template>

    <div class="questionContent row">
        <div v-if="question" class="col">
        <h1 v-html="question.text"></h1>
            <component :is="question.question_info.question_type + '_display'" :question="question"/>
        </div>
        <div v-else>
            <span>
                <h4>There aren't any questions in this folder</h4>
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
    /* margin-left: 5%; */
    /* margin-top: 2em; */
}

</style>