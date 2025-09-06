import { WordFrequencyLookup } from "@/types";

const MIN_BASE_FONT_SIZE = 4;
const MAX_BASE_FONT_SIZE = 64;
const WHITESPACE_TUNING = 0.9; // leave some whitespace

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
 * - And the area of a normal word will be:
 *    $ b^2 * word.length $
 * - And if the character is scaled if the word is use more
 *   frequently, a given character for a word will need:
 *     $ (b * freq_i)^2 * word.length $
 *   pixels.
 *
 * Then we have:
 * $$\Sum{i=0}^{n} b^2 * freq_i^2 * length_i  = W * H$$
 *
 * Factoring out b^2:
 * $$ b^2 * \Sum{i=0}^{n} freq_i^2 * length_i  = W * H $$
 *
 * Solving for b:
 * $$ b = \sqrt{\frac{W * H}{\Sum{i=0}^{n} freq_i^2 * length_i } $$
 */

function getBaseFontSize({
  canvasArea,
  wordFreqLookup,
}: {
  canvasArea: number;
  wordFreqLookup: WordFrequencyLookup;
}): number {
  const sumOfWordSizes = Object.entries(wordFreqLookup)
    // calculate the area needed for each word
    .map(([word, freq]) => word.length * freq ** 2)
    .reduce((acc, size) => acc + size, 0);

  const base_squared = (WHITESPACE_TUNING * canvasArea) / sumOfWordSizes;

  // sqrt to convert from area to linear dimension
  return Math.floor(Math.sqrt(base_squared));
}

function scaleFreqLookup(
  wordFreqLookup: Record<string, number>,
  scalingFn: (freq: number) => number
): Record<string, number> {
  const scaledEntries = Object.entries(wordFreqLookup).map(([word, freq]) => {
    // apply the scaling function to the frequency
    return [word, scalingFn(freq)] as [string, number];
  });

  return Object.fromEntries(scaledEntries);
}

export function getFontSizeLookup({
  wordFreqLookup,
  canvasArea,
}: {
  wordFreqLookup: Record<string, number>;
  canvasArea: number;
}): Record<string, number> {
  if (Object.keys(wordFreqLookup).length === 0) return {};

  const normalizers = [
    (freq: number) => freq, // linear
    (freq: number) => Math.sqrt(freq),
    (freq: number) => Math.log10(freq),
  ];

  for (const [index, normalizer] of normalizers.entries()) {
    const normalizedFreqLookup = scaleFreqLookup(wordFreqLookup, normalizer);
    const baseFontSize = getBaseFontSize({
      wordFreqLookup: normalizedFreqLookup,
      canvasArea,
    });

    const isValidFontSize = baseFontSize >= MIN_BASE_FONT_SIZE;
    const isLastNormalizer = index === normalizers.length - 1;

    if (isValidFontSize || isLastNormalizer) {
      return scaleFreqLookup(
        normalizedFreqLookup,
        (freq) => freq * Math.min(MAX_BASE_FONT_SIZE, baseFontSize) // cap the base font size
      );
    }
  }

  throw new Error("No suitable normalizer found");
}
