<template>
  <div class="chartContainer" data-cy="chart-container">
    <GChart
      type="ColumnChart"
      :resizeDebounce="100"
      :data="chartData"
      :options="options"
      class="googleChart"
    />
  </div>
</template>

<style>
.chartContainer {
  height: 600px;
}

.googleChart {
  height: 100%;
}
</style>

<script>
import { GChart } from "vue-google-charts";

export default {
  props: ["responses", "question"],
  components: {
    GChart,
  },
  data: function () {
    return {
      visible_responses: [],
      response_search: "",
      options: {
        height: "100%",
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
          format: "#.#",
        },
      },
    };
  },
  computed: {
    myStyles() {
      return {
        position: "relative",
      };
    },
    binnedValues() {
      return consHistObjWithBins(
        this.responses.map((r) => r.response_info),
        {
          min: 0,
          max: 100,
          width: 6.25,
        }
      )("choice");
    },
    labels() {
      if (
        !this.question.question_info.question_responses.range_type ||
        this.question.question_info.question_responses.range_type ==
          "Qualitative"
      ) {
        return [
          this.question.question_info.question_responses.left_choice_text,
          "",
          "",
          "",
          "",
          "",
          "",
          "",
          "",
          "",
          "",
          "",
          "",
          "",
          "",
          this.question.question_info.question_responses.right_choice_text,
        ];
      } else if (
        this.question.question_info.question_responses.range_type ==
        "Numeric (Linear)"
      ) {
        let leftValue = parseInt(
          this.question.question_info.question_responses.left_choice_text
        );
        let rightValue = parseInt(
          this.question.question_info.question_responses.right_choice_text
        );
        let range = rightValue - leftValue;
        let increment = 16;

        let width = range / increment;
        var outputArray = [];

        var endPoint = null;
        for (var i = leftValue; i < rightValue; i = i + width) {
          endPoint = (i + width).toFixed(2);
          outputArray.push(i.toFixed(2) + " - " + (endPoint - 0.01).toFixed(2));
        }

        outputArray[outputArray.length - 1] =
          (endPoint - width).toFixed(2) + " - " + rightValue;

        return outputArray;
      }
    },
    values() {
      return Object.values(this.binnedValues);
    },
    chartData: function () {
      var questionArray = this.values.map((e, i) => [
        this.labels[i],
        e,
        "color: rgb(54, 162, 235); opacity: 0.4; stroke-opacity: 0.9; stroke-width: 2",
      ]);
      questionArray.unshift([
        "Answer",
        "Number of Responses",
        { role: "style" },
      ]);
      return questionArray;
    },
  },
};

/*
 * Construct bins.
 * consBins :: Object → [[Number]]
 *
 * Configuration parameters:
 * min - The numerical floor from which to initialize the first interval.
 * max - The numerical ceiling by which we limit the last of intervals.
 * width - The range encompassed by each interval.
 **/
const consBins = ({ min = 0, max = Infinity, width = 1, accum = [] }) =>
  // Ensure `max` does not exceed the range of our current interval...
  min + width > max // If so, return our accumulator;
    ? accum // Otherwise, call upon our method recursively until we fulfill our
    : // predicating condition.
      consBins({
        // Increment interval, so as to preclude overlap.
        min: min + width,
        // `max` and `width` remain constant.
        max,
        width,
        // Wax upon our accumulator, combining it with a new array, in which the
        // current interval is represented.
        accum: accum.concat([[min, min + width]]),
      });

/*
 * Filter property by minimum and maximum values.
 * filterPropByMinMax :: Object → Array
 *
 * From a collection, return only those cases where the given property falls
 * within range of specified minimum and maximum values.
 *
 * Configuration parameters:
 * {String} key - The property to -- and by which to -- filter.
 * {String} min - The minimum value by which to filter our attribute.
 * {String} min - The maximum value by which to filter our attribute.
 **/
const filterPropByMinMax = (arr, { key = "", min = "0", max = "100" }) =>
  arr.filter(
    // The numerical values encompassed by our JSON list assume the form of
    // strings; hence, we need abstract their numerical values via `parseInt`.
    // Our callback will return `true` provided the item's value is greater than
    // or equal to our minimum _and_ less than or equal to our maximum.
    (item) => parseInt(item[key], 10) >= min && parseInt(item[key], 10) < max
  );

/*
 * Construct histogram object.
 * consHistObj :: (Array, [[Number]]) → String → Object
 **/
const consHistObj =
  (arr = [], bins = []) =>
  (key = "") =>
    // For each of the nested arrays within out `bins` list...
    bins.reduce(
      (accum, currVal) => ({
        // Build upon our accumulator, which we initialize as an empty object...
        ...accum,
        // And where each entry is an object, whereof the the key shall represent
        // the minimum and maximum values encompassed by each bin (e.g., `15,19`),
        // and whereof the value shall delineate all vehicles encompassed by such
        // bin. We retrieve such values by of `filterPropByMinMax`...
        [currVal]: filterPropByMinMax(arr, {
          key,
          min: currVal[0],
          max: currVal[1],
          // And we calculate a total by way of `Array.prototype.length`.
        }).length,
      }),
      {}
    );

/*
 * Construct histogram object provided bins.
 * consHistObjWithBins :: (Object → [Object] → String) → Object
 *
 * Configuration parameters:
 * {Number} min - The minimum value by which to filter our attribute.
 * {Number} min - The maximum value by which to filter our attribute.
 * {Number} width - The range encompassed by each interval.
 **/
const consHistObjWithBins =
  (arr = [], { min = 0, max = 100, width = 1 }) =>
  (key = "") => {
    var bins = consBins({
      min,
      max,
      width,
    });
    // we fudge the final bin to be ever so slightly larger than our max to ensure we bin values that equal the max.
    // otherwise our < max checks will filter that value.
    bins[bins.length - 1][1] = bins[bins.length - 1][1] + 0.0001;
    return consHistObj(arr, bins)(key);
  };
</script>
