<template>
  <article>
    <div class="mb-3">
      <BarChartResponseInputs
        v-if="questionOptions.chart_type === 'bar'"
        :questionOptions="questionOptions"
        :disabled="disabled"
        :responseInfo="localResponseInfo"
        @update:responseInfo="localResponseInfo.x = $event.x"
      />
    </div>

    <div
      v-if="questionOptions.chart_type === 'scatter'"
      class="mb-3 d-flex gap-3"
    >
      <div>
        <label for="numeric-x-input" class="form-label">
          {{ questionOptions.x_axis_label }}
        </label>
        <input
          id="numeric-x-input"
          v-model="localResponseInfo.x"
          type="number"
          :disabled="props.disabled"
          class="form-control"
        />
      </div>

      <div>
        <label for="numeric-y-input" class="form-label">
          {{ questionOptions.y_axis_label }}
        </label>
        <input
          id="numeric-y-input"
          v-model="localResponseInfo.y"
          type="number"
          :disabled="disabled"
          class="form-control"
        />
      </div>
    </div>
    <div class="mb-3 d-flex gap-2">
      <button
        v-if="!disabled && (!hasExistingResponse || hasStartedNewResponse)"
        class="btn btn-outline-primary"
        @click="handleSave"
      >
        Save
      </button>

      <button
        v-else-if="!disabled"
        class="btn btn-outline-primary"
        variant="primary"
        @click="handleUpdate"
      >
        Update
      </button>

      <button
        v-if="
          !disabled &&
          question.allow_multiple &&
          hasExistingResponse &&
          !hasStartedNewResponse
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
import BarChartResponseInputs from "./BarChartResponseInputs.vue";

const props = defineProps<{
  question: Question<NumericResponseQuestionInfo>;
  response: Response<NumericResponseResponseInfo> | {};
  // whether inputs should be disabled, i.e. in the "Answered Questions" view
  disabled: boolean;
}>();

const emit = defineEmits<{
  (
    event: "recordresponse",
    responseInfo: NumericResponseResponseInfo,
    // false = update, true = save as new
    createAsNewResponse?: boolean
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

const hasExistingResponse = computed(() => !!maybeResponse.value?.id);

const localResponseInfo = reactive<NumericResponseResponseInfo>({
  question_type: "numeric_response",
  x: 0,
  y: 0,
});

watch(
  localResponseInfo,
  () => {
    console.log({ localResponseInfo });
  },
  { deep: true, immediate: true }
);

watch(
  maybeResponse,
  () => {
    localResponseInfo.x = maybeResponse.value?.response_info?.x ?? 0;
    localResponseInfo.y = maybeResponse.value?.response_info?.y ?? 0;
  },
  { immediate: true }
);

const questionOptions = computed(() => {
  return props.question.question_info.question_responses;
});

function handleSave() {
  emit("recordresponse", localResponseInfo, true);
  hasStartedNewResponse.value = false;
}

function handleUpdate() {
  emit("recordresponse", localResponseInfo, false);
}

const hasStartedNewResponse = ref(false);
function handleClearAndStartNewResponse() {
  localResponseInfo.x = 0;
  localResponseInfo.y = 0;
  hasStartedNewResponse.value = true;
}
</script>
<style scoped>
.btn.btn-tertiary {
  display: block;
  text-transform: uppercase;
  font-size: 0.825rem;
  color: #333;
  background: #f3f3f3;
}
</style>
