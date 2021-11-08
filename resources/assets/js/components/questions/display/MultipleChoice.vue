<template>
  <ol type="A" data-cy="multiple-choice-options-list">
    <li
      class="questionDisplay"
      v-for="response in responses"
      :key="response"
      v-html="response"
    />
  </ol>
</template>

<script>
import isObject from "lodash/isObject";

export default {
  props: ["question"],
  computed: {
    responses() {
      return this.question.question_info.question_responses.map((response) =>
        isObject(response) ? response.text : `<p>${response}</p>`
      );
    },
  },
};
</script>
