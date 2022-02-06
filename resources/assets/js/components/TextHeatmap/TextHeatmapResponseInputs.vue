<template>
  <div>
    <div
      data-cy="text-heatmap-highlighted-text-container"
      class="form-group text-heatmap-highlighted-text-container"
      v-html="highlightedText"
    />
    <div class="d-flex">
      <button
        v-if="(!disabled && !response.id) || create_new_response"
        class="btn btn-outline-primary"
        variant="primary"
        :disabled="disableSubmission"
        @click="record_response"
      >
        Submit Selection
      </button>
      <button
        v-if="
          !disabled &&
          response.id &&
          !create_new_response &&
          response.response_info.startOffset < 0
        "
        class="btn btn-outline-primary"
        variant="primary"
        :disabled="disableSubmission"
        @click="record_response"
      >
        Update
      </button>
      <button
        v-if="
          !disabled &&
          response.id &&
          !create_new_response &&
          response.response_info.startOffset >= 0
        "
        class="btn btn-outline-primary"
        variant="primary"
        @click="resetSelection"
      >
        Reset Selection
      </button>
      <button
        v-if="
          !disabled &&
          response.id &&
          !create_new_response &&
          question.allow_multiple
        "
        class="btn btn-outline-primary"
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
      create_new_response: false,
      disableSubmission: true,
      stored_response: null,
    };
  },
  computed: {
    highlightedText: function () {
      var insideAnElement = false;
      var outputString = "";
      if (this.startOffset < 0 || this.create_new_response) {
        return this.question.question_info.question_responses.heatmap_text;
      }
      for (
        var i = 0;
        i < this.question.question_info.question_responses.heatmap_text.length;
        i++
      ) {
        let currentCharacter =
          this.question.question_info.question_responses.heatmap_text[i];
        // we need to not inject new html inside tags, but we need to track our position
        if (currentCharacter == "<") {
          insideAnElement = true;
        }
        if (insideAnElement) {
          outputString = outputString + currentCharacter;
        }

        if (currentCharacter == ">") {
          insideAnElement = false;
          continue;
        }

        if (insideAnElement) {
          continue;
        }

        // super easy way to make a heatmap gradient based on https://stackoverflow.com/questions/12875486/what-is-the-algorithm-to-create-colors-for-a-heatmap

        var backgroundColor = null;
        if (i < this.startOffset) {
          backgroundColor = "";
        } else if (i >= this.startOffset && i < this.endOffset) {
          backgroundColor = "background-color: yellow";
        }

        outputString =
          outputString +
          "<span style='" +
          backgroundColor +
          "'>" +
          currentCharacter +
          "</span>";
      }
      return outputString;
    },
    startOffset: function () {
      if (this.response && this.response.response_info) {
        return this.response.response_info.startOffset;
      }
      return -1;
    },
    endOffset: function () {
      if (this.response && this.response.response_info) {
        return this.response.response_info.endOffset;
      }
      return -1;
    },
  },
  created: function () {
    window.addEventListener("mouseup", this.testForHighlight);
    window.addEventListener("touchend", this.testForHighlight);
  },
  destroyed: function () {
    window.removeEventListener("mouseup", this.testForHighlight);
    window.removeEventListener("touchend", this.testForHighlight);
  },
  methods: {
    store_response: function () {
      const mySelection = window.getSelection();
      // console.log(mySelection);
      var startOffset = 0;
      var endOffset = 0;
      // if the selection is not empty (aka does not have same start and end point), grab the offsets
      if (!mySelection.isCollapsed) {
        var range = window.getSelection().getRangeAt(0);

        // document fragment with html for selection
        var fragment = range.cloneContents();
        // make new element, insert document fragment, then get innerHTML!
        var div = document.createElement("div");
        div.appendChild(fragment.cloneNode(true));

        // your document fragment to a string (w/ html)! (yay!)
        var html = div.innerHTML;
        var div2 = document.createElement("div");
        div2.innerHTML =
          this.question.question_info.question_responses.heatmap_text;
        startOffset = div2.innerHTML.indexOf(html);
        endOffset = startOffset + html.length;
        const response = {
          question_type: "text_heatmap_response",
          startOffset: startOffset,
          endOffset: endOffset,
        };
        this.stored_response = response;
      } else {
        this.resetSelection();
      }
    },
    record_response: function () {
      if (this.store_response !== null) {
        this.$emit(
          "recordresponse",
          this.store_response,
          this.create_new_response
        );
        this.create_new_response = false;
      }
    },
    resetSelection() {
      window.getSelection().removeAllRanges();
      const response = {
        question_type: "text_heatmap_response",
        startOffset: -1,
        endOffset: -1,
      };
      this.stored_response = response;
      this.$emit(
        "recordresponse",
        this.stored_response,
        this.create_new_response
      );
      return;
    },
    new_response: function () {
      window.getSelection().removeAllRanges();
      this.create_new_response = true;
    },
    testForHighlight: function () {
      const mySelection = window.getSelection();
      if (mySelection.isCollapsed) {
        this.disableSubmission = true;
      } else {
        this.disableSubmission = false;
      }
    },
  },
};
</script>
<style scoped>
.text-heatmap-highlighted-text-container {
  padding-left: 1rem;
  border-left: 0.25rem solid #ddd;
}
</style>
