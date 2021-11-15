<template>
  <ol type="A" data-cy="multiple-choice-options-list">
    <li
      v-for="response in responses"
      :key="response"
      class="questionDisplay"
      v-html="response"
    />
  </ol>
</template>

<script>
export default {
  props: ["question"],
  computed: {
    responses() {
      return this.question.question_info.question_responses.map(
        (response) => response.text
      );
    },
  },
};
</script>
