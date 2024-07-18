<template>
  <div class="">
    <BarChart
      v-if="chartType === 'bar'"
      :data="barChartData"
      :itemLabel="question.anonymous ? 'Response ID' : 'User'"
      :xAxisLabel="questionOptions.x_axis_label"
    />
    <ScatterPlot
      v-else-if="chartType === 'scatter'"
      :data="scatterPlotData"
      :xAxisLabel="questionOptions.x_axis_label || 'X'"
      :yAxisLabel="questionOptions.y_axis_label || 'Y'"
    />
  </div>
</template>
<script setup lang="ts">
import BarChart from "@/components/NumericResponse/BarChart.vue";
import ScatterPlot from "@/components/NumericResponse/ScatterPlot.vue";
import type {
  NumericResponseResponseInfo,
  NumericResponseQuestionInfo,
  Question,
  Response,
} from "@/types";
import { computed } from "vue";

const props = defineProps<{
  responses: Response<NumericResponseResponseInfo>[];
  question: Question<NumericResponseQuestionInfo>;
}>();

const chartType = computed(
  () => props.question.question_info.question_responses.chart_type
);

const questionOptions = computed(() => {
  return props.question.question_info.question_responses;
});

const barChartData = computed((): [label: string, value: number][] => {
  return props.responses.map((response, index) => {
    // is this question anonymous? If so, then just label
    // with the response id, otherwise use username
    const label = props.question.anonymous
      ? `Response ${index + 1}`
      : response.user.name;

    return [label, response.response_info.x];
  });
});

const scatterPlotData = computed((): [x: number, y: number][] => {
  return props.responses.map((response) => [
    response.response_info.x,
    response.response_info.y,
  ]);
});
</script>
<style scoped></style>
