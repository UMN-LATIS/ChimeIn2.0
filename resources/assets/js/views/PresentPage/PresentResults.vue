<template>
  <div class="row">
    <div class="col">
      <h1 v-html="question.text"></h1>

      <template v-if="question.sessions.length > 0">
        <select v-model="selected" class="mb-3 form-control col-6">
          <option
            :value="question.value"
            v-for="question in question.sessions
              .map((el) => ({ value: el.id, text: el.created_at }))
              .concat({ value: 0, text: 'All' })"
            :key="question.id"
          >
            {{ question.text }}
          </option>
        </select>

        <component
          :is="question.question_info.question_type + '_statistics'"
          v-if="selected_session"
          :responses="selected_session.responses"
          :question="question"
          :chime-id="chimeId"
          @removeResponse="removeResponse($event)"
        >
        </component>
      </template>
      <template v-else>
        <p>No sessions yet</p>
      </template>
    </div>
  </div>
</template>

<script>
const sliderstatistics = () =>
  import(
    /* webpackChunkName: "multiplechoicestatistics" */
    "./SliderStatistics.vue"
  );

const multiplechoicestatistics = () =>
  import(
    /* webpackChunkName: "multiplechoicestatistics" */
    "./MultipleChoiceStatistics.vue"
  );
const FreeResponseStatistics = () =>
  import(
    /* webpackChunkName: "FreeResponseStatistics" */
    "./FreeResponseStatistics.vue"
  );
const TextHeatmapResponseStatistics = () =>
  import(
    /* webpackChunkName: "FreeResponseStatistics" */
    "./TextHeatmapResponseStatistics.vue"
  );
const ImageResponseStatistics = () =>
  import(
    /* webpackChunkName: "ImageResponseStatistics" */
    "./ImageResponseStatistics.vue"
  );
const HeatmapResponseStatistics = () =>
  import(
    /* webpackChunkName: "ImageResponseStatistics" */
    "./HeatmapResponseStatistics.vue"
  );

export default {
  components: {
    slider_response_statistics: sliderstatistics,
    multiple_choice_statistics: multiplechoicestatistics,
    image_response_statistics: ImageResponseStatistics,
    free_response_statistics: FreeResponseStatistics,
    text_heatmap_response_statistics: TextHeatmapResponseStatistics,
    no_response_statistics: FreeResponseStatistics,
    heatmap_response_statistics: HeatmapResponseStatistics,
  },
  props: ["sessions", "session", "question", "chimeId"],
  data: function () {
    return {
      selected: null,
    };
  },
  computed: {
    selected_session: function () {
      if (this.selected === 0) {
        var newSession = {};
        var responses = this.question.sessions.map((s) => s.responses);
        newSession.responses = Array.prototype.concat(...responses);
        return newSession;
      } else {
        return this.question.sessions.find((s) => s.id == this.selected);
      }
    },
  },
  watch: {
    question: function () {
      this.updateSelected();
    },
  },
  mounted() {
    this.updateSelected();
  },
  methods: {
    updateSelected() {
      this.selected = 0;
    },
    removeResponse(response) {
      const url =
        "/api/chime/" +
        this.chimeId +
        "/folder/" +
        this.question.folder_id +
        "/response/" +
        response.id;

      axios
        .delete(url)
        .then(() => {
          this.$emit("reload");
        })
        .catch((err) => {
          console.log(err.response);
        });
    },
  },
};
</script>
