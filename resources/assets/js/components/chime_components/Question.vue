<template>
  <li class="questionRow">
    <div class="row">
      <div class="col-sm-9">
        <question-form
          v-if="show_edit"
          :show="show_edit"
          @edited="edit_question"
          @close="show_edit = false"
          :question="question"
          :folder="folder"
          controlType="edit"
        >
        </question-form>
        <div class="draghandle">
          <p class="response_label">{{ total_responses }}</p>
          <p class="flow-text question_list_text" v-html="question.text"></p>
        </div>
      </div>
      <div class="col-sm-3">
        <div class="float-right">
          <p-check
            name="check"
            data-cy="toggle-open-question"
            class="p-switch p-outline"
            color="success"
            v-model="check"
          >
            &nbsp;
          </p-check>
          <router-link
            data-cy="present-question-button"
            :to="{
              name: 'present',
              params: {
                chimeId: folder.chime_id,
                folderId: folder.id,
                questionId: question.order - 1,
              },
            }"
            class="text-dark"
          >
            <i class="material-icons">play_arrow</i>
          </router-link>
          <a
            class="pointer text-dark"
            @click="show_edit = !show_edit"
            data-cy="edit-question-button"
          >
            <i class="material-icons">edit</i>
          </a>
          <a
            class="pointer text-dark"
            @click="delete_question"
            data-cy="delete-question-button"
          >
            <i class="material-icons">delete</i>
          </a>
        </div>
      </div>
    </div>
  </li>
</template>

<script>
const QuestionForm = () =>
  import(
    /* webpackChunkName: "QuestionForm" */
    "./QuestionForm.vue"
  );

export default {
  props: ["folder", "question"],
  components: {
    "question-form": QuestionForm,
  },
  data: function () {
    return {
      show_edit: false,
    };
  },
  methods: {
    edit_question: function () {
      this.$emit("editquestion");
      this.show_edit = false;
    },
    delete_question: function () {
      this.$emit("deletequestion", this.question.id);
    },
  },
  computed: {
    total_responses: function () {
      if (this.question.sessions.length == 0) {
        return 0;
      }
      return this.question.sessions.reduce(function (accumulator, session) {
        return accumulator + parseInt(session.responses.length);
      }, 0);
    },
    check: {
      get: function () {
        if (this.question.current_session_id) {
          var session = this.question.sessions.find(
            (s) => s.id == this.question.current_session_id
          );
          return session;
        } else {
          return false;
        }
      },
      set: function (newValue) {
        if (newValue == true) {
          const url =
            "/api/chime/" +
            this.folder.chime_id +
            "/folder/" +
            this.folder.id +
            "/question/" +
            this.question.id;

          axios
            .post(url, {})
            .then((res) => {})
            .catch((err) => {
              console.log(err.response);
            });
        } else {
          const url =
            "/api/chime/" +
            this.folder.chime_id +
            "/folder/" +
            this.folder.id +
            "/question/" +
            this.question.id +
            "/stopSession/";

          axios
            .put(url, {})
            .then((res) => {})
            .catch((err) => {
              console.log(err.response);
            });
        }
      },
    },
  },
};
</script>
<style>
.question_list_text img {
  max-width: 100px;
  max-height: 100px;
}
</style>

<style scoped>
.questionRow {
  cursor: move;
}
.pointer {
  cursor: pointer;
}
.card-title {
  margin: 0 auto;
  max-width: 500px;
}

.response_label {
  display: inline-block;
  border-radius: 10px;
  background-color: lightblue;
  padding-left: 5px;
  padding-right: 5px;
  vertical-align: top;
}

.flow-text {
  display: inline-block;
  width: 90%;
}

.pretty {
  margin-right: 0;
  font-size: 15px;
}

.p-switch {
  vertical-align: top;
  margin-top: 6px;
}
</style>
