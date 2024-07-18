<template>
  <section class="d-flex flex-column gap-2 mt-3">
    <fieldset class="type-select">
      <h2>Chart Type</h2>
      <label for="bar-chart">
        <input
          id="bar-chart"
          type="radio"
          class="sr-only"
          name="chart-type"
          value="bar"
          :checked="question_responses.chart_type === 'bar'"
          @change="
            $emit('update:question_responses', {
              ...question_responses,
              chart_type: 'bar',
            })
          "
        />

        <IconChartBar />
        Bar Chart
      </label>

      <label for="scatter-chart">
        <input
          id="scatter-chart"
          type="radio"
          name="chart-type"
          value="scatter"
          class="sr-only"
          :checked="question_responses.chart_type === 'scatter'"
          @change="
            $emit('update:question_responses', {
              ...question_responses,
              chart_type: 'scatter',
            })
          "
        />
        <IconChartScatter />
        Scatter Plot
      </label>
    </fieldset>

    <div class="row">
      <label for="x-axis-label" class="col-form-label col-sm-3">
        X Axis Label
      </label>
      <div class="col-sm-9">
        <input
          type="text"
          class="form-control"
          :value="question_responses.x_axis_label"
          required
          @input="
            (event) =>
              $emit('update:question_responses', {
                ...question_responses,
                x_axis_label: (event.target as HTMLInputElement).value,
              })
          "
        />
      </div>
    </div>

    <div v-if="question_responses.chart_type === 'scatter'" class="row">
      <label for="x-axis-label" class="col-form-label col-sm-3">
        Y Axis Label
      </label>
      <div class="col-sm-9">
        <input
          type="text"
          class="form-control"
          :value="question_responses.y_axis_label"
          :required="question_responses.chart_type === 'scatter'"
          @input="
            (event) =>
              $emit('update:question_responses', {
                ...question_responses,
                y_axis_label: (event.target as HTMLInputElement).value,
              })
          "
        />
      </div>
    </div>
  </section>
</template>
<script setup lang="ts">
import IconChartBar from "@/icons/IconChartBar.vue";
import IconChartScatter from "@/icons/IconChartScatter.vue";
import { NumericResponseQuestionInfo } from "@/types";

withDefaults(
  defineProps<{
    // eslint-disable-next-line vue/prop-name-casing
    question_responses: NumericResponseQuestionInfo["question_responses"];
  }>(),
  {
    question_responses: () => ({
      chart: "bar",
      x_axis_label: "",
      y_axis_label: "",
    }),
  }
);

defineEmits<{
  (
    event: "update:question_responses",
    value: NumericResponseQuestionInfo["question_responses"]
  ): void;
}>();
</script>
<style scoped>
.type-select {
  display: flex;
  align-items: baseline;
  gap: 0.5rem;
}

.type-select h2 {
  margin: 0;
  font-size: 1rem;
  width: 25%;
}

.type-select label {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  border: 1px solid #ced4da;
  padding: 0.5rem;
  border-radius: 4px;
}

.type-select label:has(input:checked) {
  background-color: #333;
  border-color: #333;
  color: white;
}
</style>
