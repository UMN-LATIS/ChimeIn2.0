<template>
  <div>
    <label for="numeric-x-input" class="form-label">
      {{ questionOptions.x_axis_label }}
    </label>
    <input
      id="numeric-x-input"
      :value="responseInfo.x"
      type="number"
      :disabled="disabled"
      class="form-control"
      @input="handleInput"
    />
  </div>
</template>
<script setup lang="ts">
import {
  NumericResponseResponseInfo,
  NumericResponseQuestionInfo,
} from "@/types";

const props = defineProps<{
  questionOptions: NumericResponseQuestionInfo["question_responses"];
  disabled: boolean;
  responseInfo: NumericResponseResponseInfo;
}>();

const emit = defineEmits<{
  (eventName: "update:responseInfo", value: NumericResponseResponseInfo): void;
}>();

function handleInput(event: Event) {
  const target = event.target as HTMLInputElement;

  const x = parseFloat(target.value);

  // ignore updates that are not numbers
  if (isNaN(x)) {
    return;
  }

  emit("update:responseInfo", {
    ...props.responseInfo,
    x,
  });
}
</script>
<style scoped></style>
