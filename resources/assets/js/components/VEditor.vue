<template>
  <div ref="editorContainerRef" class="v-editor"></div>
</template>
<script setup>
import { onMounted, onUnmounted, ref } from "vue";
import useQuill from "../hooks/useQuill.js";

const props = defineProps({
  modelValue: {
    type: String,
    default: "",
  },
  imageHandler: {
    type: Function,
    default: (file) => Promise.resolve(console.log("no image handler", file)),
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

const { quill, onAttachImage, onTextChange } = useQuill({
  editorContainerRef,
  options: props.options,
  modules: props.modules,
  // onAttachImage: props.imageHandler,
  // onTextChange: (contents) => emit("update:modelValue", contents),
});

const removeImageHandler = onAttachImage(props.imageHandler);
const removeTextChangeHandler = onTextChange((contents) => {
  console.log("textChange", contents);
  emit("update:modelValue", contents);
});

onMounted(() => {
  emit("ready", quill);
});

onUnmounted(() => {
  removeImageHandler();
  removeTextChangeHandler();
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
