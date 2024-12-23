import { removeStopwords } from "stopword/dist/stopword.esm.min.mjs";
import { WordFrequencyLookup } from "../../types";

const quotedRegex = /"(?<quoted>[^"]*)"/g;

export function removeQuotedStrings(text: string) {
  return text
    .replace(quotedRegex, "") // remove quoted strings
    .replace(/\s+/g, " ") // remove extra whitespace
    .trim(); // remove leading/trailing whitespace
}

export function getQuotedStrings(text): string[] {
  // get the captured group from the regex at index 1
  return [...text.matchAll(quotedRegex)].map((m) => m[1]);
}

export function getSingleWords(text) {
  return (
    text
      .toLowerCase()
      // remove any quoted chars
      .replace(/['"“”‘’„”«»]/g, "")
      // don't split on '&' (e.g. S&P500)
      .split(/[^&\w]+/gm)
  );
}

export function getWordsFromText(
  text: string,
  filteredWords: string[] = []
): string[] {
  // remove extra whitespace
  text = text.replace(/\s+/g, " ").trim();

  const filteredWordsSet = new Set(filteredWords);
  const textWithoutQuotedStrings = removeQuotedStrings(text);
  const singleWords = getSingleWords(textWithoutQuotedStrings);

  // remove stopwords and filtered words
  const normalizedSingleWords = removeStopwords(singleWords);

  return [...getQuotedStrings(text), ...normalizedSingleWords]
    .map((word) => word.trim())
    .filter((word) => word !== "" && !filteredWordsSet.has(word));
}

function isNotEmpty<TValue>(value: TValue | null | undefined): value is TValue {
  return value !== null && value !== undefined;
}

export default function toWordFrequencyLookup(
  responseTexts: (string | null)[],
  filteredWords: string[] = []
): WordFrequencyLookup {
  return (
    responseTexts
      // remove null responses
      .filter(isNotEmpty)
      .flatMap((text) => getWordsFromText(text, filteredWords))
      .reduce((acc, word) => {
        const prevWordCount = acc[word] || 0;
        return {
          ...acc,
          [word]: prevWordCount + 1,
        };
      }, {})
  );
}
