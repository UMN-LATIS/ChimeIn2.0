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
            v-if="show_results"
            :question="question"
            :chimeId="chime.id"
            @reload="$emit('reload')"
          />
          <PresentPrompt
            v-if="!show_results"
            :session="current_session"
            :question="question"
          />
        </div>
        <PresentationControls
          v-if="!folder.student_view"
          :showJoinInstructions="showJoinInstructions"
          :chime="chime"
          :folder="folder"
          :currentSession="current_session"
          :totalResponses="total_responses"
          :usersCount="usersCount"
          :showResults="show_results"
          class="col-sm-12 col-md-4 col-lg-3 presentationControls"
          @startSession="start_session"
          @stopSession="stop_session"
          @toggleShowResults="show_results = !show_results"
          @toggleFullScreen="$emit('toggle')"
          @nextQuestion="$emit('nextQuestion')"
          @previousQuestion="$emit('previousQuestion')"
        />
      </div>
    </div>
  </div>
</template>

<script>
import PresentPrompt from "./PresentPrompt.vue";
import PresentResults from "./PresentResults.vue";
import PresentationControls from "@/components/PresentationControls.vue";
import { openQuestion, closeQuestion } from "../../common/api";

export default {
  components: {
    PresentPrompt,
    PresentResults,
    PresentationControls,
  },
  props: {
    question: { type: Object, required: true },
    chime: { type: Object, required: true },
    folder: { type: Object, required: true },
    usersCount: { type: Number, required: true },
  },
  emits: ["nextQuestion", "previousQuestion", "toggle", "reload"],
  data() {
    return {
      show_results: false,
    };
  },
  computed: {
    current_session: function () {
      if (this.question.current_session_id) {
        var session = this.question.sessions.find(
          (s) => s.id == this.question.current_session_id,
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
        return accumulator + parseInt(session.responses.length);
      }, 0);
    },
    showJoinInstructions() {
      return Boolean(this.chime.join_instructions);
    },
  },
  mounted() {
    if (this.folder.student_view) {
      this.show_results = true;
    }
  },
  methods: {
    toggle() {
      this.$emit("toggle");
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
