<template>
  <Card class="question-card">
    <footer class="question-card__footer">
      <span class="question-card__question-type">{{ questionType }}</span>
      <Chip color="primary">{{ totalResponses }} Responses</Chip>
    </footer>
    <div class="flow-text question_list_text" v-html="question.text" />

    <template #actions>
      <Toggle
        :checked="isOpen"
        :name="`question-${question.id}-isOpen`"
        color="green"
        @change="handleToggleOpenQuestion($event)"
      >
        {{ isOpen ? "Open" : "Closed" }}
      </Toggle>

      <CardActionButton icon="edit" @click="handleEditClick"
        >Edit</CardActionButton
      >

      <CardActionButton icon="play_circle_outline" @click="handlePresentClick"
        >Present</CardActionButton
      >
      <CardActionButton icon="clear" @click="handleDeleteClick"
        >Delete</CardActionButton
      >
    </template>
  </Card>
</template>

<script>
import Card from "../../components/Card.vue";
import Chip from "../../components/Chip.vue";
import CardActionButton from "../../components/CardActionButton.vue";
import Toggle from "../../components/Toggle.vue";

export default {
  components: {
    Card,
    CardActionButton,
    Chip,
    Toggle,
  },
  props: {
    folder: {
      type: Object,
      required: true,
    },
    question: {
      type: Object,
      required: true,
    },
  },
  data: function () {
    return {
      showEdit: false,
    };
  },
  computed: {
    questionType() {
      return this.question.question_info.question_type
        .split("_")
        .map((str) => str.charAt(0).toUpperCase() + str.slice(1))
        .join(" ");
    },
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
    handleToggleOpenQuestion($event) {
      const shouldOpen = $event.target.checked;
      const chimeId = this.folder.chime_id;
      const folderId = this.folder.id;
      const questionId = this.question.id;

      const questionUrl = `/api/chime/${chimeId}/folder/${folderId}/question/${questionId}`;

      if (shouldOpen) {
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
.question-card__question-type {
  text-transform: uppercase;
  font-size: 0.8rem;
  font-weight: bold;
}
.question-card__footer {
  display: flex;
  justify-content: space-between;
  align-items: baseline;
  margin-bottom: 1rem;
}
</style>
