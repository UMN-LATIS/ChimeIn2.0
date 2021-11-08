<template>
  <div class="chartContainer">
    <GChart
      ref="googleChart"
      type="ColumnChart"
      :resizeDebounce="100"
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
</style>

<script>
import { GChart } from "vue-google-charts";
import isObject from "lodash/isObject";

export default {
  props: ["responses", "question"],
  components: {
    GChart,
  },
  data: function() {
    return {
      visible_responses: [],
      response_search: "",
      chartEvents: {
        /**
         * To support LaTeX equations, we replace the plain text labels
         * with HTML.
         */
        ready: () => {
          this.$nextTick(() => {
            // get labels that contain html paragraphs
            const labels = [
              ...this.$refs.googleChart.$el.querySelectorAll("text"),
            ].filter((el) => /<p>/.test(el.textContent));

            // get our choices. textContent of labels likely won't contain
            // the full response content
            const choices = this.question.question_info.question_responses.map(
              (q) => q.text
            );

            // replace each text node with a `<foreignObject>` element
            // containing the HTML
            labels.forEach((label, index) => {
              const x = label.getAttribute("x");
              const y = label.getAttribute("y");
              label.parentNode.innerHTML = `
                <foreignObject width="100" height="24" x=${x} y=${y}>
                  ${choices[index]}
                </foreignObject>
              `;
            });
          });
        },
      },
      options: {
        height: "100%",
        // animation: {
        //   duration: 1000,
        //   easing: "out",
        //   startup: true,
        // },
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
    chartData: function() {
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
