<template>
  <div>
    <GChart
      type="Histogram"
      :resizeDebounce="100"
      :data="gChartData"
      :options="options"
      class="googleChart"
    />
  </div>
</template>
<script setup lang="ts">
import { computed } from "vue";
import { GChart } from "vue-google-charts";
import { GoogleChartOptions } from "vue-google-charts/dist/types";

const props = defineProps<{
  data: [label: string, value: number][];
  itemLabel: string;
  xAxisLabel: string;
}>();

/**
 * Example data: first row is labels
 * [["User", "Cups of Coffee per day"],
 *  ["Alice", 0],
 *  ["Bob", 2],
 *  ["Charlie", 2],
 *  ...
 * ]
 */
const gChartData = computed(() => {
  return [[props.itemLabel, props.xAxisLabel], ...props.data];
});

const options = computed(
  (): GoogleChartOptions => ({
    // height: "100%",
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
    vAxis: {
      baseline: 0,
      title: "Responses",
    },
    hAxis: {
      title: props.xAxisLabel,
    },
    tooltip: {
      isHtml: false,
    },
  })
);
</script>
<style scoped>
.googleChart {
  min-height: 600px;
}
</style>
