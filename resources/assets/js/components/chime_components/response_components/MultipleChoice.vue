<template>
  <div class="multiple-choice-question-options">
    <section class="form-section">
      <header v-if="question_responses.length">
        <h3 class="form-section__heading">
          Response Choices
        </h3>
        <p class="text-muted">Check to mark response correct.</p>
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
              <input type="checkbox" v-model="response.correct" />
              <label class="visually-hidden">Correct?</label>
            </div>
            <div class="response-choice-item__contents">
              <label :for="`response-text-${i}`" class="visually-hidden"
                >Response Text</label
              >
              <input
                class="response-choice-item__text"
                :id="`response-text-${i}`"
                :name="`response-text-${i}`"
                v-model="response.text"
                ref="responseInput"
                @keyup.enter="addChoice"
              />

              <button @click="remove(i)" class="response-choice-item__remove">
                <i class="material-icons inline-icon">clear</i>
              </button>
            </div>
          </li>
        </draggable>
      </ol>
      <!-- <EditResponseChoiceForm @submit="addChoice" /> -->
      <button class="btn btn-outline-primary" @click="addChoice">
        Add Choice
      </button>
    </section>
  </div>
</template>

<style scoped>
label {
  margin: 0;
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
  background: #eee;
  border-color: #333;
  outline: 0.25rem auto var(--blue);
}

.response-choice-item__correct-toggle {
  padding: 0.5rem;
}

.response-choice-item__text {
  border: 0;
  flex-grow: 1;
  padding: 0.5rem;
  background: transparent;
}

.response-choice-item__text:focus {
  outline: none;
}

.response-choice-item__remove {
  display: flex;
  background: 0;
  border: 0;
}

.form-section {
  margin-top: 1rem;
}
.form-section__heading {
  font-size: 0.9rem;
  text-transform: uppercase;
  font-weight: bold;
}
.is-draggable {
  cursor: move;
}

.true-false-question-toggle {
  margin-top: 1rem;
}
</style>

<script>
import draggable from "vuedraggable";

export default {
  props: {
    question_responses: Array,
  },
  components: {
    draggable,
  },
  methods: {
    remove(responseIndex) {
      const updatedResponses = this.question_responses.filter(
        (_, i) => i !== responseIndex
      );
      this.$emit("update:question_responses", updatedResponses);
    },

    addChoice() {
      // filter out any blanks and add a new responses
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
      this.$nextTick(() => {
        const lastResponseIndex = this.question_responses.length - 1;
        this.$refs.responseInput[lastResponseIndex].focus();
      });
    },
    createTrueFalseQuestion: function() {
      const updatedResponses = [
        { text: "True", correct: false },
        { text: "False", correct: false },
      ];

      this.$emit("update:question_responses", updatedResponses);
    },
  },
  mounted() {
    // if question responses is empty, initialize with blank array
    // perhaps this should be the parents job?
    if (!this.question_responses) {
      this.$emit("update:question_responses", []);
    }
  },
};
</script>
