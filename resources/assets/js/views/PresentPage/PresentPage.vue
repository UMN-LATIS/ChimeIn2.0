<template>
  <div class="present-page">
    <NavBar
      v-if="!folder.student_view"
      title="Back to Folder"
      :user="user"
      :access_code="hyphenatedCode"
      :host="host"
      :link="{ name: 'folder', params: { chimeId, folderId } }"
    />
    <ErrorDialog />

    <Spinner v-if="!chime" />
    <div v-if="chime" class="container-fluid presentContainer">
      <fullscreen ref="fullscreen" @change="fullscreenChange">
        <JoinPanel
          v-if="showJoinInstructions"
          :chime="testCanvasChime"
          class="present-page__join-panel"
        />
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
      </fullscreen>
    </div>
  </div>
</template>

<script>
import { component as fullscreen } from "vue-fullscreen";
import { questionsListener } from "../../mixins/questionsListener";
import toHyphenatedCode from "../../helpers/toHyphenatedCode.js";
import ErrorDialog from "../../components/ErrorDialog.vue";
import NavBar from "../../components/NavBar.vue";
import PresentQuestion from "./PresentQuestion.vue";
import JoinPanel from "./JoinPanel.vue";
import Spinner from "../../components/Spinner.vue";

const testCanvasChime = {
  id: 1609,
  access_code: "183086",
  created_at: "2021-08-29T21:48:03.000000Z",
  updated_at: "2021-10-08T18:45:50.000000Z",
  name: "SLHS 3302 (001) Anatomy and Physiology of the Speech and Hearing Mechanisms (Fall 2021)",
  lti_return_url: "https://canvas.umn.edu/courses/266159/modules",
  lti_course_title:
    "SLHS 3302 (001) Anatomy and Physiology of the Speech and Hearing Mechanisms (Fall 2021)",
  lti_course_id: "841b04e4d0419cf5a2561dc371e15c8f0430b8d6",
  require_login: 1,
  deleted_at: null,
  students_can_view: 1,
  join_instructions: 1,
  only_correct_answers_lti: 1,
  lti_setup_complete: 1,
  resource_link_pk: null,
  lti13_resource_link_id: null,
  lti_grade_mode: null,
  show_folder_title_to_participants: 1,
  pivot: {
    user_id: 726785,
    chime_id: 1609,
    permission_number: 300,
    created_at: "2021-10-07T22:00:37.000000Z",
    updated_at: "2021-10-15T02:31:57.000000Z",
  },
  folders: [
    {
      id: 3745,
      created_at: "2021-08-29T21:51:12.000000Z",
      updated_at: "2021-08-29T21:51:12.000000Z",
      name: "Introduction",
      chime_id: 1609,
      resource_link_pk: 2030,
      deleted_at: null,
      order: 2,
      lti_lineitem: null,
    },
    {
      id: 4009,
      created_at: "2021-09-14T18:51:38.000000Z",
      updated_at: "2021-09-14T18:51:38.000000Z",
      name: "SLHS 3302: Week 2",
      chime_id: 1609,
      resource_link_pk: 2031,
      deleted_at: null,
      order: 3,
      lti_lineitem: null,
    },
    {
      id: 4108,
      created_at: "2021-09-21T18:33:49.000000Z",
      updated_at: "2021-09-21T18:33:49.000000Z",
      name: "SLHS 3302: Week 3",
      chime_id: 1609,
      resource_link_pk: 2032,
      deleted_at: null,
      order: 4,
      lti_lineitem: null,
    },
    {
      id: 4316,
      created_at: "2021-10-07T14:55:36.000000Z",
      updated_at: "2021-10-07T14:55:36.000000Z",
      name: "SLHS 3302: Week 5",
      chime_id: 1609,
      resource_link_pk: 2033,
      deleted_at: null,
      order: 5,
      lti_lineitem: null,
    },
    {
      id: 4404,
      created_at: "2021-10-13T22:12:35.000000Z",
      updated_at: "2021-10-13T22:12:35.000000Z",
      name: "Fall 2021 (SLHS 3302)",
      chime_id: 1609,
      resource_link_pk: 2069,
      deleted_at: null,
      order: 1,
      lti_lineitem: null,
    },
    {
      id: 4405,
      created_at: "2021-10-13T22:13:08.000000Z",
      updated_at: "2021-10-13T22:13:08.000000Z",
      name: "SLHS 3302: Week 6",
      chime_id: 1609,
      resource_link_pk: null,
      deleted_at: null,
      order: 6,
      lti_lineitem: null,
    },
    {
      id: 4461,
      created_at: "2021-10-18T16:50:57.000000Z",
      updated_at: "2021-10-18T16:50:57.000000Z",
      name: "SLHS 3302: Week 7",
      chime_id: 1609,
      resource_link_pk: null,
      deleted_at: null,
      order: 7,
      lti_lineitem: null,
    },
    {
      id: 4709,
      created_at: "2021-11-11T19:38:55.000000Z",
      updated_at: "2021-11-11T19:38:55.000000Z",
      name: "ChimeIn 2",
      chime_id: 1609,
      resource_link_pk: 2248,
      deleted_at: null,
      order: 1,
      lti_lineitem: null,
    },
    {
      id: 4710,
      created_at: "2021-11-11T19:39:34.000000Z",
      updated_at: "2021-11-11T19:39:34.000000Z",
      name: "SLHS 3302: Week 10",
      chime_id: 1609,
      resource_link_pk: null,
      deleted_at: null,
      order: 8,
      lti_lineitem: null,
    },
  ],
};

export default {
  components: {
    fullscreen,
    ErrorDialog,
    NavBar,
    PresentQuestion,
    JoinPanel,
    Spinner,
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
      testCanvasChime,
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
    showJoinInstructions() {
      return !!this.chime.join_instructions;
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

<style>
.fullscreen {
  background: #fff;
}
.present-page__join-panel {
  position: fixed;
  top: 0.5rem;
  left: 50%;
  transform: translateX(-50%);
}
</style>
