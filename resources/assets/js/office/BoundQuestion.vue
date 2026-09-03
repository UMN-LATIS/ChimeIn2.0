<template>
  <div class="chimein-bound">
    <div v-if="error" class="alert alert-danger">
      {{ error }}
      <button class="btn btn-sm btn-outline-danger ms-2" @click="$emit('reconnect')">
        Reconnect
      </button>
    </div>

    <template v-else-if="question">
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

      <div v-if="isEditView" class="chimein-controls mt-2">
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
        <button class="btn btn-sm btn-outline-secondary" @click="showingResults = !showingResults">
          {{ showingResults ? "Hide Results" : "Show Results" }}
        </button>
        <button class="btn btn-sm btn-link" @click="$emit('reconnect')">
          Change question
        </button>
        <span class="text-muted ms-2">
          Responses: {{ currentSession?.responses?.length ?? 0 }}
        </span>
      </div>
    </template>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref, watch } from "vue";
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
