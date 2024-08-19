<template>
  <div
    class="response-choice-item"
    :class="{ 'response-choice-item--is-correct': correct }"
  >
    <div
      class="response-choice-item__correct-toggle"
      title="Mark Response Correct"
    >
      <input
        :checked="correct"
        type="checkbox"
        @change="
          handleUpdate({ correct: ($event.target as HTMLInputElement).checked })
        "
      />
      <label class="visually-hidden">Correct?</label>
    </div>
    <div class="response-choice-item__contents">
      <label class="visually-hidden">Response Text</label>
      <QuillEditor
        :options="options"
        class="response-choice-item__text"
        :modelValue="text"
        :imageUploadUrl="`/api/chime/${chime_id}/image`"
        @update:modelValue="
          (updatedText) => handleUpdate({ text: updatedText })
        "
      />

      <button
        class="response-choice-item__remove"
        data-cy="remove-response-button"
        @click="$emit('remove')"
      >
        <i class="material-icons inline-icon">clear</i>
      </button>
    </div>
  </div>
</template>
<script setup lang="ts">
import QuillEditor from "@/components/QuillEditor.vue";

const props = withDefaults(
  defineProps<{
    text: string;
    correct: boolean;
    // eslint-disable-next-line vue/prop-name-casing
    chime_id: number;
  }>(),
  {
    text: "",
    correct: false,
  }
);

function handleUpdate(update) {
  return emit("update", {
    ...props,
    ...update,
  });
}

const emit = defineEmits<{
  (
    eventName: "update",
    value: {
      text: string;
      correct: boolean;
    }
  ): void;
  (eventName: "enter"): void;
  (eventName: "remove"): void;
}>();

const options = {
  bounds: ".modal-body",
  modules: {
    toolbar: ["formula"],
    keyboard: {
      bindings: {
        Enter: {
          key: "Enter",
          handler: () => {
            emit("enter");
          },
        },
      },
    },
  },
};
</script>
<style scoped>
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
</style>

<style>
/**
* override default quill editor styles
* extra classes are to increase specificity
**/
.response-choice-item .response-choice-item__text {
  display: flex;
  align-items: baseline;
  flex: 1;
  order: -1;
}
.response-choice-item .response-choice-item__text .ql-container {
  flex-grow: 1;
}

.response-choice-item .ql-editor {
  flex: 1;
  min-height: auto;
}

.response-choice-item .ql-toolbar.ql-snow,
.response-choice-item .ql-container.ql-snow,
.response-choice-item .response-choice-item__text .ql-toolbar,
.response-choice-item .response-choice-item__text .ql-container {
  border: 0;
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
.response-choice-item .ql-toolbar.ql-snow .ql-formats {
  margin: 0;
}
</style>
