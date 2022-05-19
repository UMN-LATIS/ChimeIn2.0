<template>
  <div class="wordcloud">
    <button class="wordcloud__refresh-btn btn" @click="renderWordcloud">
      <i class="material-icons">refresh</i>
      <span class="sr-only">Refresh</span>
    </button>
    <div ref="canvasRoot" class="canvas-container"></div>
  </div>
</template>
<script setup lang="ts">
import { ref, onMounted, watchEffect } from "vue";
import { removeStopwords } from "stopword";
import { Chart, ChartEvent, LinearScale } from "chart.js";

import {
  IWordElementProps,
  WordCloudController,
  WordElement,
} from "chartjs-chart-wordcloud";
import type { Ref } from "vue";
type WordFrequenceLookup = {
  [word: string]: number;
};

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

function toWordFrequency(words: string): WordFrequenceLookup {
  return normalizeWordlist(words).reduce((acc, word) => {
    const prevWordCount = acc[word] || 0;
    return {
      ...acc,
      [word]: prevWordCount + 1,
    };
  }, {});
}

const chart: Ref<Chart<"wordCloud", number[], string> | null> = ref(null);
function renderWordcloud() {
  if (!canvasRoot.value) return;

  // create a new Canvas element and attach to root
  const canvas = document.createElement("canvas");
  canvas.setAttribute("aria-label", "wordcloud");
  canvas.setAttribute("role", "img");
  canvasRoot.value.replaceChildren(canvas);

  const text = props.text;
  const wordFreqLookup = toWordFrequency(text);
  const words = Object.keys(wordFreqLookup);
  const wordFrequencies = Object.values(wordFreqLookup).map(
    (freq) => 16 + freq * 16
  );

  new Chart(canvas, {
    type: "wordCloud",
    data: {
      labels: words,
      datasets: [
        {
          label: "",
          data: wordFrequencies,
          family: "Arial Black",
          color(ctx) {
            return wordColors[ctx.dataIndex % wordColors.length];
          },
        },
      ],
    },
    options: {
      interaction: {
        mode: "point",
      },
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

watchEffect(() => renderWordcloud());

onMounted(renderWordcloud);
</script>
<style>
.wordcloud {
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
  border: 1px solid #ccc;
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
}
</style>
