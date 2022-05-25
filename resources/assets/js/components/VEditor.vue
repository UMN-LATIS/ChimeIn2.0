<template>
  <div ref="editorContainerRef" class="v-editor"></div>
</template>
<script setup>
import { onMounted, onUnmounted, ref } from "vue";
import useQuill from "../hooks/useQuill.js";
import { useStore } from "vuex";

const store = useStore();

const createImageUploaderForChime = (imageUploadUrl) => (file) => {
  const form = new FormData();
  form.append("image", file);

  return axios
    .post(imageUploadUrl, form)
    .then((res) => {
      return `/storage/${res.data.image}`;
    })
    .catch((err) => {
      store.commit(
        "message",
        "Could not store this image. Please contact support at latistecharch@umn.edu. The full error was: " +
          err.response
      );
      console.error(err);
      throw err;
    });
};

const props = defineProps({
  modelValue: {
    type: String,
    default: "",
  },
  // required to create an image uploader
  imageUploadUrl: {
    type: String,
    default: null,
  },
  options: {
    type: Object,
    default: () => ({}),
  },
  modules: {
    type: Array,
    default: () => [],
  },
});

const emit = defineEmits(["update:modelValue", "ready"]);
const editorContainerRef = ref(null);

const {
  onAttachImage,
  onTextChange,
  setHTML: setEditorHTML,
} = useQuill({
  editorContainerRef,
  options: props.options,
  modules: props.modules,
});

if (props.imageUploadUrl) {
  const removeImageHandler = onAttachImage(
    createImageUploaderForChime(props.imageUploadUrl)
  );
  onUnmounted(removeImageHandler);
}

const removeTextChangeHandler = onTextChange((contents) => {
  emit("update:modelValue", contents);
});

onMounted(() => {
  setEditorHTML(props.modelValue);
});

onUnmounted(removeTextChangeHandler);
</script>
<style>
.ql-editor {
  min-height: 10rem;
}

.ql-container {
  font-size: 1rem;
}
</style>
