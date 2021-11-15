<template>
  <div class="row">
    <div class="col">
      <hr />
      <p>Heatmap Text:</p>
      <vue-editor
        data-cy="heatmap-text-editor"
        v-model="heatmap_text"
        placeholder="Heatmap Text"
        v-bind:editorToolbar="toolbar"
        v-bind:editorOptions="editorOptions"
        @input="emitChanges"
      />
    </div>
  </div>
</template>

<script>
import { VueEditor } from "vue2-editor";

export default {
  name: "TextHeatmapResponse",
  props: ["question_responses"],
  components: {
    "vue-editor": VueEditor,
  },
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
      console.log("fda");
      this.$emit("update:question_responses", {
        heatmap_text: this.heatmap_text,
      });
    },
  },
};
</script>
