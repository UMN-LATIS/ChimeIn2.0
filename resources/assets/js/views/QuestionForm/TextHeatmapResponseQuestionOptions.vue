<template>
  <div class="text-heatmap-response-question-options">
    <label>Heatmap Text</label>
    <VEditor
      :modelValue="question_responses.heatmap_text"
      data-cy="heatmap-text-editor"
      placeholder="Heatmap Text"
      :toolbar="toolbar"
      :options="editorOptions"
      @update:modelValue="emitChanges"
    />
  </div>
</template>

<script>
import VEditor from "../../components/VEditor.vue";

export default {
  name: "TextHeatmapResponse",
  components: {
    VEditor,
  },
  props: ["question_responses"],
  emits: ["update:question_responses"],
  data: function () {
    return {
      toolbar: [
        ["bold", "italic", "underline", "align"],
        [
          {
            list: "ordered",
          },
          {
            list: "bullet",
          },
        ],
        [
          {
            script: "sub",
          },
          {
            script: "super",
          },
          "formula",
        ],
        ["link"],
      ],
      editorOptions: {
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
      },
    };
  },
  methods: {
    emitChanges(updatedContents) {
      this.$emit("update:question_responses", {
        heatmap_text: updatedContents,
      });
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
