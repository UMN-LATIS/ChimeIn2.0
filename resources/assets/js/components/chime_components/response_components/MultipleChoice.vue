<template>
  <div class="multiple-choice-question-options">
    <label>
      <input
        type="checkbox"
        v-model="isTrueFalseQuestion"
        @change="toggleTrueFalse"
      />
      True/False Question
    </label>

    <section class="form-section">
      <h3 class="form-section__heading">Response Choices</h3>

      <ol type="A" class="response-choice-list" data-cy="response-choice-list">
        <draggable :list="responses">
          <li
            v-for="(response, i) in responses"
            :key="i"
            class="response-choice-item is-draggable"
          >
            <label>
              <input type="checkbox" v-model="response.correct" />
              <span class="visually-hidden">Correct?</span>
            </label>

            <label :for="`response-text_${i}`" class="visually-hidden">
              Response Text
            </label>
            <input
              :name="`response-text_${i}`"
              :id="`response-text_${i}`"
              v-model="response.text"
            />

            <button @click="remove(i)">
              <i class="material-icons inline-icon">delete</i>
            </button>
          </li>
        </draggable>
      </ol>
      <EditResponseChoiceForm @submit="addChoice" />
    </section>
  </div>
</template>

<style scoped>
.multiple-choice-question-options {
  border: 1px solid red;
}

.response-choice-item {
  display: grid;
}

button {
  border: 1px solid #ccc;
}

.form-section {
  margin-top: 1rem;
}
.form-section__heading {
  font-size: 0.9rem;
  text-transform: uppercase;
  color: #777;
}
.is-draggable {
  cursor: move;
}
</style>

<script>
import draggable from "vuedraggable";
import EditResponseChoiceForm from "./EditResponseChoiceForm.vue";

export default {
  props: {
    question_responses: {
      type: Array,
      default: () => [],
    },
  },
  components: {
    draggable: draggable,
    EditResponseChoiceForm,
  },
  data: function() {
    return {
      responses: this.question_responses,
      isTrueFalseQuestion: false,
    };
  },
  methods: {
    remove(responseIndex) {
      this.responses = this.responses.filter((_, i) => i !== responseIndex);
    },
    addChoice({ text, isCorrect }) {
      this.responses.push({
        text,
        correct: isCorrect,
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
