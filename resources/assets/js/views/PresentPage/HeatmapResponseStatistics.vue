<template>
  <div class="col-sm-12">
    <div class="overlay-container">
      <canvas id="simpleheat" ref="targetCanvas"></canvas>
      <img
        ref="targetImage"
        data-cy="image-heatmap-original"
        class="img-fluid heatmap-target-image"
        :src="'/storage/' + question.question_info.question_responses.image"
        :alt="question.question_info.question_responses.image_alt"
        @load="renderHeatMap"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted, onUnmounted, ref, watch } from "vue";
import simpleheat from "simpleheat";
import { Question, Response } from "../../types";

interface Props {
  responses: Response[];
  question: Question;
}

const props = defineProps<Props>();
const targetImage = ref<HTMLImageElement | null>(null);
const targetCanvas = ref<HTMLCanvasElement | null>(null);

function renderHeatMap() {
  if (!targetCanvas.value || !targetImage.value) {
    return;
  }

  targetCanvas.value.width = targetImage.value.clientWidth;
  targetCanvas.value.height = targetImage.value.clientHeight;
  const scaleFactorX =
    targetImage.value.clientWidth / targetImage.value.naturalWidth;
  const scaleFactorY =
    targetImage.value.clientHeight / targetImage.value.naturalHeight;

  const data = props.responses.map((r) => {
    return [
      r.response_info.image_coordinates.coordinate_x * scaleFactorX,
      r.response_info.image_coordinates.coordinate_y * scaleFactorY,
      0.1,
    ];
  });
  simpleheat("simpleheat")
    .data(data)
    .radius(
      50 * Math.min(scaleFactorX, scaleFactorY),
      20 * Math.min(scaleFactorX, scaleFactorY)
    )
    .draw();
}

onMounted(() => window.addEventListener("resize", renderHeatMap));
onUnmounted(() => window.removeEventListener("resize", renderHeatMap));

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
</style>
