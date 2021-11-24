<template>
  <Card class="question-card">
    <div class="flow-text question_list_text" v-html="question.text" />

    <template #actions>
      <Chip color="primary">{{ totalResponses }} Responses</Chip>

      <PrettyCheck
        :checked="isOpen"
        name="isOpen"
        data-cy="toggle-open-question"
        class="open-question-toggle p-switch p-outline"
        color="success"
        @change="handleToggleOpenQuestion($event)"
      >
        {{ isOpen ? "Open" : "Closed" }}
      </PrettyCheck>

      <CardActionButton icon="edit" @click="handleEditClick"
        >Edit</CardActionButton
      >

      <CardActionButton icon="play_circle_outline" @click="handlePresentClick"
        >Present</CardActionButton
      >
    </template>
  </Card>
</template>

<script>
import PrettyCheck from "pretty-checkbox-vue/check";
import Card from "../../components/Card.vue";
import Chip from "../../components/Chip.vue";
import CardActionButton from "../../components/CardActionButton.vue";

export default {
  components: {
    PrettyCheck,
    Card,
    CardActionButton,
    Chip,
  },
  props: ["folder", "question"],
  data: function () {
    return {
      showEdit: false,
    };
  },
  computed: {
    totalResponses() {
      return this.question.sessions.reduce(
        (acc, session) => acc + session.responses.length,
        0
      );
    },
    isOpen() {
      const { sessions, current_session_id } = this.question;
      // check that question's active sessions includes the current_session_id
      return sessions.some((s) => s.id === current_session_id);
    },
  },
  methods: {
    handleEditClick() {
      this.$emit("editquestion");
      this.show_edit = false;
    },
    handlePresentClick() {},
    handleDeleteClick() {
      this.$emit("deletequestion", this.question.id);
    },
    handleToggleOpenQuestion(setToOpen) {
      const chimeId = this.folder.chime_id;
      const folderId = this.folder.id;
      const questionId = this.question.id;

      const questionUrl = `/api/chime/${chimeId}/folder/${folderId}/question/${questionId}`;

      if (setToOpen) {
        //  open question
        axios
          .post(questionUrl)
          .catch((err) =>
            console.error(
              `Error opening question: ${err.message}`,
              err.response
            )
          );
      } else {
        // close question
        axios
          .put(`${questionUrl}/stopSession`)
          .catch((err) =>
            console.error(
              `Error closing question: ${err.message}`,
              err.response
            )
          );
      }
    },
  },
};
</script>

<style scoped>
.open-question-toggle {
  display: flex;
  flex-direction: column;
  font-size: 17px;
}
.open-question-toggle label {
  display: block;
}
</style>
