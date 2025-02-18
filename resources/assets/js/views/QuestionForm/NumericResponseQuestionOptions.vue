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
          :checked="normedQuestionOptions.chart_type === 'bar'"
          @change="
            $emit('update:question_responses', {
              ...normedQuestionOptions,
              chart_type: 'bar',
            })
          "
        />

        <IconChartBar />
        Histogram
      </label>
      <label for="scatter-chart">
        <input
          id="scatter-chart"
          type="radio"
          name="chart-type"
          value="scatter"
          class="sr-only"
          :checked="normedQuestionOptions.chart_type === 'scatter'"
          @change="
            $emit('update:question_responses', {
              ...normedQuestionOptions,
              chart_type: 'scatter',
            })
          "
        />
        <IconChartScatter />
        Scatter
      </label>

      <label for="range-chart">
        <input
          id="range-chart"
          type="radio"
          name="chart-type"
          value="range"
          class="sr-only"
          :checked="normedQuestionOptions.chart_type === 'range'"
          @change="
            $emit('update:question_responses', {
              ...normedQuestionOptions,
              chart_type: 'range',
            })
          "
        />
        <IconChartRange />
        Range
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
          :value="normedQuestionOptions.x_axis_label"
          required
          @input="
            (event) =>
              $emit('update:question_responses', {
                ...normedQuestionOptions,
                x_axis_label: (event.target as HTMLInputElement).value,
              })
          "
        />
      </div>
    </div>

    <div v-if="normedQuestionOptions.chart_type === 'scatter'" class="row">
      <label for="x-axis-label" class="col-form-label col-sm-3">
        Y Axis Label
      </label>
      <div class="col-sm-9">
        <input
          type="text"
          class="form-control"
          :value="normedQuestionOptions.y_axis_label"
          :required="normedQuestionOptions.chart_type === 'scatter'"
          @input="
            (event) =>
              $emit('update:question_responses', {
                ...normedQuestionOptions,
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
import IconChartRange from "@/icons/IconChartRange.vue";
import { NumericResponseQuestionInfo } from "@/types";
import { normalizeNumericQuestionOptions } from "@/helpers/getNormedNumericQuestionOptions";
import { computed, onMounted } from "vue";

const props = defineProps<{
  /**
   * It's possible – likely even – that the passed question_responses prop will
   * be a value like `[]` (the default set by a Multiple Choice) or some
   * other value that doesn't match the NumericResponseQuestionInfo type.
   *
   * What this means is that we need to check that the question_responses prop
   * is an object with the properties we expect.
   */
  // eslint-disable-next-line vue/prop-name-casing
  question_responses: NumericResponseQuestionInfo["question_responses"];
}>();

const emit = defineEmits<{
  (
    event: "update:question_responses",
    value: NumericResponseQuestionInfo["question_responses"]
  ): void;
}>();

const normedQuestionOptions = computed(() =>
  normalizeNumericQuestionOptions(props.question_responses)
);

onMounted(() => {
  // initialize the question options in case they're set to `[]`
  // or some other value that doesn't match the expected type
  emit("update:question_responses", normedQuestionOptions.value);
});
</script>
<style scoped>
.type-select {
  display: grid;
  grid-template-columns: 25% repeat(3, 1fr);
  align-items: baseline;
  gap: 0.5rem;
}

.type-select h2 {
  margin: 0;
  font-size: 1rem;
}

.type-select label {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  border: 1px solid #ced4da;
  padding: 0.5rem;
  border-radius: 4px;
  font-size: 0.875rem;
}

.type-select label:has(input:checked) {
  background-color: #333;
  border-color: #333;
  color: white;
}
</style>
