<template>
  <div class="questionContent row">
    <div v-if="question" class="col">
      <h1 v-html="question.text"></h1>
      <component
        v-if="hasSpecializedQuestionDisplay()"
        :is="question.question_info.question_type + '_display'"
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
import DisplayMultipleChoice from "../../components/MultipleChoice/MultipleChoiceDisplay.vue";
import DisplayHeatmapResponse from "../../components/ImageHeatmapResponse/ImageHeatmapResponseDisplay.vue";

export default {
  components: {
    multiple_choice_display: DisplayMultipleChoice,
    heatmap_response_display: DisplayHeatmapResponse,
  },
  props: ["question", "session"],
  methods: {
    hasSpecializedQuestionDisplay() {
      const questionType = this.question.question_info.question_type;
      return ["multiple_choice", "heatmap_response"].includes(questionType);
    },
  },
};
</script>
