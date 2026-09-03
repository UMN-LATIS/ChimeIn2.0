<template>
  <div class="chimein-bound">
    <div v-if="error" class="alert alert-danger">
      {{ error }}
      <button class="btn btn-sm btn-outline-danger ms-2" @click="$emit('reconnect')">
        Reconnect
      </button>
    </div>

    <template v-else-if="question">
      <div v-if="isEditView" class="chimein-controls">
        <button
          v-if="!currentSession"
          class="btn btn-sm btn-outline-primary"
          @click="onOpen"
        >
          Open Question
        </button>
        <button v-else class="btn btn-sm btn-outline-primary" @click="onClose">
          Close Question
        </button>
        <button class="btn btn-sm btn-outline-secondary" @click="toggleResults">
          {{ showingResults ? "Hide Results" : "Show Results" }}
        </button>
        <button class="btn btn-sm btn-link" @click="$emit('reconnect')">
          Change question
        </button>
        <span class="text-muted ms-2">
          Responses: {{ currentSession?.responses?.length ?? 0 }}
        </span>
      </div>

      <div ref="stageEl" class="chimein-stage" :class="{ 'is-results': showingResults }">
        <PresentResults
          v-if="showingResults"
          :question="question"
          :sessions="question.sessions"
          :currentSession="currentSession"
          :chimeId="chimeId"
          :userLookup="userLookup"
          @reload="refresh"
        />
        <PresentPrompt v-else :session="currentSession ?? undefined" :question="question" />
      </div>
    </template>
  </div>
</template>

<script setup lang="ts">
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from "vue";
import type Echo from "laravel-echo";
import * as T from "@/types";
import PresentPrompt from "../views/PresentPage/PresentPrompt.vue";
import PresentResults from "../views/PresentPage/PresentResults.vue";
import { closeQuestion, getChimeUsers, getQuestion, openQuestion, UnauthorizedError } from "./lib/api";
import { createOfficeEchoClient } from "./lib/echo";
import { getActiveView } from "./lib/storage";

const props = defineProps<{
  token: string;
  chimeId: number;
  questionId: number;
}>();

defineEmits<{ (e: "reconnect"): void }>();

const question = ref<T.Question | null>(null);
const userLookup = ref<Map<number, T.User>>(new Map());
const error = ref<string | null>(null);
const showingResults = ref(false);
const isEditView = ref(true);
const stageEl = ref<HTMLElement | null>(null);

let echo: Echo<"reverb"> | null = null;
let pollTimer: number | null = null;

const currentSession = computed(() =>
  question.value?.sessions.find((s) => s.id === question.value?.current_session_id) ?? null
);

async function refresh() {
  try {
    const [loaded, users] = await Promise.all([
      getQuestion(props.token, props.chimeId, props.questionId),
      getChimeUsers(props.token, props.chimeId),
    ]);
    question.value = loaded;
    userLookup.value = new Map(users.map((u) => [u.id, u]));
    error.value = null;
  } catch (e) {
    error.value =
      e instanceof UnauthorizedError
        ? e.message
        : "Couldn't reach ChimeIn. Retrying in the background.";
  }
}

async function onOpen() {
  question.value = await openQuestion(props.token, props.chimeId, props.questionId);
}

async function onClose() {
  question.value = await closeQuestion(props.token, props.chimeId, props.questionId);
}

async function toggleResults() {
  showingResults.value = !showingResults.value;
  await nextTick();
  stageEl.value?.scrollTo({ top: 0 });
  window.scrollTo({ top: 0 });
}

function startPolling() {
  // Fallback for when the socket can't connect (locked-down networks, etc).
  pollTimer = window.setInterval(refresh, 15000);
}

onMounted(async () => {
  isEditView.value = (await getActiveView()) === "edit";
  await refresh();

  try {
    echo = createOfficeEchoClient(props.token);
    echo
      .private(`session-status.${props.chimeId}`)
      .listen("StartSession", (event: { session: T.Session & { question: { id: number } } }) => {
        if (event.session.question.id !== props.questionId || !question.value) return;
        question.value.current_session_id = event.session.id;
        question.value.sessions.push({ ...event.session, responses: [] });
      })
      .listen("EndSession", (event: { session: { question_id: number } }) => {
        if (event.session.question_id !== props.questionId || !question.value) return;
        question.value.current_session_id = null;
      });

    echo
      .private(`session-response.${props.chimeId}`)
      .listen("SubmitResponse", (event: { session: { id: number; question: { id: number } } }) => {
        if (event.session.question.id !== props.questionId) return;
        refresh();
      });
  } catch {
    // Echo failed to initialize; polling below covers it.
  }

  startPolling();
});

onUnmounted(() => {
  echo?.leave(`session-status.${props.chimeId}`);
  echo?.leave(`session-response.${props.chimeId}`);
  echo?.disconnect();
  if (pollTimer !== null) window.clearInterval(pollTimer);
});

watch(
  () => props.questionId,
  () => refresh()
);
</script>

<style scoped>
.chimein-bound {
  min-height: calc(100vh - 1.5rem);
}

.chimein-controls {
  position: sticky;
  top: 0;
  z-index: 2;
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.4rem;
  margin: -0.75rem -0.75rem 0.75rem;
  padding: 0.65rem 0.75rem;
  background: rgba(255, 255, 255, 0.96);
  border-bottom: 1px solid #d9e0e8;
  box-shadow: 0 2px 8px rgba(35, 49, 66, 0.08);
}

.chimein-controls .btn {
  border-radius: 0.35rem;
  font-weight: 600;
  line-height: 1.2;
}

.chimein-controls .text-muted {
  margin-left: 0;
  white-space: nowrap;
}

.chimein-stage {
  overflow: auto;
  padding: 0.85rem;
  background: #fff;
  border: 1px solid #d9e0e8;
  border-radius: 0.45rem;
  box-shadow: 0 1px 3px rgba(35, 49, 66, 0.06);
}

.chimein-stage.is-results {
  min-height: 12rem;
}

.chimein-stage :deep(.row) {
  margin-right: 0;
  margin-left: 0;
}

.chimein-stage :deep(.col) {
  padding-right: 0;
  padding-left: 0;
}

.chimein-stage :deep(h1) {
  margin-bottom: 0.85rem;
  color: #172331;
  font-size: 1.15rem;
  font-weight: 700;
  line-height: 1.3;
}

.chimein-stage :deep(.form-control) {
  width: 100%;
  max-width: 100%;
}

.chimein-stage :deep(svg),
.chimein-stage :deep(canvas),
.chimein-stage :deep(img) {
  max-width: 100%;
}
</style>
