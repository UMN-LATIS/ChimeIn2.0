<template>
  <div class="questionContent row">
    <div v-if="question" class="col">
      <h1 v-html="question.text"></h1>
      <component
        v-if="hasSpecializedQuestionDisplay(questionType)"
        :is="`${questionType}_display`"
        :question="question"
      />
    </div>
    <div v-else>
      <span>
        <h4>There aren't any questions in this folder</h4>
      </span>
    </div>
  </div>
</template>

<script>
import MultipleChoiceDisplay from "../../components/MultipleChoice/MultipleChoiceDisplay.vue";
import HeatmapResponseDisplay from "../../components/ImageHeatmapResponse/ImageHeatmapResponseDisplay.vue";
import hasSpecializedQuestionDisplay from "../../helpers/hasSpecializedQuestionDisplay";

export default {
  components: {
    multiple_choice_display: MultipleChoiceDisplay,
    heatmap_response_display: HeatmapResponseDisplay,
  },
  props: ["question", "session"],
  computed: {
    questionType() {
      return this.question.question_info.question_type;
    },
  },
  methods: {
    hasSpecializedQuestionDisplay,
  },
};
</script>
