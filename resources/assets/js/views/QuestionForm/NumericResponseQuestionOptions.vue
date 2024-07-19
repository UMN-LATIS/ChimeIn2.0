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
          :checked="typedQuestionResponsesProp.chart_type === 'bar'"
          @change="
            $emit('update:question_responses', {
              ...typedQuestionResponsesProp,
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
          :checked="typedQuestionResponsesProp.chart_type === 'scatter'"
          @change="
            $emit('update:question_responses', {
              ...typedQuestionResponsesProp,
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
          :value="typedQuestionResponsesProp.x_axis_label"
          required
          @input="
            (event) =>
              $emit('update:question_responses', {
                ...typedQuestionResponsesProp,
                x_axis_label: (event.target as HTMLInputElement).value,
              })
          "
        />
      </div>
    </div>

    <div v-if="typedQuestionResponsesProp.chart_type === 'scatter'" class="row">
      <label for="x-axis-label" class="col-form-label col-sm-3">
        Y Axis Label
      </label>
      <div class="col-sm-9">
        <input
          type="text"
          class="form-control"
          :value="typedQuestionResponsesProp.y_axis_label"
          :required="typedQuestionResponsesProp.chart_type === 'scatter'"
          @input="
            (event) =>
              $emit('update:question_responses', {
                ...typedQuestionResponsesProp,
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
import { computed } from "vue";

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
  question_responses:
    | NumericResponseQuestionInfo["question_responses"]
    | unknown;
}>();

defineEmits<{
  (
    event: "update:question_responses",
    value: NumericResponseQuestionInfo["question_responses"]
  ): void;
}>();

function isNumericResponseQuestionInfo(
  question_responses:
    | NumericResponseQuestionInfo["question_responses"]
    | unknown
): question_responses is NumericResponseQuestionInfo["question_responses"] {
  return (
    typeof question_responses === "object" &&
    question_responses !== null &&
    "chart_type" in question_responses &&
    "x_axis_label" in question_responses &&
    "y_axis_label" in question_responses
  );
}

const typedQuestionResponsesProp = computed(() => {
  if (isNumericResponseQuestionInfo(props.question_responses)) {
    return props.question_responses;
  }
  return {
    chart_type: "bar",
    x_axis_label: "",
    y_axis_label: "",
  } as NumericResponseQuestionInfo["question_responses"];
});
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
