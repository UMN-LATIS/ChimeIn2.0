<template>
  <section class="multiple-choice-question-options form-section">
    <header
      v-if="Array.isArray(question_responses) && question_responses.length"
    >
      <h3 class="form-section__heading">Choices</h3>
      <p class="text-muted">Check to mark choice correct.</p>
    </header>

    <ol
      v-if="Array.isArray(question_responses) && question_responses.length"
      class="response-choice-list"
      data-cy="response-choice-list"
    >
      <!-- question responses don't have an unique id, 
       we so force component update after reordering with :key prop-->
      <Draggable
        :key="draggableKey"
        :modelValue="question_responses"
        itemKey="id"
        ghostClass="ghost"
        :disabled="false"
        @change="handleResponseOrderChange"
        @start="dragging = true"
        @end="dragging = false"
      >
        <template #item="{ element, index }">
          <li
            ref="responseChoiceItemRefs"
            class="is-draggable response-choice-item"
            :class="{ 'response-choice-item--is-correct': element.correct }"
          >
            <ResponseChoiceItem
              :id="`response-choice-item-${index}`"
              :text="element.text"
              :correct="element.correct"
              @update="(updatedChoice) => handleUpdate(index, updatedChoice)"
              @remove="handleRemove(index)"
              @enter="addChoice"
            />
          </li>
        </template>
      </Draggable>
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
import { move } from "ramda";
import { onMounted, ref, nextTick } from "vue";
import ResponseChoiceItem from "./ResponseChoiceItem.vue";
import Draggable from "vuedraggable";

const props = defineProps({
  // eslint-disable-next-line vue/prop-name-casing
  question_responses: {
    type: Array,
    default: () => [],
  },
});

const dragging = ref(false);
const emit = defineEmits(["update:question_responses"]);
const responseChoiceItemRefs = ref([]);

function handleUpdate(index, updatedChoice) {
  const updatedResponses = [
    ...props.question_responses.slice(0, index),
    updatedChoice,
    ...props.question_responses.slice(index + 1),
  ];
  emit("update:question_responses", updatedResponses);
}

function handleRemove(responseIndex) {
  const updatedResponses = props.question_responses.filter(
    (_, index) => index !== responseIndex
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

    lastItem.focus();
  });
}

//increment when we want to force rerendering
const draggableKey = ref(0);
function handleResponseOrderChange(event) {
  if (!event.moved) return;
  const { oldIndex, newIndex } = event.moved;
  const updatedResponses = move(oldIndex, newIndex, props.question_responses);
  emit("update:question_responses", updatedResponses);
  nextTick(() => (draggableKey.value += 1));
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
