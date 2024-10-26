<template>
  <div class="image-heatmap-response">
    <div class="image-heatmap-response__image-container">
      <div
        v-if="image_coordinates && targetImageLoaded"
        data-cy="image-heatmap-click-spot"
        class="clickPointer"
        :style="{
          top: image_coordinates.coordinate_y + 'px',
          left: image_coordinates.coordinate_x + 'px',
        }"
      ></div>
      <img
        ref="targetImage"
        data-cy="image-heatmap-target"
        class="img-fluid max-height-image"
        :src="'/storage/' + question.question_info.question_responses.image"
        @click="triggerResponse"
        @load="handleTargetImageLoaded"
      />
    </div>
    <div>
      <button
        v-if="
          !disabled &&
          response?.id &&
          !create_new_response &&
          question.allow_multiple
        "
        class="btn btn-outline-primary"
        variant="primary"
        @click="new_response"
      >
        Clear and Select Another Point
      </button>
    </div>
  </div>
</template>

<script lang="ts">
import { PropType } from "vue";
import * as T from "@/types";

interface ImageCoordinates {
  coordinate_x: number;
  coordinate_y: number;
}

export default {
  props: {
    question: {
      type: Object as PropType<T.Question<T.ImageHeatmapQuestionInfo>>,
      required: true,
    },
    response: {
      type: Object as PropType<T.Response<T.ImageHeatmapResponseResponseInfo> | null>,
      required: false,
      default: null,
    },
    disabled: {
      type: Boolean,
      required: false,
    },
  },
  emits: ["recordresponse"],
  data() {
    return {
      targetImageLoaded: false,
      image_coordinates: null as ImageCoordinates | null,
      create_new_response: false,
    };
  },
  watch: {
    response() {
      this.updateScaledCoordinates();
    },
  },
  created() {
    window.addEventListener("resize", this.updateScaledCoordinates);
  },
  unmounted() {
    window.removeEventListener("resize", this.updateScaledCoordinates);
  },
  methods: {
    handleTargetImageLoaded() {
      this.targetImageLoaded = true;
      this.updateScaledCoordinates();
    },
    updateScaledCoordinates() {
      if (!this.response?.response_info) {
        return;
      }

      const targetImage = this.$refs["targetImage"] as HTMLImageElement;
      const imageCoordinates = this.response.response_info.image_coordinates;

      if (!imageCoordinates || !targetImage) {
        return;
      }

      var cw = targetImage.clientWidth;
      var ch = targetImage.clientHeight;
      var iw = targetImage.naturalWidth;
      var ih = targetImage.naturalHeight;
      var px =
        (this.response.response_info.image_coordinates.coordinate_x * cw) / iw;
      var py =
        (this.response.response_info.image_coordinates.coordinate_y * ch) / ih;
      this.image_coordinates = {
        coordinate_x: px,
        coordinate_y: py,
      };
    },
    new_response: function () {
      this.create_new_response = true;
      this.image_coordinates = null;
    },
    triggerResponse: function (event) {
      var bounds = event.target.getBoundingClientRect();
      var left = bounds.left;
      var top = bounds.top;
      var x = event.clientX - left;
      var y = event.clientY - top;
      var cw = event.target.clientWidth;
      var ch = event.target.clientHeight;
      var iw = event.target.naturalWidth;
      var ih = event.target.naturalHeight;
      var px = (x / cw) * iw;
      var py = (y / ch) * ih;

      var image_coordinates = {
        coordinate_x: px,
        coordinate_y: py,
      };
      // this.image_coordinates = image_coordinates;
      const response = {
        question_type: "heatmap_response",
        image_coordinates: image_coordinates,
      };
      this.$emit("recordresponse", response, this.create_new_response);
      this.create_new_response = false;
    },
  },
};
</script>

<style scoped>
.max-height-image {
  max-height: 70vh;
}

.image-heatmap-response__image-container {
  position: relative;
}

.clickPointer {
  transform: translate(-50%, -50%);
  padding: 1em;
  border: solid 0.5em #c00;
  border-radius: 50%;
  position: absolute;
  pointer-events: none;
}
</style>
