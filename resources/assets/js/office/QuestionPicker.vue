<template>
  <div class="chimein-picker">
    <h5>{{ chimeName }}</h5>

    <div v-if="loading">Loading questions&hellip;</div>
    <div v-else-if="error" class="text-danger">{{ error }}</div>
    <template v-else>
      <div class="mb-3">
        <label class="form-label" for="chimein-folder">Folder</label>
        <select
          id="chimein-folder"
          v-model.number="selectedFolderId"
          class="form-select"
          @change="onFolderChange"
        >
          <option v-for="folder in folders" :key="folder.id" :value="folder.id">
            {{ folder.name }}
          </option>
        </select>
      </div>

      <ul class="list-group">
        <li
          v-for="question in questions"
          :key="question.id"
          class="list-group-item list-group-item-action"
          :class="{ 'list-group-item-secondary': usedQuestionIds.has(question.id) }"
          role="button"
          @click="selectedFolderId !== null && $emit('chosen', { folderId: selectedFolderId, questionId: question.id })"
        >
          <span v-html="question.text"></span>
          <span v-if="usedQuestionIds.has(question.id)" class="badge bg-secondary ms-2">
            already on a slide
          </span>
        </li>
        <li v-if="questions.length === 0" class="list-group-item text-muted">
          This folder has no questions yet.
        </li>
      </ul>
    </template>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref, watch } from "vue";
import * as T from "@/types";
import { getChime, getFolder } from "./lib/api";
import { readUsedQuestionIds } from "./lib/storage";

const props = defineProps<{
  token: string;
  chimeId: number;
  widgetId: string;
}>();

defineEmits<{
  (e: "chosen", value: { folderId: number; questionId: number }): void;
}>();

const chimeName = ref("");
const folders = ref<T.Folder[]>([]);
const selectedFolderId = ref<number | null>(null);
const questions = ref<T.Question[]>([]);
const usedQuestionIds = ref<Set<number>>(new Set());
const loading = ref(true);
const error = ref<string | null>(null);

async function loadQuestions(folderId: number) {
  const folder = await getFolder(props.token, props.chimeId, folderId);
  questions.value = folder.questions;
}

onMounted(async () => {
  try {
    const [chime, usedIds] = await Promise.all([
      getChime(props.token, props.chimeId),
      readUsedQuestionIds(props.widgetId),
    ]);

    chimeName.value = chime.name;
    folders.value = chime.folders ?? [];
    usedQuestionIds.value = new Set(usedIds);

    if (folders.value.length > 0) {
      selectedFolderId.value = folders.value[0].id;
      await loadQuestions(folders.value[0].id);
    }
  } catch (e) {
    error.value = "Couldn't load that chime's folders and questions.";
  } finally {
    loading.value = false;
  }
});

async function onFolderChange() {
  if (selectedFolderId.value === null) return;
  loading.value = true;
  try {
    await loadQuestions(selectedFolderId.value);
  } finally {
    loading.value = false;
  }
}

// first unused question in the current folder, used by the parent as a 1-click default
const suggestedQuestionId = computed(() =>
  questions.value.find((q) => !usedQuestionIds.value.has(q.id))?.id ?? null
);

defineExpose({ suggestedQuestionId, selectedFolderId });
</script>
