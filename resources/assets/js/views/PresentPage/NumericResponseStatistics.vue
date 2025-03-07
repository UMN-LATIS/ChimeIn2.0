<template>
  <div>
    <BarChart
      v-if="chartType === 'bar'"
      :data="barChartData"
      :itemLabel="question.anonymous ? 'Response ID' : 'User'"
      :xAxisLabel="questionOptions.x_axis_label"
    />
    <ScatterPlot
      v-else-if="chartType === 'scatter'"
      :data="scatterPlotData"
      :xAxisLabel="questionOptions.x_axis_label"
      :yAxisLabel="questionOptions.y_axis_label"
    />
    <RangeChart
      v-else-if="chartType === 'range'"
      :data="rangeChartData"
      :xAxisLabel="questionOptions.x_axis_label"
    />
  </div>
</template>
<script setup lang="ts">
import BarChart from "@/components/NumericResponse/BarChart.vue";
import ScatterPlot from "@/components/NumericResponse/ScatterPlot.vue";
import RangeChart from "@/components/NumericResponse/RangeChart.vue";
import * as T from "@/types";
import { computed } from "vue";
import { normalizeNumericQuestionOptions } from "@/helpers/getNormedNumericQuestionOptions";

const props = defineProps<{
  responses: T.Response<T.NumericResponseResponseInfo>[];
  question: T.Question<T.NumericResponseQuestionInfo>;
  userLookup: Map<number, T.User>;
}>();

const questionOptions = computed((): T.NormalizedNumericQuestionOptions => {
  return normalizeNumericQuestionOptions(
    props.question.question_info.question_responses
  );
});

const chartType = computed(() => questionOptions.value.chart_type);

const barChartData = computed((): [label: string, value: number][] => {
  return props.responses.map((response, index) => {
    // is this question anonymous? If so, then just label
    // with the response id, otherwise use name
    const user = props.userLookup.get(response.user_id);
    const label = props.question.anonymous
      ? `Response ${index + 1}`
      : user?.name ?? "Unknown";

    return [label, response.response_info.x ?? 0];
  });
});

const scatterPlotData = computed((): [x: number, y: number][] => {
  return props.responses.map((response) => [
    response.response_info.x ?? 0,
    response.response_info.y ?? 0,
  ]);
});

const rangeChartData = computed((): [minX: number, maxX: number][] => {
  return props.responses.map((response) => {
    const a = response.response_info.xRange?.[0] ?? 0;
    const b = response.response_info.xRange?.[1] ?? 0;
    const min = Math.min(a, b);
    const max = Math.max(a, b);
    return [min, max];
  });
});
</script>
<style scoped></style>
