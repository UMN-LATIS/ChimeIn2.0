<template>
  <div
    class="present-question row"
    :class="{
      in_progress: current_session,
      not_in_progress: !current_session,
    }"
  >
    <div class="col-sm-12 col-md-8 col-lg-9 present-question__inner">
      <PresentResults
        v-if="show_results"
        :question="question"
        :chime-id="chime.id"
        @reload="$emit('reload')"
      />
      <PresentPrompt
        v-if="!show_results"
        :session="current_session"
        :question="question"
      />
    </div>
    <div
      v-if="!folder.student_view"
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
            @click="show_results = !show_results"
          >
            <i class="material-icons left">zoom_in</i>
            <span v-if="show_results"> Hide Results </span>
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
</template>

<script>
import PresentPrompt from "./PresentPrompt.vue";
import PresentResults from "./PresentResults.vue";
import JoinPanel from "./JoinPanel.vue";

export default {
  components: {
    PresentPrompt,
    PresentResults,
    JoinPanel,
  },
  props: ["question", "chime", "folder", "usersCount"],
  data() {
    return {
      show_results: false,
    };
  },
  computed: {
    current_session: function () {
      if (this.question.current_session_id) {
        var session = this.question.sessions.find(
          (s) => s.id == this.question.current_session_id
        );
        return session;
      } else {
        return false;
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
    start_session: function () {
      const url =
        "/api/chime/" +
        this.chime.id +
        "/folder/" +
        this.folder.id +
        "/question/" +
        this.question.id;

      axios.post(url, {}).catch((err) => {
        console.error(err.response);
      });
    },
    stop_session: function () {
      const url =
        "/api/chime/" +
        this.chime.id +
        "/folder/" +
        this.folder.id +
        "/question/" +
        this.question.id +
        "/stopSession/";

      axios.put(url, {}).catch((err) => {
        console.error(err.response);
      });
    },
    showJoinInstructions() {
      return Boolean(this.chime.join_instructions);
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
