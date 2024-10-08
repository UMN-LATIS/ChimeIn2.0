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
        ready: () => this.handleHtmlChartLabels(),
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
          const choiceHtml = isObject(q) ? q.text : q;
          const totalResponsesForQuestion = this.responses.length;

          // number of users making this choice
          const totalResponsesForChoice = this.responses.filter((r) =>
            Array.isArray(r.response_info.choice)
              ? r.response_info.choice.includes(choiceHtml)
              : r.response_info.choice == choiceHtml
          ).length;

          return [
            choiceHtml,
            totalResponsesForChoice / totalResponsesForQuestion,
            "color: rgb(54, 162, 235); opacity: 0.4; stroke-opacity: 0.9; stroke-width: 2",
            "Number of Responses:  " +
              totalResponsesForChoice +
              "\n" +
              "Percentage: " +
              Math.round(
                (totalResponsesForChoice / totalResponsesForQuestion) * 10000
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
  methods: {
    handleHtmlChartLabels() {
      const chart = this.$refs.googleChart.$el;
      const questionChoices = this.question.question_info.question_responses;
      const hasCorrectChoice = questionChoices.some((q) => q.correct);

      const checkmarkIcon = `
        <span 
          class="material-icons" 
          style="display: flex; justify-content: center; font-size: 1rem; color: var(--green); line-height: 1;"
        >
          check_circle
        </span>
      `;

      const emptySpan = "<span></span>";

      const createChoiceLabelHtml = (prependHtmlFn) => (choice) =>
        `
        <div style="display: grid; grid-template-rows: 1.5rem 1fr; justify-content: center;">
          ${prependHtmlFn(choice)}
          ${choice.text}
        </div>
        `;

      const prependCheckIfCorrect = (choice) => {
        // noop if there's no correct choice
        if (!hasCorrectChoice) return "";
        return choice.correct ? checkmarkIcon : emptySpan;
      };

      const htmlLabels = questionChoices.map(
        createChoiceLabelHtml(prependCheckIfCorrect)
      );

      const replaceImgWithPlaceholder = (html) => {
        return html.replace(
          /<img[^>]*>/gi,
          `<div style="background: #ddd; width: 100px; height: 100px;"></div>`
        );
      };

      // when animations are turned on, these labels will be overwritten, so we reinsert the labels with each animation frame
      let start;
      const updateLabels = (timestamp) => {
        if (start === undefined) {
          start = timestamp;
        }
        const elapsed = timestamp - start;
        // stop looping a bit after animation duration completes
        if (elapsed < ANIMATION_DURATION * 1.25) {
          // images may be used as labels, and to avoid
          // flashing and repeated image requests, scrub
          // the images from the labels during animation
          // and reinsert them after the animation completes
          const labelsWithoutImages = htmlLabels.map(replaceImgWithPlaceholder);

          insertHtmlLabelsIntoGchart(chart, labelsWithoutImages);
          window.requestAnimationFrame(updateLabels);
        } else {
          // on last frame, insert the original labels with
          // images back into the chart
          insertHtmlLabelsIntoGchart(chart, htmlLabels);
        }
      };
      window.requestAnimationFrame(updateLabels);
    },
  },
};
</script>

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
