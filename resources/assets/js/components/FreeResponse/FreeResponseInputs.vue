<template>
  <div>
    <div class="col-sm-12">
      <div class="form-group">
        <textarea
          v-model="response_text"
          data-cy="free-response-textarea"
          class="form-control"
          placeholder="Type your response"
          :rows="3"
          :disabled="disabled"
          :max-rows="6"
        >
        </textarea>
      </div>
    </div>
    <div class="col-sm-12">
      <button
        v-if="(!disabled && !response.id) || create_new_response"
        class="btn btn-primary"
        variant="primary"
        @click="record_response"
      >
        Save
      </button>
      <button
        v-if="!disabled && response.id && !create_new_response"
        class="btn btn-primary"
        variant="primary"
        @click="record_response"
      >
        Update
      </button>
      <button
        v-if="
          !disabled &&
          response.id &&
          !create_new_response &&
          question.allow_multiple
        "
        class="btn btn-primary"
        variant="primary"
        @click="new_response"
      >
        Clear and Start a New Response
      </button>
    </div>
  </div>
</template>

<script>
export default {
  props: ["question", "response", "disabled"],
  data() {
    return {
      response_text: "",
      create_new_response: false,
    };
  },
  watch: {
    response: function (value) {
      if (this.response && this.response.response_info) {
        this.response_text = this.response.response_info.text;
      }
    },
  },
  mounted() {
    if (
      this.response &&
      this.response.hasOwnProperty("response_info") &&
      this.response.response_info.hasOwnProperty("text")
    ) {
      this.response_text = this.response.response_info.text;
    }
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
