import nlp from "compromise";
import { stemmer } from "stemmer";
import { removeStopwords } from "stopword";

import type { WordFrequencyLookup } from "../types";

// 'new york' => 'New York'
function toTitleCase(str: string) {
  return str
    .toLowerCase()
    .split(" ")
    .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
    .join(" ");
}

function cleanupText(text: string) {
  return text
    .replace(/[^a-zA-Z0-9 ]/g, "") // remove non-alphanumeric chars
    .replace(/\s+/g, " ") // replace multiple spaces with a single space
    .trim(); // remove leading and trailing whitespace
}

// removeWordsFromText("New York, new", ["Minnesota"]) => "world"
function removeWordsFromText(text, wordsToRemove) {
  const anyWordMatch = new RegExp(
    `\\b(${wordsToRemove.join("|")})\\b`, // match any word
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
  const normalizedText = nlp(text).normalize({
    possessives: true,
    plurals: true,
  });

  // get a list of topic words like ["James Bond", "New York"]
  const topics = normalizedText.topics().out("array").map(toTitleCase);

  // get a list of verbs for stemming: ["walk", "walked", "walking"]
  const verbs = normalizedText.verbs().terms().out("array");
  const stemmedVerbs = verbs.map(stemmer);

  // before removing the topics and verbs from the text,
  // we sort the them so that the longest word are removed first
  // otherwise, topic "Molly" might be removed before topic "Molly Ringwald"
  // which would leave "Ringwald" in the text.
  // This would cause "Molly", "Ringwald", and "Molly Ringwald" to all show up
  // in the word frequency lookup
  const sortedWordsToRemove = [...topics, ...verbs].sort((a, b) => {
    // sort from largest to smallest
    return b.length - a.length;
  });

  // get the remaining words in the text
  const otherWords = removeWordsFromText(
    normalizedText.out("text"),
    sortedWordsToRemove
  );

  const otherWordList = nlp(otherWords)
    .normalize({
      possessives: true,
      plurals: true,
    })
    .terms()
    .out("array");

  // console.log(topics, stemmedVerbs, otherWordList);

  // remove any stopwords and filtered words
  const filteredWordlist = removeStopwords([
    ...topics,
    ...stemmedVerbs,
    ...otherWordList,
  ]).filter((word) => !filteredWords.includes(word));

  // get the word frequency count
  return toWordFrequencyLookup(filteredWordlist);
};
