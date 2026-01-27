<template>
  <div>
    <div class="form-group tw-mt-2">
      <div class="tw-flex tw-justify-end tw-mb-1">
        <fieldset
          class="tw-inline-flex tw-items-center tw-gap-1 tw-border tw-border-neutral-600 tw-rounded-md tw-p-0.5"
        >
          <legend class="tw-sr-only">Editor Mode</legend>
          <label
            v-for="{ value, label } in editorModes"
            :key="value"
            class="tw-px-2 tw-py-1 tw-rounded tw-cursor-pointer text-xs mb-0 has-[:focus]:tw-ring-2 has-[:focus]:tw-ring-blue-500"
            :class="{
              'tw-bg-neutral-900 tw-text-neutral-100':
                value === activeEditorMode,
            }"
          >
            <input
              v-model="activeEditorMode"
              type="radio"
              :name="`editorMode-${question.id}`"
              :value="value"
              class="tw-appearance-none tw-w-0 tw-h-0 tw-m-0 tw-p-0"
            />
            {{ label }}
          </label>
        </fieldset>
      </div>

      <textarea
        v-model="response_text"
        :aria-labelledby="`question-${question.id}-heading`"
        data-cy="free-response-textarea"
        class="form-control"
        placeholder="Type your response"
        :rows="activeEditorMode === 'code' ? 8 : 3"
        :disabled="disabled"
        :class="{
          'tw-font-mono text-sm tw-whitespace-pre-wrap tw-bg-neutral-900 focus:tw-bg-neutral-900 tw-text-neutral-400 focus:tw-text-neutral-300':
            activeEditorMode === 'code',
        }"
      >
      </textarea>
      <small
        v-if="isLargerThanMaxChars"
        data-cy="free-response-char-count"
        class="d-block text-right text-danger mt-1"
      >
        {{ response_text.length.toLocaleString() }} /
        {{ MAX_CHARS.toLocaleString() }}
      </small>
    </div>
    <div class="mb-3">
      <button
        v-if="(!disabled && !response?.id) || create_new_response"
        class="btn btn-outline-primary"
        variant="primary"
        :disabled="isEmpty || isLargerThanMaxChars"
        @click="record_response"
      >
        Save
      </button>
      <button
        v-if="!disabled && response?.id && !create_new_response"
        class="btn btn-outline-primary"
        variant="primary"
        :disabled="isEmpty || isLargerThanMaxChars"
        @click="record_response"
      >
        Update
      </button>
      <button
        v-if="
          !disabled &&
          response?.id &&
          !create_new_response &&
          question.allow_multiple
        "
        class="btn btn-outline-primary"
        variant="primary"
        @click="new_response"
      >
        Clear and Start a New Response
      </button>

      <small v-if="isLargerThanMaxChars" role="alert" class="text-danger ml-1">
        Your response is too long. Please shorten it.
      </small>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { ref, computed, watch } from "vue";
import * as T from "@/types";

const MAX_CHARS = 10000;

const props = withDefaults(
  defineProps<{
    question: T.Question<T.FreeResponseQuestionInfo>;
    response?: T.Response<T.FreeResponseResponseInfo> | null;
    disabled?: boolean;
  }>(),
  {
    response: null,
    disabled: false,
  }
);

const emit = defineEmits<{
  (
    e: "recordresponse",
    response: T.FreeResponseResponseInfo,
    create_new_response: boolean
  ): void;
}>();

const response_text = ref("");
const create_new_response = ref(false);

type EditorMode = "text" | "code";

interface EditorModeOption {
  label: string;
  value: EditorMode;
}

const editorModes: EditorModeOption[] = [
  { label: "Text", value: "text" },
  { label: "Code", value: "code" },
];

const activeEditorMode = ref<EditorMode>("text");

const isLargerThanMaxChars = computed(() => {
  return response_text.value.length > MAX_CHARS;
});

const isEmpty = computed(() => {
  return response_text.value.length === 0;
});

watch(
  () => props.response,
  (newResponse) => {
    if (newResponse && newResponse.response_info) {
      response_text.value = newResponse.response_info.text;
      activeEditorMode.value = newResponse.response_info.editorMode ?? "text";
    }
  },
  { immediate: true }
);

function record_response() {
  const response: T.FreeResponseResponseInfo = {
    question_type: "free_response",
    text: response_text.value,
    editorMode: activeEditorMode.value,
  };

  emit("recordresponse", response, create_new_response.value);
  create_new_response.value = false;
}

function new_response() {
  create_new_response.value = true;
  response_text.value = "";
}
</script>
<style scoped>
button[disabled],
button[disabled]:hover {
  cursor: not-allowed;
  background-color: #e9ecef;
  border-color: #e9ecef;
  color: #6c757d;
}
</style>
