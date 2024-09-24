<template>
  <div>
    <div class="form-group mt-2">
      <textarea
        v-model="response_text"
        :aria-labelledby="`question-${question.id}-heading`"
        data-cy="free-response-textarea"
        class="form-control"
        placeholder="Type your response"
        :rows="3"
        :disabled="disabled"
        :max-rows="6"
      >
      </textarea>
      <small
        v-if="isLargerThanMaxChars"
        data-cy="free-response-char-count"
        class="d-block text-right text-danger mt-1"
      >
        {{ response_text.length.toLocaleString() }} /
        {{ MAX_CHARS.toLocaleString() }}
      </small>
    </div>
    <div class="mb-3">
      <button
        v-if="(!disabled && !response?.id) || create_new_response"
        class="btn btn-outline-primary"
        variant="primary"
        :disabled="isEmpty || isLargerThanMaxChars"
        @click="record_response"
      >
        Save
      </button>
      <button
        v-if="!disabled && response?.id && !create_new_response"
        class="btn btn-outline-primary"
        variant="primary"
        :disabled="isEmpty || isLargerThanMaxChars"
        @click="record_response"
      >
        Update
      </button>
      <button
        v-if="
          !disabled &&
          response?.id &&
          !create_new_response &&
          question.allow_multiple
        "
        class="btn btn-outline-primary"
        variant="primary"
        @click="new_response"
      >
        Clear and Start a New Response
      </button>

      <small v-if="isLargerThanMaxChars" role="alert" class="text-danger ml-1">
        Your response is too long. Please shorten it.
      </small>
    </div>
  </div>
</template>

<script lang="ts">
import { PropType } from "vue";
import * as T from "@/types";

const MAX_CHARS = 10000;

export default {
  props: {
    question: {
      type: Object as PropType<T.Question<T.FreeResponseQuestionInfo>>,
      required: true,
    },
    response: {
      type: Object as PropType<T.Response<T.FreeResponseResponseInfo> | null>,
      required: false,
      default: null,
    },
    disabled: {
      type: Boolean,
      required: false,
    },
  },
  emits: ["recordresponse"],
  data() {
    return {
      response_text: "",
      create_new_response: false,
      MAX_CHARS,
    };
  },
  computed: {
    isLargerThanMaxChars() {
      return this.response_text.length > MAX_CHARS;
    },
    isEmpty() {
      return this.response_text.length === 0;
    },
  },
  watch: {
    response() {
      if (this.response && this.response.response_info) {
        this.response_text = this.response.response_info.text;
      }
    },
  },
  mounted() {
    this.response_text = this.response?.response_info.text ?? "";
  },
  methods: {
    record_response: function () {
      const response = {
        question_type: "free_response",
        text: this.response_text,
      };

      this.$emit("recordresponse", response, this.create_new_response);
      this.create_new_response = false;
    },
    new_response: function () {
      this.create_new_response = true;
      this.response_text = "";
    },
  },
};
</script>
<style scoped>
button[disabled],
button[disabled]:hover {
  cursor: not-allowed;
  background-color: #e9ecef;
  border-color: #e9ecef;
  color: #6c757d;
}
</style>
