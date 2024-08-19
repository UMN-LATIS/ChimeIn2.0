<template>
  <div class="text-heatmap-response-question-options">
    <label>Heatmap Text</label>
    <QuillEditor
      :modelValue="question_responses.heatmap_text ?? ''"
      data-cy="heatmap-text-editor"
      :options="editorOptions"
      @update:modelValue="
        (updatedText) =>
          $emit('update:question_responses', {
            heatmap_text: updatedText,
          })
      "
    />
  </div>
</template>

<script setup>
import QuillEditor from "@/components/QuillEditor.vue";
defineProps({
  // eslint-disable-next-line vue/prop-name-casing
  question_responses: {
    type: Object,
    required: true,
  },
});

defineEmits(["update:question_responses"]);

const toolbar = [
  ["bold", "italic", "underline", "align"],
  [{ list: "ordered" }, { list: "bullet" }],
  [{ script: "sub" }, { script: "super" }, "formula"],
  ["link"],
];

const editorOptions = {
  bounds: ".modal-body",
  modules: {
    toolbar,
  },
};
</script>
<style scoped>
.text-heatmap-response-question-options {
  margin: 1rem 0;
  padding: 1rem 0;
}
</style>
