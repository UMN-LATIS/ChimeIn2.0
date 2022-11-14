<template>
  <div>
    <div class="row value-slider">
      <div class="col">
        <div class="range-wrap">
          <input
            :id="`formControlRange-question-${question.id}`"
            :aria-labelledby="`question-${question.id}-heading`"
            type="range"
            :disabled="disabled"
            class="form-control-range custom-range range"
            :value="sliderValue"
            min="0"
            max="100"
            :aria-valuemin="left_choice_text"
            :aria-valuemax="right_choice_text"
            :aria-valuetext="inputValueText"
            data-cy="slider-response-input"
            @change="valueChanged($event.target.value)"
          />
          <output v-if="isNumericInput" class="bubble" :style="customStyle">
            {{ bubbleValue }}
          </output>
        </div>
      </div>
    </div>
    <div class="row justify-content-between">
      <div class="col-sm-2">
        <b>{{ left_choice_text }}</b>
      </div>
      <div class="col-sm-2 text-right">
        <b>{{ right_choice_text }}</b>
      </div>
    </div>
    <div v-if="question.allow_multiple && !disabled" class="form-group">
      <button class="btn btn-primary" @click="clear">
        Clear and Start a New Response
      </button>
    </div>
  </div>
</template>
<script>
import get from "lodash/get";

function isNumeric(value) {
  return /^-?\d+$/.test(value);
}

export default {
  // eslint-disable-next-line vue/require-prop-types
  props: ["question", "response", "disabled"],
  emits: ["recordresponse"],
  data() {
    return {
      create_new_response: false,
      customStyle: {
        left: "0px",
        right: "0px",
      },
      bubbleValue: "",
      bubbleOffset: 0,
    };
  },
  computed: {
    sliderValue: function () {
      if (this.create_new_response) {
        return 50;
      } else if (this.response && this.response.response_info) {
        return this.response.response_info.choice;
      } else {
        return 50;
      }
    },
    left_choice_text() {
      return get(
        this.question,
        "question_info.question_responses.left_choice_text",
        null
      );
    },
    right_choice_text() {
      return get(
        this.question,
        "question_info.question_responses.right_choice_text",
        null
      );
    },
    isNumericInput() {
      return (
        this.question.question_info.question_responses.range_type ==
          "Numeric (Linear)" ||
        // left choice and right choice texts are actually numbers
        (isNumeric(this.left_choice_text) && isNumeric(this.right_choice_text))
      );
    },
    inputValueText() {
      return this.isNumericInput
        ? this.bubbleValue
        : `${this.sliderValue}% between ${this.left_choice_text} and ${this.right_choice_text}`;
    },
  },
  watch: {
    sliderValue: function (newValue) {
      this.updateRange(newValue);
    },
  },
  mounted() {
    this.updateRange(this.sliderValue);
  },
  methods: {
    valueChanged: function (targetValue) {
      const response = {
        question_type: "slider",
        choice: targetValue,
      };
      this.$emit("recordresponse", response, this.create_new_response);
      this.create_new_response = false;
    },
    clear: function () {
      this.create_new_response = true;
    },
    updateRange: function (newValue) {
      let range =
        parseInt(this.right_choice_text) - parseInt(this.left_choice_text);
      this.bubbleValue =
        parseInt(this.left_choice_text) + (range * newValue) / 100;
      var computed = newValue;
      var otherValue = 7 - computed * 0.15;
      this.customStyle = {
        left: `calc(${computed}% + (${otherValue}px))`,
      };
    },
  },
};
</script>

<style scoped>
.range-wrap {
  position: relative;
  margin: 0 auto 0.3rem;
}

.range {
  width: 100%;
}

.bubble {
  font-size: 2em;
}
.value-slider {
  font-size: 1.3em;
}

.bubble {
  display: inline;
  border: 1px solid #7a0019;
  border-radius: 4px;
  color: black;
  padding: 7px;
  position: absolute;
  left: 50%;
  transform: translateX(-50%);
  top: 23px;
  /* transform: translateX(-60%); */
  background-color: white;
  z-index: 100;
}

.bubble::after {
  content: "";
  position: absolute;
  width: 2px;
  height: 3px;
  background: #7a0019;
  top: -3px;
  left: 50%;
}
</style>
