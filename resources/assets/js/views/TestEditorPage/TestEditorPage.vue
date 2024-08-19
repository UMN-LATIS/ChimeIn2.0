<template>
  <div class="p-5">
    <h1>Test Editor Page</h1>
    <QuillEditor
      v-model="content"
      :imageUploadUrl="`/api/chime/${chimeId}/image`"
    />
  </div>
</template>
<script setup lang="ts">
import QuillEditor from "@/components/QuillEditor.vue";

import { ref } from "vue";

const content = ref<string>("This is initial content");

// just a dummy chime id for testing
const chimeId = 1;
</script>
<style scoped></style>
