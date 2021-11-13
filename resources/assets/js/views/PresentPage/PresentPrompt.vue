<template>
  <div class="questionContent row">
    <div v-if="question" class="col">
      <h1 v-html="question.text"></h1>
      <component
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
import DisplaySlider from "../../components/SliderResponse/SliderResponseDisplay.vue";
import DisplayTextHeatmapResponse from "../../components/TextHeatmap/TextHeatmapResponseDisplay.vue";
import DisplayFreeResponse from "../../components/FreeResponse/FreeResponseDisplay.vue";
import DisplayImageResponse from "../../components/ImageResponse/ImageResponseDisplay.vue";
import DisplayHeatmapResponse from "../../components/ImageHeatmapResponse/ImageHeatmapResponseDisplay.vue";

export default {
  components: {
    multiple_choice_display: DisplayMultipleChoice,
    slider_response_display: DisplaySlider,
    text_heatmap_response_display: DisplayTextHeatmapResponse,
    free_response_display: DisplayFreeResponse,
    no_response_display: DisplayFreeResponse,
    image_response_display: DisplayImageResponse,
    heatmap_response_display: DisplayHeatmapResponse,
  },
  props: ["question", "session"],
};
</script>
