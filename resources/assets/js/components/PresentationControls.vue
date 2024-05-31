<template>
  <div>
    <JoinPanel
      v-if="showJoinInstructions"
      :chime="chime"
      :includeFullUrl="false"
      class="present-page__join-panel mb-2"
    />

    <div class="card">
      <div class="card-body">
        <button
          v-if="!currentSession"
          class="btn btn-outline-primary align-items-center d-flex"
          @click="$emit('startSession')"
        >
          <i class="material-icons left">play_arrow</i>
          Open Question
        </button>
        <button
          v-else
          :class="{ openSession: currentSession }"
          class="btn btn-outline-primary align-items-center d-flex"
          @click="$emit('stopSession')"
        >
          <i class="material-icons left">stop</i>
          Close Question
        </button>
        <button
          data-cy="show-results-button"
          class="btn btn-outline-primary align-items-center d-flex"
          @click="$emit('toggleShowResults')"
        >
          <i class="material-icons left">zoom_in</i>
          <span v-if="showResults"> Hide Results </span>
          <span v-else> View Results </span>
        </button>
        <RouterLink
          v-if="showResults"
          :to="`/chime/${chime.id}/folder/${folder.id}/present/${questionIndex}`"
          class="btn btn-outline-primary align-items-center d-flex"
          >Hide Results</RouterLink
        >
        <RouterLink
          v-else
          :to="`/chime/${chime.id}/folder/${folder.id}/present/${questionIndex}/results`"
          class="btn btn-outline-primary align-items-center d-flex"
          >Show Results</RouterLink
        >
        <button
          v-if="folder.questions.length > 1"
          class="btn btn-outline-primary align-items-center d-flex"
          @click="$emit('nextQuestion')"
        >
          <i class="material-icons left">arrow_right</i>
          Next Question
        </button>
        <button
          v-if="folder.questions.length > 1"
          class="btn btn-outline-primary align-items-center d-flex"
          @click="$emit('previousQuestion')"
        >
          <i class="material-icons left">arrow_left</i>
          Previous Question
        </button>
        <button
          class="btn btn-outline-primary align-items-center d-flex"
          @click="$emit('toggleFullScreen')"
        >
          <i class="material-icons left">fullscreen</i>
          Fullscreen
        </button>
        <ul class="sessionStatus">
          <li v-if="currentSession">
            Session Responses:
            {{ currentSession ? currentSession.responses.length : 0 }}
          </li>
          <li>Total Responses: {{ totalResponses }}</li>
          <li>Connected Participants: {{ usersCount - 1 }}</li>
        </ul>
      </div>
    </div>
  </div>
</template>
<script setup lang="ts">
import JoinPanel from "./JoinPanel.vue";
import type { Chime, FolderWithQuestions, Session } from "@/types";

defineProps<{
  showJoinInstructions: boolean;
  chime: Chime;
  folder: FolderWithQuestions;
  questionIndex: number;
  currentSession: Session | null;
  totalResponses: number;
  showResults: boolean;
  usersCount: number;
}>();

defineEmits<{
  (eventName: "startSession"): void;
  (eventName: "stopSession"): void;
  (eventName: "toggleShowResults"): void;
  (eventName: "toggleFullScreen"): void;
  (eventName: "nextQuestion"): void;
  (eventName: "previousQuestion"): void;
}>();
</script>
<style scoped></style>
