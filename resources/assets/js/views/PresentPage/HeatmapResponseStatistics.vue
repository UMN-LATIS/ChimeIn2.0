<template>
  <div class="col-sm-12">
    <div class="overlay-container">
      <canvas id="simpleheat" ref="targetCanvas"></canvas>
      <ul class="pin-container">
        <li
          v-for="response in responses"
          :key="response.id"
          :style="getPinDropStyle(response)"
          class="pin"
        >
          <span class="sr-only">
            {{ response.response_info.image_coordinates.coordinate_x }},
            {{ response.response_info.image_coordinates.coordinate_y }}
          </span>
          <svg
            xmlns="http://www.w3.org/2000/svg"
            width="32"
            height="32"
            viewBox="0 0 32 32"
            class="pin-icon"
          >
            <path
              fill="currentColor"
              stroke="white"
              stroke-width="1"
              d="M16 2A11.013 11.013 0 0 0 5 13a10.889 10.889 0 0 0 2.216 6.6s.3.395.349.452L16 30l8.439-9.953c.044-.053.345-.447.345-.447l.001-.003A10.885 10.885 0 0 0 27 13A11.013 11.013 0 0 0 16 2Zm0 15a4 4 0 1 1 4-4a4.005 4.005 0 0 1-4 4Z"
            />
            <circle cx="16" cy="13" r="4" fill="white" />
          </svg>
        </li>
      </ul>

      <img
        ref="targetImage"
        data-cy="image-heatmap-original"
        class="img-fluid heatmap-target-image"
        :src="'/storage/' + question.question_info.question_responses.image"
        :alt="
          question.question_info.question_responses.image_alt ||
          question.question_info.question_responses.image_name
        "
        @load="renderHeatMap"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { StyleValue, computed, onMounted, onUnmounted, ref, watch } from "vue";
import simpleheat from "simpleheat";
import { ImageHeatmapQuestion, ImageHeatmapResponse } from "@/types";
import { useResizeObserver, useElementSize } from "@vueuse/core";

interface Props {
  responses: ImageHeatmapResponse[];
  question: ImageHeatmapQuestion;
}

const props = defineProps<Props>();
const targetImage = ref<HTMLImageElement | null>(null);
const targetCanvas = ref<HTMLCanvasElement | null>(null);

interface Point {
  x: number;
  y: number;
}

const { width: imageWidth, height: imageHeight } = useElementSize(targetImage);

const scaleX = computed(() => {
  if (!targetImage.value) {
    return 1;
  }
  return imageWidth.value / targetImage.value.naturalWidth;
});
const scaleY = computed(() => {
  if (!targetImage.value) {
    return 1;
  }
  return imageHeight.value / targetImage.value.naturalHeight;
});

function getScaledImageCoords(p: Point): Point {
  return {
    x: p.x * scaleX.value,
    y: p.y * scaleY.value,
  };
}

function getPinDropStyle(response: ImageHeatmapResponse): StyleValue {
  const coords = getScaledImageCoords({
    x: response.response_info.image_coordinates.coordinate_x,
    y: response.response_info.image_coordinates.coordinate_y,
  });
  return {
    position: "absolute",
    top: coords.y + "px",
    left: coords.x + "px",
  };
}

function renderHeatMap() {
  if (!targetCanvas.value || !targetImage.value) {
    return;
  }

  targetCanvas.value.width = targetImage.value.clientWidth;
  targetCanvas.value.height = targetImage.value.clientHeight;

  const data = props.responses.map((r) => {
    return [
      r.response_info.image_coordinates.coordinate_x * scaleX.value,
      r.response_info.image_coordinates.coordinate_y * scaleY.value,
      0.1,
    ];
  });
  simpleheat("simpleheat")
    .data(data)
    .radius(
      50 * Math.min(scaleX.value, scaleY.value),
      20 * Math.min(scaleX.value, scaleY.value)
    )
    .draw();
}

onMounted(() => window.addEventListener("resize", renderHeatMap));
onUnmounted(() => window.removeEventListener("resize", renderHeatMap));

useResizeObserver(targetImage, renderHeatMap);

watch(() => props.responses, renderHeatMap);
</script>

<style scoped>
.heatmap-target-image {
  display: block;
  max-width: 100%;
  max-height: 70vh;
}

.overlay-container {
  position: relative;
}
canvas {
  position: absolute;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
}

.pin-container {
  position: absolute;
  inset: 0;
  padding: 0;
  margin: 0;
  list-style-type: none;
}

.pin {
  padding: 0;
  margin: 0;
  border-radius: 50%;
}

.pin-icon {
  position: absolute;
  bottom: 0;
  left: 50%;
  transform: translate(-50%, 0);
  color: #007bff;
  width: 1.25rem;
  height: 1.25rem;
}
</style>
