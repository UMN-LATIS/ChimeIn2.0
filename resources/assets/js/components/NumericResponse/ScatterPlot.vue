<template>
  <div>
    <div class="chart-controls">
      <label>Trendline</label>
      <select v-model="trendlineType" class="form-control">
        <option value="none">None</option>
        <option value="linear">Linear</option>
        <option value="exponential">Exponential</option>
        <option value="quadratic">Quadratic</option>
        <option value="cubic">Cubic</option>
      </select>
    </div>
    <GChart
      type="ScatterChart"
      :resizeDebounce="100"
      :data="[[xAxisLabel, yAxisLabel], ...data]"
      :options="options"
      class="googleChart"
    />
  </div>
</template>
<script setup lang="ts">
import { computed, ref } from "vue";
import { GChart } from "vue-google-charts";
import { GoogleChartOptions } from "vue-google-charts/dist/types";

const props = defineProps<{
  data: [x: number, y: number][];
  xAxisLabel: string;
  yAxisLabel: string;
}>();

type TrendlineType = "none" | "linear" | "quadratic" | "cubic" | "exponential";
const trendlineType = ref<TrendlineType>("none");

const options = computed(
  (): GoogleChartOptions => ({
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
      bottom: 30,
      width: "100%",
    },
    vAxis: {
      baseline: 0,
      title: props.yAxisLabel,
    },
    hAxis: {
      title: props.xAxisLabel,
    },
    tooltip: {
      isHtml: false,
    },
    trendlines: {
      0: {
        type: "linear",
        color: "green",
        lineWidth: 3,
        opacity: 0.3,
        showR2: true,
        visibleInLegend: true,
      },
    },
  })
);
</script>
<style scoped>
.googleChart {
  min-height: 50vh;
}

.chart-controls {
  display: flex;
  justify-content: flex-end;
  gap: 0.5rem;
  align-items: baseline;
  /* hack to reduce whitespace and make appear aligned with session dropdown */
  margin-top: -3.4rem;
}
.chart-controls select {
  width: auto;
}
</style>
