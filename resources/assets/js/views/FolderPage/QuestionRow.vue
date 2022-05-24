<template>
  <li class="questionRow">
    <div class="row">
      <div class="col-sm-9">
        <QuestionForm
          v-if="show_edit"
          :show="show_edit"
          :question="question"
          :folder="folder"
          controlType="edit"
          @edited="edit_question"
          @close="show_edit = false"
        />
        <div class="draghandle">
          <p class="response_label">{{ total_responses }}</p>
          <p class="flow-text question_list_text" v-html="question.text"></p>
        </div>
      </div>
      <div class="col-sm-3">
        <div class="float-right">
          <PrettyCheck
            v-model="check"
            name="check"
            data-cy="toggle-open-question"
            class="p-switch p-outline"
            color="success"
          >
            &nbsp;
          </PrettyCheck>
          <router-link
            data-cy="present-question-button"
            :to="{
              name: 'present',
              params: {
                chimeId: folder.chime_id,
                folderId: folder.id,
                questionIndex: question.order - 1,
              },
            }"
            class="text-dark"
          >
            <i class="material-icons">play_arrow</i>
          </router-link>
          <a
            class="pointer text-dark"
            data-cy="edit-question-button"
            @click="show_edit = !show_edit"
          >
            <i class="material-icons">edit</i>
          </a>
          <a
            class="pointer text-dark"
            data-cy="delete-question-button"
            @click="delete_question"
          >
            <i class="material-icons">delete</i>
          </a>
        </div>
      </div>
    </div>
  </li>
</template>

<script>
import PrettyCheck from "pretty-checkbox-vue/check";
const QuestionForm = () =>
  import(
    /* webpackChunkName: "QuestionForm" */
    "../QuestionForm/QuestionForm.vue"
  );

export default {
  components: {
    QuestionForm,
    PrettyCheck,
  },
  // eslint-disable-next-line vue/require-prop-types
  props: ["folder", "question"],
  emits: ["editquestion", "deletequestion"],
  data: function () {
    return {
      show_edit: false,
    };
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

          axios.post(url, {}).catch((err) => {
            console.error(err.response);
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

          axios.put(url, {}).catch((err) => {
            console.error(err.response);
          });
        }
      },
    },
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
