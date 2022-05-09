<template>
  <section class="multiple-choice-question-options form-section">
    <header
      v-if="Array.isArray(question_responses) && question_responses.length"
    >
      <h3 class="form-section__heading">Choices</h3>
      <p class="text-muted">Check to mark choice correct.</p>
    </header>

    <ol class="response-choice-list" data-cy="response-choice-list">
      <!-- <draggable :list="question_responses"> -->
      <li
        v-for="(response, i) in question_responses"
        :key="i"
        ref="responseChoiceItemRefs"
      >
        <ResponseChoiceItem
          :id="`response-choice-item-${i}`"
          :text="response.text"
          :correct="response.correct"
          @update="(updatedChoice) => handleUpdate(i, updatedChoice)"
          @remove="handleRemove(i)"
          @enter="addChoice"
        />
      </li>
      <!-- </draggable> -->
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

<script setup>
import { onMounted, ref, nextTick } from "vue";
import ResponseChoiceItem from "./ResponseChoiceItem.vue";
// import draggable from "vuedraggable";

const props = defineProps({
  question_responses: {
    type: Array,
    default: () => [],
  },
});

const emit = defineEmits(["update:question_responses"]);
const responseChoiceItemRefs = ref([]);

function handleUpdate(index, updatedChoice) {
  emit("update:question_responses", [
    ...props.question_responses.slice(0, index),
    updatedChoice,
    ...props.question_responses.slice(index + 1),
  ]);
}

function handleRemove(responseIndex) {
  const updatedResponses = props.question_responses.filter(
    (_, i) => i !== responseIndex
  );
  emit("update:question_responses", updatedResponses);
}

function addChoice() {
  const updatedResponses = props.question_responses
    .filter((r) => r.text !== "")
    .concat([
      {
        text: "",
        correct: false,
      },
    ]);

  emit("update:question_responses", updatedResponses);

  nextTick(() => {
    const newChoiceIndex = updatedResponses.length - 1;
    const lastItem = document
      .getElementById(`response-choice-item-${newChoiceIndex}`)
      .querySelector(".ql-editor");

    console.log(lastItem);
    lastItem.focus();
  });
}

onMounted(() => {
  if (!props.question_responses) {
    emit("update:question_responses", []);
  }
});
</script>
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
  flex: 1;
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
