<template>
  <section class="multiple-choice-question-options form-section">
    <header
      v-if="Array.isArray(question_responses) && question_responses.length"
    >
      <h3 class="form-section__heading">Choices</h3>
      <p class="text-muted">Check to mark choice correct.</p>
    </header>

    <ol class="response-choice-list" data-cy="response-choice-list">
      <draggable :list="question_responses">
        <li
          v-for="(response, i) in question_responses"
          :key="i"
          class="is-draggable response-choice-item"
          :class="{ 'response-choice-item--is-correct': response.correct }"
        >
          <div
            class="response-choice-item__correct-toggle"
            title="Mark Response Correct"
          >
            <input v-model="response.correct" type="checkbox" />
            <label class="visually-hidden">Correct?</label>
          </div>
          <div class="response-choice-item__contents">
            <label :for="`response-text-${i}`" class="visually-hidden"
              >Response Text</label
            >
            <VueEditor
              :id="`response-text-${i}`"
              ref="responseInput"
              v-model="response.text"
              class="response-choice-item__text"
              :name="`response-text-${i}`"
              :editor-toolbar="choiceEditorToolbar"
              :editor-options="choiceEditorOptions"
            />

            <button
              class="response-choice-item__remove"
              data-cy="remove-response-button"
              @click="remove(i)"
            >
              <i class="material-icons inline-icon">clear</i>
            </button>
          </div>
        </li>
      </draggable>
    </ol>
    <button
      class="btn btn-outline-primary add-choice-button"
      data-cy="add-choice-button"
      @click="addChoice"
    >
      Add Choice
    </button>
  </section>
</template>

<style scoped>
label {
  margin: 0;
}
.multiple-choice-question-options {
  margin-bottom: 1rem;
}

.add-choice-button {
  margin: 0.25rem 0;
}

.response-choice-list {
  list-style: none;
  margin: 0;
  padding: 0;
}
.response-choice-item {
  display: flex;
  margin: 0.5rem 0;
  align-items: center;
}
.response-choice-item__contents {
  display: flex;
  align-items: center;
  border: 1px solid #ddd;
  border-radius: 0.25rem;
  flex-grow: 1;
}

.response-choice-item--is-correct .response-choice-item__contents,
.response-choice-item--is-correct .response-choice-item__contents:focus-within {
  background: #28a745;
  color: white;
}
.response-choice-item--is-correct input,
.response-choice-item--is-correct button {
  color: white;
}

.response-choice-item__contents:focus-within {
  border-color: #333;
  box-shadow: 0 0 0 3px hsl(211deg 100% 50% / 42%);
}

.response-choice-item__correct-toggle {
  padding: 0.5rem;
}

.response-choice-item__text {
  border: 0;
  flex-grow: 1;
  background: transparent;
}

.response-choice-item__text:focus {
  outline: none;
}

.response-choice-item__remove {
  display: flex;
  background: 0;
  border: 0;
  padding: 0.25rem 0.75rem;
}

.form-section {
  margin-top: 1rem;
}
.form-section__heading {
  font-size: 1.1rem;
  margin-bottom: 0.25rem;
}
.text-muted {
  font-size: 0.9rem;
}
.is-draggable {
  cursor: move;
}

.true-false-question-toggle {
  margin-top: 1rem;
}
</style>
<style>
/**
* override default quill editor styles
* extra classes are to increase specificity
**/
.response-choice-item .response-choice-item__text {
  display: flex;
  align-items: baseline;
  flex-direction: row-reverse;
}
.response-choice-item .response-choice-item__text .ql-container {
  border: 0;
  flex-grow: 1;
}
.response-choice-item .response-choice-item__text .ql-toolbar {
  border: 0;
}
.response-choice-item .ql-editor {
  min-height: auto;
}

.response-choice-item .quillWrapper .ql-snow.ql-toolbar .ql-formats {
  margin: 0;
}
.response-choice-item .ql-snow .ql-toolbar button,
.response-choice-item .ql-snow.ql-toolbar button {
  padding: 0;
}
.response-choice-item--is-correct .ql-snow .ql-fill,
.response-choice-item--is-correct .ql-snow .ql-stroke.ql-fill {
  fill: #fff;
}
</style>

<script>
import { VueEditor } from "vue2-editor";
import draggable from "vuedraggable";

export default {
  components: {
    draggable,
    VueEditor,
  },
  props: {
    question_responses: Array,
  },
  computed: {
    // note: don't use arrow functions so that `this` is bound properly
    choiceEditorOptions(thisComponent) {
      return {
        bounds: ".response-choice-item__contents",
        modules: {
          formula: true,
          keyboard: {
            bindings: {
              13: {
                key: 13,
                handler() {
                  thisComponent.addChoice();
                },
              },
            },
          },
        },
      };
    },
    choiceEditorToolbar: () => ["formula"],
  },
  mounted() {
    // if question responses is empty, initialize with blank array
    // perhaps this should be the parents job?
    if (!this.question_responses) {
      this.$emit("update:question_responses", []);
    }
  },
  methods: {
    remove(responseIndex) {
      const updatedResponses = this.question_responses.filter(
        (_, i) => i !== responseIndex
      );
      this.$emit("update:question_responses", updatedResponses);
    },
    focusEditor(responseIndex) {
      // use last index by default
      if (typeof responseIndex === "undefined") {
        responseIndex = this.question_responses.length - 1;
      }
      this.$refs.responseInput[responseIndex].quill.focus();
    },
    addChoice() {
      // remove any empty responses and then add a new responses
      const updatedResponses = this.question_responses
        .filter((r) => r.text !== "")
        .concat([
          {
            text: "",
            correct: false,
          },
        ]);

      this.$emit("update:question_responses", updatedResponses);

      // focus new choice on next tick
      this.$nextTick(function () {
        this.focusEditor();
      });
    },
    createTrueFalseQuestion: function () {
      const updatedResponses = [
        { text: "True", correct: false },
        { text: "False", correct: false },
      ];

      this.$emit("update:question_responses", updatedResponses);
    },
  },
};
</script>
