<template>
  <Card
    class="question-card"
    :icon="showMoveIcon ? 'drag_handle' : ''"
    iconClass="handle"
  >
    <header class="question-card__header">
      <h2 class="question-card__question-type">
        <router-link
          :to="`/chime/${folder.chime_id}/folder/${folder.id}/present/${orderedQuestionIndex}`"
        >
          {{ questionTypeToString }}
        </router-link>
      </h2>
      <Chip :color="totalResponses ? 'primary' : 'muted'" :solid="true"
        >{{ totalResponses }} {{ pluralize("Response", totalResponses) }}</Chip
      >
    </header>

    <router-link
      :to="`/chime/${folder.chime_id}/folder/${folder.id}/present/${orderedQuestionIndex}`"
    >
      <!-- eslint-disable-next-line vue/no-v-html -->
      <div class="flow-text question_list_text" v-html="question.text" />

      <component
        :is="`${questionType}_display`"
        v-if="hasSpecializedQuestionDisplay(questionType)"
        class="question-card__choice-display"
        :question="question"
      />
    </router-link>

    <QuestionForm
      v-if="showEdit"
      :show="showEdit"
      :question="question"
      :folder="folder"
      controlType="edit"
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

      <div class="dropdown question-card__dropdown">
        <button
          type="button"
          data-toggle="dropdown"
          aria-expanded="false"
          class="question-card__dropdown-button"
        >
          <i class="material-icons">more_vert</i>
        </button>
        <ul
          class="dropdown-menu dropdown-menu-right question-card__dropdown-list"
          aria-labelledby="moreOptionsDropdownButton"
        >
          <li>
            <CardActionButton
              class="dropdown-item question-card__action-button"
              data-cy="edit-question-button"
              icon="edit"
              @click="handleEditClick"
              >Edit</CardActionButton
            >
          </li>

          <li>
            <CardActionButton
              class="dropdown-item question-card__action-button"
              data-cy="present-question-button"
              icon="play_circle_outline"
              :to="`/chime/${folder.chime_id}/folder/${folder.id}/present/${orderedQuestionIndex}`"
              >Present</CardActionButton
            >
          </li>
          <li>
            <CardActionButton
              class="dropdown-item question-card__action-button"
              data-cy="delete-question-button"
              icon="clear"
              @click="handleDeleteClick"
              >Delete</CardActionButton
            >
          </li>
        </ul>
      </div>
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
import TextHeatmapResponseDisplay from "../../components/TextHeatmap/TextHeatmapResponseDisplay.vue";
import pluralize from "../../common/pluralize";

export default {
  components: {
    Card,
    CardActionButton,
    Chip,
    Toggle,
    QuestionForm,
    multiple_choice_display: MultipleChoiceDisplay,
    heatmap_response_display: HeatmapResponseDisplay,
    text_heatmap_response_display: TextHeatmapResponseDisplay,
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
  emits: ["change"],
  data: function () {
    return {
      showEdit: false,
    };
  },
  computed: {
    orderedQuestionIndex() {
      return this.question.order - 1;
    },
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
    pluralize,
    hasSpecializedQuestionDisplay,
    handleEditClick() {
      this.showEdit = true;
    },
    handleDeleteClick() {
      if (confirm("Are you sure you want to remove this question?")) {
        const chimeId = this.folder.chime_id;
        const folderId = this.folder.id;
        const questionId = this.question.id;

        const url = `/api/chime/${chimeId}/folder/${folderId}/question/${questionId}`;

        axios
          .delete(url)
          .then(() => this.$emit("change"))
          .catch((err) => console.error(err));
      }
    },
    handleQuestionEdited() {
      this.$emit("change");
      this.showEdit = false;
    },
    handleToggleOpenQuestion(event) {
      const shouldOpen = event.target.checked;
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
.question-card {
  line-height: 1.4;
}
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
.question-card__header {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  align-items: baseline;
  gap: 0.25rem;
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

.question-card .question-card__action-button {
  flex-direction: row;
  justify-content: left;
  gap: 0.5rem;
  padding: 0.25rem 1rem;
}
.question-card__dropdown-button {
  display: block;
  border: 0;
  background: transparent;
  padding: 0.8rem 0;
}

@media (min-width: 48rem) {
  .question-card__dropdown .dropdown-menu {
    display: flex;
    position: initial;
    z-index: initial;
    float: initial;
    min-width: initial;
    padding: 0;
    margin: 0;
    background-color: transparent;
    border: 0;
  }
  .question-card .question-card__action-button {
    flex-direction: column;
    gap: 0;
    padding: 0.75rem;
  }
  .question-card__dropdown-button {
    display: none;
  }
}
</style>
<style>
.question-card__choice-display.mult-choice-display {
  padding-left: 1.25rem;
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
