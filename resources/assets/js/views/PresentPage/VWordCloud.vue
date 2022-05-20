<template>
  <div class="wordcloud">
    <div class="position-relative wordcloud-wrap">
      <button class="wordcloud__refresh-btn btn" @click="renderWordcloud">
        <i class="material-icons">refresh</i>
        <span class="sr-only">Refresh</span>
      </button>
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
import { ref, onMounted, watchEffect, computed } from "vue";
import { removeStopwords } from "stopword";
import { Chart, ChartEvent, LinearScale } from "chart.js";
import {
  IWordElementProps,
  WordCloudController,
  WordElement,
} from "chartjs-chart-wordcloud";
import getBaseFontSize from "./getBaseFontSize";
import type { Ref } from "vue";
import type { WordFrequencyLookup } from "../../types";

Chart.register(WordCloudController, WordElement, LinearScale);

interface Props {
  text: string;
  filteredWords: string[];
}
const props = withDefaults(defineProps<Props>(), {
  filteredWords: () => [],
});

interface Emits {
  (e: "click:word", word: string): void;
}
const emit = defineEmits<Emits>();

const canvasRoot = ref<HTMLElement | null>(null);
const wordColors = [
  "#693EA6",
  "#8B3DAF",
  "#AF40AC",
  "#D34A9C",
  "#EE626F",
  "#F18E44",
];

function normalizeWordlist(words: string): string[] {
  const wordlist: string[] = words.toLowerCase().split(/\W+/gm);
  const wordlistWithoutStopwords = removeStopwords(wordlist);
  const filteredWordlist = wordlistWithoutStopwords.filter(
    (word) => !props.filteredWords.includes(word)
  );
  return filteredWordlist;
}

function toWordFrequency(wordlist: string[]): WordFrequencyLookup {
  return wordlist.reduce((acc, word) => {
    const prevWordCount = acc[word] || 0;
    return {
      ...acc,
      [word]: prevWordCount + 1,
    };
  }, {});
}

const chart: Ref<Chart<"wordCloud", number[], string> | null> = ref(null);
const filteredWordlist = computed(() => normalizeWordlist(props.text));
const wordFreqLookup = computed(() => toWordFrequency(filteredWordlist.value));
const orderedWordList = computed(() =>
  Object.entries(wordFreqLookup.value)
    // eslint-disable-next-line @typescript-eslint/no-unused-vars
    .sort(([word1, freq1], [word2, freq2]) => freq2 - freq1)
);

function renderWordcloud() {
  if (!canvasRoot.value) return;

  const words = Object.keys(wordFreqLookup.value);
  const baseFontSize = getBaseFontSize({
    canvasRoot: canvasRoot.value,
    wordFreqLookup: wordFreqLookup.value,
  });
  console.log(baseFontSize);
  const wordFontSizes = Object.values(wordFreqLookup.value).map(
    (freq) => freq * baseFontSize
  );

  // create a new Canvas element and attach to root
  const canvas = document.createElement("canvas");
  canvas.setAttribute("role", "img");
  canvasRoot.value.replaceChildren(canvas);

  canvas.setAttribute(
    "aria-label",
    // add an ordered wordlist to the alt text for the canvas to help
    // with accessibility
    orderedWordList.value.map(([word, freq]) => `${word}: ${freq}`).join(", ")
  );

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
  return chart;
}

watchEffect(renderWordcloud);
onMounted(renderWordcloud);
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
.wordcloud__refresh-btn {
  position: absolute;
  top: 0;
  right: 0;
  z-index: 1;
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
