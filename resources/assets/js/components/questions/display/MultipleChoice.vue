<template>
    <ol type="A">
        <li class="questionDisplay" v-for="response in question.question_info.question_responses" :key="response.text">
            {{  isObject(response) ? response.text : response }}
        </li>
    </ol>
</template>

<script>
import isObject from 'lodash/isObject';

export default {
    props: ['question'],
    methods: {
        isObject
    }
};
</script>