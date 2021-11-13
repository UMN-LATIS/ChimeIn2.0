<template>
  <div>
    <div class="col-sm-12">
      <div
        v-if="image_coordinates"
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
        @load="updateScaledCoordinates"
      />
    </div>
    <div class="col-sm-12">
      <button
        v-if="
          !disabled &&
          response.id &&
          !create_new_response &&
          question.allow_multiple
        "
        class="btn btn-primary"
        variant="primary"
        @click="new_response"
      >
        Clear and Select Another Point
      </button>
    </div>
  </div>
</template>

<style scoped>
.max-height-image {
  max-height: 70vh;
}

.clickPointer {
  margin-top: -1.5em;
  margin-left: -0.5em;
  padding: 1em;
  border: solid 0.5em #c00;
  border-radius: 50%;
  position: absolute;
  pointer-events: none;
}
</style>

<script>
export default {
  props: ["question", "response", "disabled"],
  data() {
    return {
      image_coordinates: null,
      create_new_response: false,
    };
  },
  watch: {
    response: function (value) {
      this.updateScaledCoordinates();
    },
  },
  created() {
    window.addEventListener("resize", this.updateScaledCoordinates);
  },
  destroyed() {
    window.removeEventListener("resize", this.updateScaledCoordinates);
  },
  mounted() {
    if (
      this.response &&
      this.response.hasOwnProperty("response_info") &&
      this.response.response_info.hasOwnProperty("image_coordinates")
    ) {
      this.updateScaledCoordinates();
    }
  },
  methods: {
    updateScaledCoordinates: function () {
      if (
        !this.response ||
        !this.response.response_info ||
        !this.response.response_info.image_coordinates
      ) {
        return;
      }

      var targetImage = this.$refs["targetImage"];
      var boundingRect = targetImage.getBoundingClientRect();

      var left = boundingRect.left;
      var top = boundingRect.top;
      // var x = targetImage.clientX - left;
      // var y = targetImage.clientY - top;
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
    // record_response: function() {
    //     const response = {
    //         question_type: 'heatmap_response',
    //         image_coordinates: this.image_coordinates
    //     }

    //     this.$emit('recordresponse', response, false);

    // }
  },
};
</script>
