<template>
  <div class="chartContainer">
    <GChart
      ref="googleChart"
      type="ColumnChart"
      :resize-debounce="100"
      :data="chartData"
      :options="options"
      class="googleChart"
      :events="chartEvents"
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

/* for HTML chart labels */
.chartContainer foreignObject p {
  text-align: center;
}
</style>

<script>
import { GChart } from "vue-google-charts";
import isObject from "lodash/isObject";
import insertHtmlLabelsIntoGchart from "../../helpers/insertHtmlLabelsIntoGchart";

const ANIMATION_DURATION = 1000;

export default {
  components: {
    GChart,
  },
  props: ["responses", "question"],
  data: function () {
    return {
      visible_responses: [],
      response_search: "",
      chartEvents: {
        /**
         * To support LaTeX equations, we replace the plain text labels
         * with HTML.
         */
        ready: () => {
          const chart = this.$refs.googleChart.$el;
          const htmlLabels = this.question.question_info.question_responses.map(
            (q) => q.text
          );

          // when animations are turned on, these labels will be overwritten, so we reinsert the labels with each animation frame
          let start;
          const updateLabels = (timestamp) => {
            if (start === undefined) {
              start = timestamp;
            }

            const elapsed = timestamp - start;

            // stop looping a bit after animation duration completes
            if (elapsed < ANIMATION_DURATION * 1.25) {
              insertHtmlLabelsIntoGchart(chart, htmlLabels);
              window.requestAnimationFrame(updateLabels);
            }
          };

          window.requestAnimationFrame(updateLabels);
        },
      },
      options: {
        height: "100%",
        animation: {
          duration: ANIMATION_DURATION,
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
          format: "%",
        },
        tooltip: {
          isHtml: false,
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
    chartData: function () {
      var questionArray = this.question.question_info.question_responses.map(
        (q) => {
          var questionText = isObject(q) ? q.text : q;
          var formattedQuestion = questionText;
          if (isObject(q) && q.correct == true) {
            formattedQuestion = formattedQuestion + " ✓";
          }
          var totalResponsesForAnswer = this.responses.filter((r) =>
            Array.isArray(r.response_info.choice)
              ? r.response_info.choice.includes(questionText)
              : r.response_info.choice == questionText
          ).length;
          var totalResponsesForQuestion = this.responses.length;
          return [
            formattedQuestion,
            totalResponsesForAnswer / totalResponsesForQuestion,
            "color: rgb(54, 162, 235); opacity: 0.4; stroke-opacity: 0.9; stroke-width: 2",
            "Number of Responses:  " +
              totalResponsesForAnswer +
              "\n" +
              "Percentage: " +
              Math.round(
                (totalResponsesForAnswer / totalResponsesForQuestion) * 10000
              ) /
                100 +
              "%",
          ];
        }
      );
      questionArray.unshift([
        "Answer",
        "Number of Responses",
        { role: "style" },
        { type: "string", role: "tooltip", p: { html: false } },
      ]);
      return questionArray;
    },
  },
};
</script>
