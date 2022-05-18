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
import { ref, onMounted, watch } from "vue";
import { removeStopwords } from "stopword";
import { Chart, LinearScale } from "chart.js";
import { WordCloudController, WordElement } from "chartjs-chart-wordcloud";
import type { Ref } from "vue";

Chart.register(WordCloudController, WordElement, LinearScale);

interface Props {
  text: string;
}

const props = defineProps<Props>();

const canvasRoot = ref<HTMLElement | null>(null);

type WordFrequenceLookup = {
  [word: string]: number;
};

function normalizeWordlist(words: string): string[] {
  const wordlist: string[] = words.toLowerCase().split(/\W+/gm);
  const wordlistWithoutStopwords = removeStopwords(wordlist);
  // return wordlistWithoutStopwords.map(stemmer);
  return wordlistWithoutStopwords;
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
  canvasRoot.value.replaceChildren(canvas);

  const text = props.text;
  const wordFreqLookup = toWordFrequency(text);
  const words = Object.keys(wordFreqLookup);
  const wordFrequencies = Object.values(wordFreqLookup).map(
    (freq) => 10 + freq * 10
  );

  new Chart(canvas, {
    type: "wordCloud",
    data: {
      labels: words,
      datasets: [
        {
          label: "",
          data: wordFrequencies,
        },
      ],
    },
    // options: {
    //   animation: {
    //     onProgress(animation: AnimationEvent) {
    //       if (stopRequested.value) {
    //         animation.stop();
    //       }
    //     }),
    //     onComplete() {
    //       isUpdating.value = false;
    //     },
    //   },
    // },
  });
  return chart;
}

watch(() => props.text, renderWordcloud);
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
