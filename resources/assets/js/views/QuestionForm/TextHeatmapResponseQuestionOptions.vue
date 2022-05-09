<template>
  <div class="row">
    <div class="col">
      <hr />
      <p>Heatmap Text:</p>
      <VEditor
        v-model="heatmap_text"
        data-cy="heatmap-text-editor"
        placeholder="Heatmap Text"
        :toolbar="toolbar"
        :options="editorOptions"
        @input="emitChanges"
      />
    </div>
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
      heatmap_text: this.question_responses.heatmap_text,
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
    emitChanges: function () {
      this.$emit("update:question_responses", {
        heatmap_text: this.heatmap_text,
      });
    },
  },
};
</script>
