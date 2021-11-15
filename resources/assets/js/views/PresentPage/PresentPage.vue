<template>
  <div>
    <NavBar
      v-if="!folder.student_view"
      title="Back to Folder"
      :user="user"
      :access_code="hyphenatedCode"
      :host="host"
      :link="{ name: 'folder', params: { chimeId, folderId } }"
    />

    <ErrorDialog />

    <div class="container-fluid presentContainer">
      <fullscreen ref="fullscreen" @change="fullscreenChange">
        <PresentQuestion
          v-if="current_question_item"
          :users-count="usersCount"
          :question="current_question_item"
          :chime-id="chimeId"
          :folder="folder"
          @nextQuestion="next_question"
          @previousQuestion="previous_question"
          @sessionUpdated="load_questions"
          @toggle="toggle"
          @reload="reload"
        />
        <div
          v-if="fullscreen && host"
          class="alert alert-info text-center fixed-bottom joinbox"
          role="alert"
        >
          <span class="text-center"
            >Go to <strong>{{ host }}</strong> and enter code
            <strong>{{ hyphenatedCode }}</strong></span
          >
        </div>
      </fullscreen>
    </div>
  </div>
</template>

<style>
.presentContainer {
  min-height: 90%;
}
.joinbox {
  margin: 10px;
}
.fullscreen {
  background: #fff;
}
</style>

<script>
import { component as fullscreen } from "vue-fullscreen";
import { questionsListener } from "../../mixins/questionsListener";
import toHyphenatedCode from "../../helpers/toHyphenatedCode.mjs";
import ErrorDialog from "../../components/ErrorDialog.vue";
import NavBar from "../../components/NavBar.vue";
import PresentQuestion from "./PresentQuestion.vue";

export default {
  components: {
    fullscreen,
    ErrorDialog,
    NavBar,
    PresentQuestion,
  },
  mixins: [questionsListener],
  props: ["user", "chimeId", "folderId", "questionId"],
  data() {
    return {
      folder: { name: "" },
      questions: [],
      show_results: false,
      current_question: 0,
      fullscreen: false,
      chime: null,
    };
  },
  computed: {
    current_question_item: function () {
      if (!this.questions || this.questions.length == 0) {
        return false;
      }
      return this.questions[this.current_question];
    },
    host: function () {
      if (this.chime && this.chime.join_instructions) {
        return window.location.host;
      }
      return null;
    },
    hyphenatedCode: function () {
      if (!this.chime || !this.chime.join_instructions) {
        return "";
      }
      return toHyphenatedCode(this.chime.access_code);
    },
  },
  watch: {
    $route(to) {
      this.current_question = parseInt(to.params.questionId);
    },
  },
  mounted: function () {
    this.current_question = parseInt(this.$route.params.questionId) || 0;
    this.load_questions();
    axios.get("/api/chime/" + this.chimeId).then((res) => {
      console.log(res);
      this.chime = res.data;
    });
  },
  beforeDestroy: function () {
    Echo.leave("session-status." + this.chimeId);
  },
  methods: {
    toggle() {
      this.$refs["fullscreen"].toggle();
    },
    reload() {
      this.load_questions();
    },
    fullscreenChange(fullscreen) {
      this.fullscreen = fullscreen;
    },
    next_question: function () {
      var target = 0;
      if (this.questions.length > this.current_question + 1) {
        target = this.current_question + 1;
      }
      this.$router.replace({
        name: "present",
        params: {
          chimeId: this.chimeId,
          folderId: this.folderId,
          questionId: target,
        },
      });
    },
    previous_question: function () {
      var target = this.current_question - 1;
      if (this.current_question - 1 < 0) {
        target = this.questions.length - 1;
      }
      this.$router.replace({
        name: "present",
        params: {
          chimeId: this.chimeId,
          folderId: this.folderId,
          questionId: target,
        },
      });
    },
  },
};
</script>
