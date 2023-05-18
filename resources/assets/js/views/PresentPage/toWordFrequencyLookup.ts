import { removeStopwords } from "stopword/dist/stopword.esm.min.mjs";
import { WordFrequencyLookup } from "../../types";

const quotedRegex = /"(?<quoted>[^"]*)"/g;

function removeQuotedStrings(text: string) {
  return text.replace(quotedRegex, "");
}

export function getQuotedStrings(text): string[] {
  // get the captured group from the regex at index 1
  return [...text.matchAll(quotedRegex)].map((m) => m[1]);
}

function getSingleWords(text) {
  return (
    text
      .toLowerCase()
      // remove any quoted chars
      .replace(/['"“”‘’„”«»]/g, "")
      // don't split on '&' (e.g. S&P500)
      .split(/[^&\w]+/gm)
  );
}

function getWordsFromText(
  text: string,
  filteredWords: string[] = []
): string[] {
  const textWithoutQuotedStrings = removeQuotedStrings(text);
  const singleWords = getSingleWords(textWithoutQuotedStrings);

  // remove stopwords and filtered words
  const normalizedSingleWords = removeStopwords(singleWords).filter(
    (word) => !filteredWords.includes(word)
  );

  return [...getQuotedStrings(text), ...normalizedSingleWords];
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
