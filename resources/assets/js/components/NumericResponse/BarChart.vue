<template>
  <div>
    <div class="chart-controls">
      <div>
        <label class="form-label"> Bucket Size </label>
        <input
          v-model="bucketSize"
          class="form-control"
          type="number"
          placeholder="auto"
        />
      </div>
    </div>

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
import { computed, ref } from "vue";
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

// 0 = auto
const bucketSize = ref<number | "">("");

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
    histogram: {
      // '' or 0 = auto
      bucketSize: !bucketSize.value ? undefined : bucketSize.value,
    },
  })
);
</script>
<style scoped>
.googleChart {
  min-height: 600px;
}

.chart-controls {
  display: flex;
  align-items: baseline;
  justify-content: flex-end;
  gap: 0.5rem;
  margin-top: -4.75rem;
  margin-bottom: 0.25rem;
}
.chart-controls label {
  margin: 0;
  font-size: 0.825rem;
}
.chart-controls input {
  width: 8rem;
}
</style>
