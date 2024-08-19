<template>
  <div ref="editorContainerRef" class="v-editor"></div>
</template>
<script setup lang="ts">
import { onMounted, ref } from "vue";
import Quill, { QuillOptions, Range } from "quill";
import { Delta } from "quill/core";
import Emitter from "quill/core/emitter";
import { mergeDeepRight } from "ramda";
import axios from "@/common/axiosClient";

const props = withDefaults(
  defineProps<{
    modelValue: string;
    imageUploadUrl?: string | null;
    options?: Partial<QuillOptions>;
  }>(),
  {
    imageUploadUrl: null,
    options: () => ({}),
  }
);

const emit = defineEmits<{
  (eventName: "update:modelValue", value: string): void;
}>();

const editorContainerRef = ref<HTMLElement | null>(null);

// using a ref causes errors
// @see: https://github.com/slab/quill/issues/4293
let quill: Quill | null = null;

const defaultOptions = {
  modules: {
    toolbar: [
      ["bold", "italic", "underline", "align"],
      [{ list: "ordered" }, { list: "bullet" }],
      [{ script: "sub" }, { script: "super" }, "formula"],
      ["link", "image"],
    ],
    uploader: {
      mimetypes: ["image/png", "image/jpeg", "image/jpg"],
      async handler(range: Range, files: File[]) {
        if (!props.imageUploadUrl) {
          throw new Error("Image upload URL is not set");
        }

        if (!quill) {
          throw new Error("Quill editor is not set");
        }

        const form = new FormData();
        form.append("image", files[0]);

        const res = await axios.post(props.imageUploadUrl, form);

        const imageUrl = `/storage/${res.data.image}`;

        // Validate the range object
        if (
          !range ||
          typeof range.index !== "number" ||
          typeof range.length !== "number"
        ) {
          throw new Error("Invalid range object");
        }

        const imageDelta = new Delta()
          .retain(range.index) // Retain the text before the range
          .delete(range.length) // Delete the selected text
          .insert({ image: imageUrl }); // Insert the image

        // apply the new delta to the editor
        quill.updateContents(imageDelta, Emitter.sources.USER);

        // move the cursor to the end of the image
        quill.setSelection(
          range.index + imageDelta.length(),
          Emitter.sources.SILENT
        );
      },
    },
    betterImage: {},
  },
  theme: "snow",
};

onMounted(() => {
  if (!editorContainerRef.value) {
    throw new Error("Editor container ref is not set");
  }

  const mergedOptions = mergeDeepRight(defaultOptions, props.options);

  quill = new Quill(editorContainerRef.value, mergedOptions);
});
</script>
<style>
.ql-editor {
  min-height: 10rem;
}

.ql-container {
  font-size: 1rem;
}
</style>
