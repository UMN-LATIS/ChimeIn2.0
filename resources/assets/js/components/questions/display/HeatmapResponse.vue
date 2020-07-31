<template>
     <img class="img-fluid" v-bind:src="'/storage/' + question.question_info.question_responses.image">
</template>

<script>
export default {
    props: ['question']
};
</script>


<style>



</style>