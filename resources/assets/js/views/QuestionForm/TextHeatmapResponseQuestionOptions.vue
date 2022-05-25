<template>
  <div class="text-heatmap-response-question-options">
    <label>Heatmap Text</label>
    <VEditor
      :modelValue="question_responses.heatmap_text"
      data-cy="heatmap-text-editor"
      placeholder="Heatmap Text"
      :toolbar="toolbar"
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
import VEditor from "../../components/VEditor.vue";
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
    formula: true,
    keyboard: {
      bindings: {
        "list autofill": {
          prefix: /^\s{0,}(1){1,1}(\.|-|\*|\[ ?\]|\[x\])$/,
        },
      },
    },
  },
};
</script>
<style scoped>
.text-heatmap-response-question-options {
  margin: 1rem 0;
  padding: 1rem 0;
}
</style>
