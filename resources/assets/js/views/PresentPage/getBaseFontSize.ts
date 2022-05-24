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
 * - and we want to find the base font size b such that
 *   we can fit all the words
 * - A normal character will use b^2 pixel (roughly)
 * - And if the character is scaled if the word is use more
 *   frequently, a given character for a word will need:
 *     $ b^2 * freq_i^2 $
 *   pixels.
 * - So the full word will need b^2 * freq_i^2 * len_i
 * pixels.
 *
 * Then we have:
 * $$\Sum{i=0}^{n} b^2 *{f_i}^2 * l_i  = W * H$$
 *
 * Factoring out b^2:
 * $$ b^2 * \Sum{i=0}^{n} {f_i}^2 * l_i  = W * H $$
 *
 * Solving for b:
 * $$ b = \sqrt{\frac{W * H}{\Sum{i=0}^{n} {f_i}^2 * l_i } $$
 */

// tuning parameter to allow for more white space
// if it seems we can't fit everything on the page
// this is inversely proportional to the base font size
// so increasing above 1 will decrease the base fontsize
// allowing for more whitespace per character
const whitespaceTuning = 1;

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
  const bSquared =
    (canvasRoot.clientWidth * canvasRoot.clientHeight) / sumOfWordSizes;
  const baseFontSize = Math.floor(Math.sqrt(bSquared));

  // cap the base font size
  return Math.min(baseFontSize, 64);
}
