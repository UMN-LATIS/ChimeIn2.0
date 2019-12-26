<template>
    <ol type="A">
        <li class="questionDisplay" v-for="response in question.question_info.question_responses">
            {{  isObject(response)?response.text:response }}
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