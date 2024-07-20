<template>
  <div class="">
    {{ data }}

    <GChart
      v-if="data.length"
      type="BarChart"
      :data="chartData"
      :options="chartOptions"
      class="googleChart"
    />
    <p
      v-else
      style="
        display: grid;
        min-height: 10em;
        place-content: center;
        background: #fafafa;
        border-radius: 0.5rem;
      "
    >
      No data
    </p>
  </div>
</template>
<script setup lang="ts">
import { GChart } from "vue-google-charts";
import { computed } from "vue";

const props = defineProps<{
  data: [minX: number, maxX: number][];
  xAxisLabel: string;
}>();

const chartData = computed(() => {
  const header = [
    "Label",
    "Min",
    { role: "style" },
    { role: "tooltip" },
    "Max",
    { role: "style" },
    { role: "tooltip" },
  ];

  /**
   * The magic behind the range chart is that
   * we're drawing stacked bars, where one is transparent
   * and the other is the diff between the min and max.
   *
   * We need to handle three cases:
   * 1. 0 < minX < maxX - draw a transparent bar to minX,
   *    then draw the diff normally.
   * 2. minX < 0 < maxX - both bars normally – no transparent bar needed.
   * 3. minX < maxX < 0 - draw a transparent bar to maxX,
   *   then draw the (negative) diff normally.
   *
   * We also need to customize the tooltips to show the
   * proper min and max values, and not the diff.
   */
  return [
    header,
    ...props.data.map(([minX, maxX], index) => {
      const diff = maxX - minX;

      // Case 1: 0 < minX < maxX
      if (minX >= 0) {
        return [
          index.toString(),
          minX,
          "opacity: 0",
          `min: ${minX}`,
          diff,
          "opacity: 1",
          `max: ${maxX}`,
        ];
      }

      // Case 2: minX < 0 < maxX
      if (minX < 0 && maxX >= 0) {
        return [
          index.toString(),
          minX,
          "opacity: 1",
          `min: ${minX}`,
          maxX,
          "opacity: 1",
          `max: ${maxX}`,
        ];
      }

      // Case 3: minX < maxX < 0
      if (maxX < 0) {
        // we need to swap the draw order so that
        // we FIRST draw the transparent bar to the max
        // and THEN draw the diff
        return [
          index.toString(),
          maxX,
          "opacity: 0",
          `max: ${maxX}`,
          // diff needs to be negated so that it's drawn
          // in the negative direction
          -1 * diff,
          "opacity: 1",
          `min: ${minX}`,
        ];
      }
    }),
  ];
});

const chartOptions = computed(() => {
  return {
    isStacked: true,
    hAxis: {
      title: props.xAxisLabel,
    },
    vAxis: {
      title: "Responses",
    },
    animation: {
      duration: 1000,
      easing: "out",
      startup: true,
    },
    legend: {
      position: "none",
    },
    chartArea: {
      top: 30,
      left: 50,
      width: "100%",
    },
    tooltip: {
      isHtml: false,
    },
    bar: { groupWidth: "95%" },
    series: {
      0: { color: "#36C" },
      1: { color: "#36C" },
    },
  };
});
</script>
<style scoped>
.googleChart {
  min-height: 600px;
}
</style>
