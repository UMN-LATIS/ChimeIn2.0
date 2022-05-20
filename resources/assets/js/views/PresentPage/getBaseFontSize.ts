import type { WordFrequencyLookup } from "../../types";

/**
 * Used with the Wordcloud to geta a base font size that
 * will allow all characters from a given wordlist to fit on the canvas.
 *
 * Thinking:
 * - Canvas size = W * H
 * - there are n words to fit on the canvas
 * - a given word (word_i) has character length len_i and
 *   a frequency of freq_i
 * So, a given character for a word will be b * freq_i^2
 * pixels. And the full word will need b * freq_i^2 * len_i
 * pixels.
 *
 * Then we have:
 * $$\Sum{i=0}^{n} b *{f_i}^2 * l_i  = W * H$$
 *
 * Factoring out b:
 * $$ b * \Sum{i=0}^{n} {f_i}^2 * l_i  = W * H $$
 *
 * Solving for b:
 * $$ b = \frac{W * H}{{f_i}^2 * l_i } $$
 */

// tuning parameter to allow for more white space
const whitespaceTuning = 6;

const getWordSize = ([word, freq]: [string, number]): number =>
  whitespaceTuning * word.length * freq ** 2;

export default function getBaseFontSize({
  canvasRoot,
  wordFreqLookup,
}: {
  canvasRoot: HTMLElement;
  wordFreqLookup: WordFrequencyLookup;
}): number {
  const sumOfWordSizes = Object.entries(wordFreqLookup)
    .map(getWordSize)
    .reduce((acc, size) => acc + size, 0);
  return (canvasRoot.clientWidth * canvasRoot.clientHeight) / sumOfWordSizes;
}
