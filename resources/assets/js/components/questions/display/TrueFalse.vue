<template>
    <ol type="A">
        <li class="questionDisplay" v-for="question in question.question_info.question_responses">
            {{question}}
        </li>
    </ol>
</template>

<script>
export default {
    props: ['question']
};
</script>


<style>



</style>