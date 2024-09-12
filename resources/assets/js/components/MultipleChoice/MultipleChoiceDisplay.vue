<template>
  <ol
    type="A"
    class="mult-choice-display"
    data-cy="multiple-choice-options-list"
  >
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
<style>
.mult-choice-display img {
  max-height: 10rem;
  height: auto;
  width: auto;
}
</style>
