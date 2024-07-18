<template>
  <div>
    <GChart
      type="Histogram"
      :resizeDebounce="100"
      :data="[[itemLabel, valueLabel], ...data]"
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
  valueLabel: string;
}>();

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
      title: props.valueLabel,
    },
    tooltip: {
      isHtml: false,
    },
  })
);
</script>
<style scoped></style>
