<template>
  <div class="present-prompt questionContent row">
    <div v-if="question" class="col">
      <!-- eslint-disable-next-line vue/no-v-html -->
      <h1 v-html="question.text"></h1>
      <component
        :is="`${questionType}_display`"
        v-if="hasSpecializedQuestionDisplay(questionType)"
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
import TextHeatmapResponseDisplay from "../../components/TextHeatmap/TextHeatmapResponseDisplay.vue";

export default {
  components: {
    multiple_choice_display: MultipleChoiceDisplay,
    heatmap_response_display: HeatmapResponseDisplay,
    text_heatmap_response_display: TextHeatmapResponseDisplay,
  },
  props: {
    question: { type: Object, required: true },
    session: { type: Object, required: true },
  },
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
<style scoped>
.present-prompt .text-heatmap-response-display {
  font-size: 1.2rem;
}
</style>
