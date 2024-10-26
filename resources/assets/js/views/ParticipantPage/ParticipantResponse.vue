<template>
  <article class="row responseContainer">
    <div v-if="response.session && response.session.question" class="col-12">
      <div role="heading" aria-level="3">
        <div class="questionText" v-html="response.session.question.text" />
      </div>
      <component
        :is="response.session.question.question_info.question_type"
        :question="response.session.question"
        :disabled="true"
        :response="response"
        :chime="chime"
      >
      </component>
      <RouterLink
        v-if="chime.students_can_view"
        class="pointer"
        :to="
          '/chime/' +
          chime.id +
          '/folder/' +
          response.session.question.folder_id +
          '/present/' +
          (response.session.question.order - 1)
        "
        >View Responses
      </RouterLink>

      <small v-if="chime.show_folder_title_to_participants" class="text-muted"
        ><strong>Folder</strong>: {{ response.session.question.folder.name }}
      </small>
      <hr />
    </div>
  </article>
</template>

<script lang="ts">
import MultipleChoice from "../../components/MultipleChoice/MultipleChoiceInputs.vue";
import ImageResponse from "../../components/ImageResponse/ImageResponseInputs.vue";
import FreeResponse from "../../components/FreeResponse/FreeResponseInputs.vue";
import TextHeatmapResponse from "../../components/TextHeatmap/TextHeatmapResponseInputs.vue";
import NoResponse from "../../components/NoResponse/NoResponseInputs.vue";
import SliderResponse from "../../components/SliderResponse/SliderResponseInputs.vue";
import ImageHeatmapResponse from "../../components/ImageHeatmapResponse/ImageHeatmapResponseInputs.vue";
import NumericResponse from "../../components/NumericResponse/NumericResponseInputs.vue";

export default {
  components: {
    multiple_choice: MultipleChoice,
    image_response: ImageResponse,
    text_heatmap_response: TextHeatmapResponse,
    no_response: NoResponse,
    free_response: FreeResponse,
    slider_response: SliderResponse,
    heatmap_response: ImageHeatmapResponse,
    numeric_response: NumericResponse,
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
