<template>
  <div>
    <fieldset class="form-group" role="radiogroup">
      <div class="form-check" :key="key" v-for="(option, key) in selectOptions">
        <input
          class="form-check-input"
          v-model="selected"
          :disabled="disabled"
          :type="question.allow_multiple ? 'checkbox' : 'radio'"
          :id="'radio' + question.id + '_' + key"
          :value="option"
        />
        <label
          class="form-check-label"
          :for="'radio' + question.id + '_' + key"
          v-html="option"
        />
      </div>
    </fieldset>
  </div>
</template>

<script>
import isObject from "lodash/isObject";

export default {
  props: ["question", "response", "disabled"],
  data() {
    return {
      selected: [],
    };
  },
  computed: {
    selectOptions: function() {
      return this.question.question_info.question_responses.map((response) =>
        isObject(response) ? response.text : `<p>${response}</p>`
      );
    },
  },
  watch: {
    selected: function(newValue, value) {
      if (
        newValue !== null &&
        newValue !== value &&
        !(
          this.response &&
          this.response.response_info &&
          newValue == this.response.response_info.choice
        )
      ) {
        const response = {
          question_type: "multiple_choice",
          choice: newValue,
        };
        this.$emit("recordresponse", response, false);
      }
    },
    response: function(value) {
      if (this.response && this.response.response_info) {
        this.selected = this.response.response_info.choice;
      }
    },
  },
  mounted() {
    if (
      this.response &&
      this.response.hasOwnProperty("response_info") &&
      this.response.response_info.hasOwnProperty("choice")
    ) {
      this.selected = this.response.response_info.choice;
    }
  },
};
</script>
