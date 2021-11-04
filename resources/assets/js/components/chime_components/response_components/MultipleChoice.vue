<template>
  <div class="multiple-choice-question-options">
    <label class="true-false-question-toggle">
      <input
        type="checkbox"
        v-model="isTrueFalseQuestion"
        @change="toggleTrueFalse"
      />
      True/False Question
    </label>

    <section class="form-section">
      <header v-if="responses.length">
        <h3 class="form-section__heading">
          Response Choices
        </h3>
        <p class="text-muted">Check to mark response correct.</p>
      </header>

      <ol class="response-choice-list" data-cy="response-choice-list">
        <draggable :list="responses">
          <li
            v-for="(response, i) in responses"
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
  data: function() {
    return {
      responses: this.question_responses || [],
      isTrueFalseQuestion: false,
    };
  },
  methods: {
    remove(responseIndex) {
      this.responses = this.responses.filter((_, i) => i !== responseIndex);
    },
    addChoice() {
      // remove any choices that are blank
      this.responses = this.responses.filter((r) => r.text !== "");

      // add a new response
      this.responses.push({
        text: "",
        correct: false,
      });

      // focus new choice on next tick
      this.$nextTick(() => {
        const lastResponseIndex = this.responses.length - 1;
        console.log({ refs: this.$refs });
        const ref = this.$refs.responseInput[lastResponseIndex];
        console.log({ ref });
        ref.focus();
      });
    },
    toggleTrueFalse: function() {
      if (!this.isTrueFalseQuestion) return;

      this.responses = [
        { text: "True", correct: false },
        { text: "False", correct: false },
      ];
    },
  },
};
</script>
