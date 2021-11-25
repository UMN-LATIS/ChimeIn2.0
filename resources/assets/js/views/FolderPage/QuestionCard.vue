<template>
  <Card
    class="question-card"
    :icon="showMoveIcon ? 'drag_handle' : 'arrow_forward'"
    iconClass="handle"
  >
    <footer class="question-card__footer">
      <span class="question-card__question-type">{{
        questionTypeToString
      }}</span>
      <Chip :color="totalResponses ? 'primary' : 'muted'" solid="true"
        >{{ totalResponses }} {{ pluralize("Response", totalResponses) }}</Chip
      >
    </footer>
    <div class="flow-text question_list_text" v-html="question.text" />

    <component
      v-if="hasSpecializedQuestionDisplay(questionType)"
      class="question-card__choice-display"
      :is="`${questionType}_display`"
      :question="question"
    />

    <QuestionForm
      v-if="showEdit"
      :show="showEdit"
      :question="question"
      :folder="folder"
      control-type="edit"
      @edited="handleQuestionEdited"
      @close="showEdit = false"
    />

    <template #actions>
      <Toggle
        data-cy="toggle-open-question"
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

      <CardActionButton
        icon="play_circle_outline"
        :to="`/chime/${folder.chime_id}/folder/${folder.id}/present/${
          question.order - 1
        }`"
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
import QuestionForm from "../QuestionForm/QuestionForm.vue";
import MultipleChoiceDisplay from "../../components/MultipleChoice/MultipleChoiceDisplay.vue";
import HeatmapResponseDisplay from "../../components/ImageHeatmapResponse/ImageHeatmapResponseDisplay.vue";
import hasSpecializedQuestionDisplay from "../../helpers/hasSpecializedQuestionDisplay";

export default {
  components: {
    Card,
    CardActionButton,
    Chip,
    Toggle,
    QuestionForm,
    multiple_choice_display: MultipleChoiceDisplay,
    heatmap_response_display: HeatmapResponseDisplay,
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
    showMoveIcon: {
      type: Boolean,
      required: true,
    },
  },
  events: ["change"],
  data: function () {
    return {
      showEdit: false,
    };
  },
  computed: {
    questionType() {
      return this.question.question_info.question_type;
    },
    questionTypeToString() {
      return this.questionType
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
    hasSpecializedQuestionDisplay,
    handleEditClick() {
      this.showEdit = true;
    },
    handlePresentClick() {},
    handleDeleteClick() {
      if (confirm("Are you sure you want to remove this question?")) {
        const chimeId = this.folder.chime_id;
        const folderId = this.folder.id;
        const questionId = this.question.id;

        const url = `/api/chime/${chimeId}/folder/${folderId}/question/${questionId}`;

        axios
          .delete(url)
          .then(() => this.$emit("change"))
          .catch((err) => console.log(err.response));
      }
    },
    handleQuestionEdited() {
      this.$emit("change");
      this.showEdit = false;
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
    pluralize(str, count) {
      return count === 1 ? str : str + "s";
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

.total-responses {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: flex-start;
  padding: 1rem;
  margin-bottom: 1rem;
}

.total-responses__label {
  text-transform: uppercase;
  color: #aaa;
  margin-bottom: 0.5rem;
  font-size: 0.9rem;
}

.total-responses__number {
  font-size: 3rem;
}
</style>
<style>
.question-card__choice-display.mult-choice-display {
  padding-left: 1rem;
}
.question-card__choice-display .questionDisplay {
  font-size: 1rem;
}
.question-card__choice-display.max-height-image {
  border: 1px solid #ddd;
  height: 10rem;
  max-width: 100%;
  max-height: 100%;
}
</style>
