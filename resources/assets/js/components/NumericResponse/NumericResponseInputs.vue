<template>
  <article>
    <div class="mb-3">
      <label for="numeric-x-input" class="form-label">
        {{ questionOptions.x_axis_label }}
      </label>
      <input
        id="numeric-x-input"
        v-model="localResponse.x"
        type="number"
        :disabled="props.disabled"
        class="form-control"
      />
    </div>

    <div v-if="questionOptions.chart_type === 'scatter'" class="mb-3">
      <label for="numeric-y-input" class="form-label">
        {{ questionOptions.y_axis_label }}
      </label>
      <input
        id="numeric-y-input"
        v-model="localResponse.y"
        type="number"
        :disabled="disabled"
        class="form-control"
      />
    </div>
    <div class="mb-3">
      <button
        v-if="!disabled"
        class="btn btn-outline-primary"
        variant="primary"
        @click="handleSaveOrUpdate"
      >
        {{ maybeResponse?.id ? "Update" : "Save" }}
      </button>

      <button
        v-if="
          !disabled &&
          question.allow_multiple &&
          hasPreviouslySaved &&
          !isAdditionalResponse
        "
        class="btn btn-outline-primary"
        variant="primary"
        @click="handleClearAndStartNewResponse"
      >
        Clear and Start a New Response
      </button>
    </div>
  </article>
</template>
<script setup lang="ts">
import {
  NumericResponseQuestionInfo,
  NumericResponseResponseInfo,
  Question,
  Response,
} from "@/types";
import { isEmpty } from "ramda";
import { computed, reactive, ref, watch } from "vue";

const props = defineProps<{
  question: Question<NumericResponseQuestionInfo>;
  response: Response<NumericResponseResponseInfo> | {};
  disabled: boolean;
}>();

const emit = defineEmits<{
  (
    event: "recordresponse",
    response: NumericResponseResponseInfo,
    isAdditionalResponse: boolean
  ): void;
}>();

// Response may be an empty object for reasons.
// But, it's easier to deal with nulls than empty objects,
// so we'll convert it to null.
const maybeResponse = computed(
  (): Response<NumericResponseResponseInfo> | null => {
    if (isEmpty(props.response)) return null;
    return props.response as Response<NumericResponseResponseInfo>;
  }
);

const hasPreviouslySaved = computed(() => {
  return maybeResponse.value?.id !== undefined;
});

const localResponse = reactive<NumericResponseResponseInfo>({
  question_type: "numeric_response",
  x: 0,
  y: 0,
});

// if the question supports multiple responses, this will
// be true when the user wants to submit a new response
// rather than updating the existing one.
const isAdditionalResponse = ref(false);

watch(
  maybeResponse,
  () => {
    localResponse.x = maybeResponse.value?.response_info?.x ?? 0;
    localResponse.y = maybeResponse.value?.response_info?.y ?? 0;
  },
  { immediate: true }
);

const questionOptions = computed(() => {
  return props.question.question_info.question_responses;
});

function handleSaveOrUpdate() {
  emit("recordresponse", localResponse, isAdditionalResponse.value);
}

function handleClearAndStartNewResponse() {
  localResponse.x = 0;
  localResponse.y = 0;
  isAdditionalResponse.value = true;
}
</script>
<style scoped></style>
