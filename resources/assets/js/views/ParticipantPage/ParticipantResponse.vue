<template>
  <div class="row responseContainer">
    <div v-if="response.session && response.session.question" class="col-12">
      <p class="questionText" v-html="response.session.question.text"></p>
      <component
        :is="response.session.question.question_info.question_type"
        :question="response.session.question"
        :disabled="true"
        :response="response"
        :chime="chime"
      >
      </component>
      <a
        v-if="chime.students_can_view"
        class="pointer"
        :href="
          '/chime/' +
          chime.id +
          '/folder/' +
          response.session.question.folder_id +
          '/present/' +
          (response.session.question.order - 1)
        "
        >View Responses</a
      >

      <small v-if="chime.show_folder_title_to_participants" class="text-muted"
        ><strong>Folder</strong>: {{ response.session.question.folder.name }}
      </small>
      <hr />
    </div>
  </div>
</template>

<script>
import MultipleChoice from "../../components/MultipleChoice/MultipleChoiceInputs.vue";
import ImageResponse from "../../components/ImageResponse/ImageResponseInputs.vue";
import FreeResponse from "../../components/FreeResponse/FreeResponseInputs.vue";
import TextHeatmapResponse from "../../components/TextHeatmap/TextHeatmapResponseInputs.vue";
import NoResponse from "../../components/NoResponse/NoResponseInputs.vue";
import SliderResponse from "../../components/SliderResponse/SliderResponseInputs.vue";
import ImageHeatmapResponse from "../../components/ImageHeatmapResponse/ImageHeatmapResponseInputs.vue";

export default {
  components: {
    multiple_choice: MultipleChoice,
    image_response: ImageResponse,
    text_heatmap_response: TextHeatmapResponse,
    no_response: NoResponse,
    free_response: FreeResponse,
    slider_response: SliderResponse,
    heatmap_response: ImageHeatmapResponse,
  },
  props: {
    response: {
      type: Object,
      required: true,
    },
    chime: {
      type: Object,
      required: true,
    },
  },
};
</script>
