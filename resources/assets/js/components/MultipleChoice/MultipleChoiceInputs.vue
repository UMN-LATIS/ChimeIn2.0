<template>
  <div data-cy="multiple-choice-participant-choices">
    <fieldset
      class="form-group"
      role="radiogroup"
      :aria-labelledby="`question-${question.id}-heading`"
    >
      <div v-for="(option, key) in selectOptions" :key="key" class="form-check">
        <input
          :id="'radio' + question.id + '_' + key"
          v-model="selected"
          class="form-check-input"
          :disabled="disabled"
          :type="question.allow_multiple ? 'checkbox' : 'radio'"
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
import get from "lodash/get";
export default {
  // eslint-disable-next-line vue/require-prop-types
  props: ["question", "response", "disabled"],
  emits: ["recordresponse"],
  data() {
    return {
      selected: [],
    };
  },
  computed: {
    selectOptions: function () {
      return this.question.question_info.question_responses.map(
        (response) => response.text
      );
    },
  },
  watch: {
    selected: function (newValue, value) {
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
    response: function () {
      if (this.response && this.response.response_info) {
        this.selected = this.response.response_info.choice;
      }
    },
  },
  mounted() {
    this.selected = get(this, "response.response_info.choice", this.selected);
  },
};
</script>
