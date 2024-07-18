<template>
  <div class="">
    <div v-if="chartType === 'bar'" class="chart-container">
      <BarChart
        :data="barChartData"
        :itemLabel="question.anonymous ? 'Response ID' : 'User'"
        :valueLabel="questionOptions.x_axis_label"
      />
    </div>
    <div v-else-if="chartType === 'scatter'">Scatter Chart</div>
  </div>
</template>
<script setup lang="ts">
import BarChart from "@/components/NumericResponse/BarChart.vue";
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
</script>
<style scoped></style>
