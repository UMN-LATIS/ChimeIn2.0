<template>
    <ol type="A">
        <li v-for="(r, i) in question.question_info.question_responses"
            :key="i"
            class="left-align">
            {{r}}
        </li>
    </ol>
</template>

<script>
export default {
    props: ['question']
}
</script>
