<template>
  <QuillEditor
    :content="modelValue"
    :modules="modules"
    :toolbar="toolbar"
    contentType="html"
    theme="snow"
    @update:content="(val) => $emit('update:modelValue', val)"
    @ready="(quill) => $emit('ready', quill)"
  />
</template>
<script setup>
import { QuillEditor } from "@vueup/vue-quill";
import BlotFormatter from "quill-blot-formatter";
import QuillImageUploader from "quill-image-uploader";
import "@vueup/vue-quill/dist/vue-quill.snow.css";

const props = defineProps({
  modelValue: {
    type: String,
    default: "",
  },
  imageHandler: {
    type: Function,
    default: (file) => Promise.resolve(console.log("no image handler", file)),
  },
});

defineEmits(["update:modelValue", "ready"]);

const modules = [
  {
    name: "blotFormatter",
    module: BlotFormatter,
    options: {
      /* options */
    },
  },
  {
    name: "imageUploader",
    module: QuillImageUploader,
    options: {
      upload(file) {
        return props.imageHandler(file);
      },
    },
  },
];

const toolbar = [
  ["bold", "italic", "underline", "align"],
  [{ list: "ordered" }, { list: "bullet" }],
  [{ script: "sub" }, { script: "super" }, "formula"],
  ["link", "image"],
];
</script>
<style>
.ql-editor {
  min-height: 10rem;
}

.ql-container {
  font-size: 1rem;
}
</style>
