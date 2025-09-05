<template>
  <div class="wordcloud tw-rounded-md" data-cy="word-cloud">
    <div class="position-relative wordcloud-wrap">
      <div
        class="tw-flex tw-items-center tw-justify-end tw-relative tw-z-10 tw-p-1 gap-1"
      >
        <button
          class="tw-border tw-p-2 tw-bg-black/5 hover:tw-bg-black/10 tw-transition-colors tw-rounded tw-inline-flex tw-items-center tw-justify-center tw-backdrop-blur-sm tw-border-neutral-200"
          @click="renderWordcloud"
        >
          <i class="material-icons">shuffle</i>
          <span class="sr-only">Shuffle Wordcloud</span>
        </button>
        <button
          class="tw-p-2 tw-bg-black/5 tw-backdrop-blur-sm hover:tw-bg-black/10 tw-transition-colors tw-rounded tw-inline-flex tw-items-center tw-justify-center tw-border tw-border-neutral-200"
          @click="handleReloadPage"
        >
          <i class="material-icons">refresh</i>
          <span class="sr-only">Refresh Page</span>
        </button>
      </div>
      <div ref="canvasRoot" class="canvas-container"></div>
      <div class="slot-wrap">
        <slot></slot>
      </div>
    </div>
    <table
      class="table table-small table-hover word-freq-section__table sr-only"
    >
      <caption>
        Words within WordCloud ranked by frequency
      </caption>
      <thead>
        <tr>
          <th scope="col">Rank</th>
          <th scope="col">Word</th>
          <th scope="col">Frequency</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="([word, freq], index) in orderedWordList" :key="word">
          <th scope="row">
            {{ index + 1 }}
          </th>
          <td>{{ word }}</td>
          <td>{{ freq }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
<script setup lang="ts">
import { onMounted, computed, watch, ref } from "vue";
import { useThrottleFn } from "@vueuse/core";
import { Chart, ChartEvent, LinearScale } from "chart.js";
import {
  IWordElementProps,
  WordCloudController,
  WordElement,
} from "chartjs-chart-wordcloud";
import { getFontSizeLookup } from "./getNormalizedFontSizes";
import type { WordFrequencyLookup } from "../../types";

Chart.register(WordCloudController, WordElement, LinearScale);

const props = defineProps<{
  wordFreqLookup: WordFrequencyLookup;
}>();

const emit = defineEmits<{
  (e: "click:word", word: string): void;
}>();

const canvasRoot = ref<HTMLDivElement | null>(null);
const wordColors = [
  "#693EA6",
  "#8B3DAF",
  "#AF40AC",
  "#D34A9C",
  "#EE626F",
  "#F18E44",
];

const MAX_RENDERED_WORDS = 500;

const truncatedWordFreqLookup = computed(() => {
  const wordCount = Object.keys(props.wordFreqLookup).length;

  if (wordCount < MAX_RENDERED_WORDS) {
    return props.wordFreqLookup;
  }

  const sortedEntries = Object.entries(props.wordFreqLookup).sort(
    ([, a], [, b]) => b - a
  );

  return Object.fromEntries(sortedEntries.slice(0, MAX_RENDERED_WORDS));
}, {});

const orderedWordList = computed(() =>
  Object.entries(truncatedWordFreqLookup.value).sort(
    ([, freq1], [, freq2]) => freq2 - freq1
  )
);

const fontSizeLookup = computed(() => {
  if (!canvasRoot.value) return {};

  const canvasArea =
    canvasRoot.value.clientWidth * canvasRoot.value.clientHeight;

  const fontSizeLookup = getFontSizeLookup({
    wordFreqLookup: truncatedWordFreqLookup.value,
    canvasArea,
  });
  return fontSizeLookup;
});

function renderWordcloud() {
  if (!canvasRoot.value) return;
  const wordFreqLookup = truncatedWordFreqLookup.value;

  const words = Object.keys(wordFreqLookup);
  const wordFontSizes = words.map((word) => fontSizeLookup.value[word]);

  // create a new Canvas element and attach to root
  const canvas = document.createElement("canvas");
  canvas.setAttribute("role", "img");
  canvasRoot.value.replaceChildren(canvas);

  // if there's no words, skip rendering the chart
  if (!words.length) return;

  new Chart(canvas, {
    type: "wordCloud",
    data: {
      labels: words,
      datasets: [
        {
          label: "",
          data: wordFontSizes,
          family: "Impact",
          color(ctx) {
            return wordColors[ctx.dataIndex % wordColors.length];
          },
          rotate: 0,
          minRotation: 0,
          maxRotation: 0,
        },
      ],
    },
    options: {
      onClick(event: ChartEvent & { chart: Chart }) {
        const points = event.chart.getElementsAtEventForMode(
          event.native as Event,
          "nearest",
          { intersect: true },
          true
        );

        if (!points.length) return;

        const wordElement = points[0].element as WordElement &
          IWordElementProps;
        emit("click:word", wordElement.text);
      },
    },
  });
}

const renderWordcloudThrottled = useThrottleFn(renderWordcloud, 500);

function handleReloadPage() {
  // in case the websocket connection breaks, reload the page
  window.location.reload();
}

watch([truncatedWordFreqLookup], renderWordcloudThrottled);
onMounted(renderWordcloudThrottled);
</script>
<style>
.wordcloud {
  border: 1px solid #ddd;
}
.wordcloud-wrap {
  width: 100%;
  height: 500px;
  max-height: 70vh;
  position: relative;
}
.canvas-container {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
}
.slot-wrap {
  position: absolute;
  bottom: 0;
  left: 0;
  width: 100%;
  padding: 0.5rem;
}

.word-freq-section__table {
  max-width: 20rem;
}

.word-freq-section {
  margin: 2rem 0;
}
</style>
