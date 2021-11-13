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
          this.$nextTick(() => {
            // get labels that contain html paragraphs
            const labels = [...chart.querySelectorAll("text")].filter((el) =>
              /<p>/.test(el.textContent)
            );

            // we'll use the chart bars to help with positioning
            // getting bars by stroke color... a bit hacky, but it works
            const bars = [...chart.querySelectorAll('[stroke="#36a2eb"]')];

            // get our choices. textContent of labels likely won't contain
            // the full response content
            const choices = this.question.question_info.question_responses.map(
              (q) => q.text
            );

            // replace each text node with a `<foreignObject>` element
            // containing the HTML
            labels.forEach((label, index) => {
              const bar = bars[index];

              // if no bar, don't bother with the label
              if (!bar) {
                label.textContent = "";
                return;
              }

              // use bar attributes for placing label
              const x = bar.getAttribute("x");
              const width = bar.getAttribute("width");
              const y =
                Number.parseFloat(bar.getAttribute("y")) +
                Number.parseFloat(bar.getAttribute("height")) +
                16;

              label.parentNode.innerHTML = `
                <foreignObject width="${width}" height="50" x=${x} y=${y}>
                  ${choices[index]}
                </foreignObject>
              `;
            });
          });
        },
      },
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
