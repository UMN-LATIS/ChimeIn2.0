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
    default: () => Promise.resolve(),
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

const removeImageHandler = onAttachImage(props.imageHandler);
const removeTextChangeHandler = onTextChange((contents) => {
  emit("update:modelValue", contents);
});

onMounted(() => {
  setEditorHTML(props.modelValue);
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
