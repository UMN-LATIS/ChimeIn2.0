<template>
  <div>
    <input v-model="bucketSize" type="number" placeholder="Bucket Size" />
    <GChart
      type="Histogram"
      :resizeDebounce="100"
      :data="chartData"
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
  data: number[];
  label: string;
}>();

// 0 = auto
const bucketSize = ref<number>(10);

const chartData = computed(() => {
  const arr: [string | number, string | number][] = [[props.label, "X"]];
  props.data.forEach((value) => {
    arr.push([String(value), value]);
  });
  return arr;
});

const options: GoogleChartOptions = {
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
  },
  tooltip: {
    isHtml: false,
  },
};
</script>
<style scoped></style>
