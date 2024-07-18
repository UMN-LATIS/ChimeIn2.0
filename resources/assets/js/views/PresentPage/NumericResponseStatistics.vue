<template>
  <div class="">
    <div v-if="chartType === 'bar'" class="chart-container">
      <BarChart :data="barChartData" :label="questionOptions.x_axis_label" />
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

const responseInfo = computed(() => {
  return props.responses.map((response) => response.response_info);
});

const questionOptions = computed(() => {
  return props.question.question_info.question_responses;
});

const barChartData = computed(() => {
  return responseInfo.value.map((info) => info.x);
});
</script>
<style scoped></style>
