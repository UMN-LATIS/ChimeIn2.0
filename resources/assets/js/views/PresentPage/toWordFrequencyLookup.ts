import { removeStopwords } from "stopword/dist/stopword.esm.min.mjs";
import { WordFrequencyLookup } from "../../types";

const quotedRegex = /"(?<quoted>[^"]*)"/g;

function removeQuotedStrings(text) {
  return text.replace(quotedRegex, "");
}

function getQuotedStrings(text): string[] {
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

function getWordsFromText(text: string): string[] {
  const textWithoutQuotedStrings = removeQuotedStrings(text);

  return [
    ...getQuotedStrings(text),
    ...getSingleWords(textWithoutQuotedStrings),
  ];
}

export default function toWordFrequencyLookup(
  responseTexts: string[],
  filteredWords: string[] = []
): WordFrequencyLookup {
  const words: string[] = responseTexts
    .filter((n) => n)
    .flatMap(getWordsFromText);

  // filter out stopwords and filtered words
  const normalizedWords = removeStopwords(words).filter(
    (word) => !filteredWords.includes(word)
  );

  return normalizedWords.reduce((acc, word) => {
    const prevWordCount = acc[word] || 0;
    return {
      ...acc,
      [word]: prevWordCount + 1,
    };
  }, {});
}
