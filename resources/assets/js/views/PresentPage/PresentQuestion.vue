<template>
  <div
    class="present-question"
    :class="{
      in_progress: current_session,
      not_in_progress: !current_session,
    }"
  >
    <div class="container-fluid">
      <div class="row">
        <div class="col-sm-12 col-md-8 col-lg-9 present-question__inner">
          <PresentResults
            v-if="isShowingResults"
            :question="question"
            :chimeId="chime.id"
            @reload="$emit('reload')"
          />
          <PresentPrompt
            v-if="!isShowingResults"
            :session="current_session"
            :question="question"
          />
        </div>
        <div
          v-if="!isStudentView"
          class="col-sm-12 col-md-4 col-lg-3 presentationControls"
        >
          <JoinPanel
            v-if="showJoinInstructions"
            :chime="chime"
            :includeFullUrl="false"
            class="present-page__join-panel mb-2"
          />

          <div class="card">
            <div class="card-body">
              <button
                v-if="!current_session"
                class="btn btn-outline-primary align-items-center d-flex"
                @click="start_session"
              >
                <i class="material-icons left">play_arrow</i>
                Open Question
              </button>
              <button
                v-else
                :class="{ openSession: current_session }"
                class="btn btn-outline-primary align-items-center d-flex"
                @click="stop_session"
              >
                <i class="material-icons left">stop</i>
                Close Question
              </button>
              <button
                data-cy="show-results-button"
                class="btn btn-outline-primary align-items-center d-flex"
                @click="toggleShowResults"
              >
                <i class="material-icons left">zoom_in</i>
                <span v-if="isShowingResults"> Hide Results </span>
                <span v-else> View Results </span>
              </button>
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
                @click="toggle"
              >
                <i class="material-icons left">fullscreen</i>
                Fullscreen
              </button>
              <ul class="sessionStatus">
                <li v-if="current_session">
                  Session Responses:
                  {{ current_session ? current_session.responses.length : 0 }}
                </li>
                <li>Total Responses: {{ total_responses }}</li>
                <li>Connected Participants: {{ usersCount - 1 }}</li>
              </ul>
            </div>
          </div>
          <!-- card -->
        </div>
        <!-- presentationControls (sidebar) -->
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import PresentPrompt from "./PresentPrompt.vue";
import PresentResults from "./PresentResults.vue";
import JoinPanel from "../../components/JoinPanel.vue";
import { openQuestion, closeQuestion } from "../../common/api";
import { PropType } from "vue";
import * as T from "@/types";

export default {
  components: {
    PresentPrompt,
    PresentResults,
    JoinPanel,
  },
  props: {
    question: { type: Object as PropType<T.Question>, required: true },
    chime: { type: Object as PropType<T.Chime>, required: true },
    folder: { type: Object as PropType<T.FolderWithQuestions>, required: true },
    questionIndex: { type: Number, required: true },
    usersCount: { type: Number, required: true },
    isShowingResults: { type: Boolean, required: false, default: false },
    isStudentView: { type: Boolean, required: true },
  },
  emits: ["nextQuestion", "previousQuestion", "toggle", "reload"],
  computed: {
    current_session: function () {
      if (this.question.current_session_id) {
        var session = this.question.sessions.find(
          (s) => s.id == this.question.current_session_id
        );
        return session;
      } else {
        return null;
      }
    },
    total_responses: function () {
      if (this.question.sessions.length == 0) {
        return 0;
      }
      return this.question.sessions.reduce(function (accumulator, session) {
        return accumulator + session.responses.length;
      }, 0);
    },
    showJoinInstructions() {
      return Boolean(this.chime.join_instructions);
    },
  },
  mounted() {
    // if this is a student view and we are not showing results,
    // redirect to the results page
    if (this.isStudentView && !this.isShowingResults) {
      this.$router.replace({
        name: "presentResults",
        params: {
          chimeId: this.chime.id,
          folderId: this.folder.id,
          questionId: this.question.id,
        },
      });
    }
  },
  methods: {
    toggle() {
      this.$emit("toggle");
    },
    showResults() {
      this.$router.replace({
        name: "presentResults",
        params: {
          chimeId: this.chime.id,
          folderId: this.folder.id,
          questionIndex: this.questionIndex,
        },
      });
    },
    showPrompt() {
      this.$router.replace({
        name: "present",
        params: {
          chimeId: this.chime.id,
          folderId: this.folder.id,
          questionIndex: this.questionIndex,
        },
      });
    },
    toggleShowResults() {
      this.isShowingResults ? this.showPrompt() : this.showResults();
    },
    start_session() {
      openQuestion({
        chimeId: this.chime.id,
        folderId: this.folder.id,
        questionId: this.question.id,
      });
    },
    stop_session() {
      closeQuestion({
        chimeId: this.chime.id,
        folderId: this.folder.id,
        questionId: this.question.id,
      });
    },
  },
};
</script>

<style>
.questionDisplay {
  font-size: 1.8em;
  margin-top: 0.3em;
  margin-bottom: 0.3em;
}

.in_progress {
  border-top: 10px solid green;
}

.not_in_progress {
  border-top: 10px solid transparent;
}

.openSession {
  color: green;
  border-color: green;
}
.openSession:hover {
  background-color: green;
  color: white;
}

.presentationControls {
  margin-top: 2rem;
  display: flex;
  flex-direction: column;
  align-items: flex-end;
}

.presentationControls .card,
.present-page__join-panel {
  width: 100%;
  max-width: 18rem;
}

.presentationControls .card-title {
  font-size: 1.4em;
}

.presentationControls .btn {
  width: 100%;
  margin-top: 5px;
  margin-bottom: 5px;
}

.fullscreen .row {
  padding: 30px;
}

.sessionStatus {
  padding: 0;
  list-style-type: none;
}

.present-question__inner {
  margin-top: 2rem;
}
</style>
