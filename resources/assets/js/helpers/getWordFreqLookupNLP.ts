import nlp from "compromise";
import { removeStopwords } from "stopword";

import type { WordFrequencyLookup } from "../types";

function cleanupText(text: string) {
  return text
    .replace(/[^a-zA-Z0-9 ]/g, "") // remove non-alphanumeric chars
    .replace(/\s+/g, " ") // replace multiple spaces with a single space
    .trim(); // remove leading and trailing whitespace
}

// removeWordsFromText("New York, new", ["Minnesota"]) => "world"
function removeWordsFromText(text: string, wordsToRemove: string[]) {
  // before removing the words from the text,
  // we sort the them so that the longest word are removed first
  // otherwise, topic "Molly" might be removed before topic "Molly Ringwald"
  // which would leave "Ringwald" in the text.
  const sortedWordsToRemove = [...wordsToRemove].sort((a, b) => {
    // sort from largest to smallest
    return b.length - a.length;
  });

  const anyWordMatch = new RegExp(
    `\\b(${sortedWordsToRemove.join("|")})\\b`, // match any word
    "gi" // global and case insensitive
  );

  const textWithoutTopics = text.replace(anyWordMatch, "");

  // do a bit of cleanup in case we have multiple spaces,
  // weird punctuation, etc.
  return cleanupText(textWithoutTopics);
}

function toWordFrequencyLookup(wordlist: string[]) {
  return wordlist.reduce((acc, word) => {
    const prevWordCount = acc[word] || 0;
    return {
      ...acc,
      [word]: prevWordCount + 1,
    };
  }, {});
}

export default (
  text: string,
  filteredWords: string[] = []
): WordFrequencyLookup => {
  const normalizedText = nlp(text)
    .normalize({
      possessives: true,
      plurals: true,
      verbs: true,
    })
    .text();

  // get a list of topic words like ["James Bond", "New York"]
  const topics = nlp(normalizedText)
    .topics()
    .toTitleCase()
    .out("array")
    .map(cleanupText);

  // get the remaining words in the text
  const otherWords = removeWordsFromText(normalizedText, topics);

  const otherWordList = nlp(otherWords)
    .normalize({
      possessives: true,
      plurals: true,
      verbs: true,
    })
    .terms()
    .out("array");

  // remove any stopwords and filtered words
  const filteredWordlist = removeStopwords([
    ...topics,
    ...otherWordList,
  ]).filter((word) => !filteredWords.includes(word));

  // get the word frequency count
  return toWordFrequencyLookup(filteredWordlist);
};
