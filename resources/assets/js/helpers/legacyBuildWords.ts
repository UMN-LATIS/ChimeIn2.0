import nlp from "compromise";
import { removeStopwords } from "stopword";
import { stemmer } from "stemmer";
import type { WordFrequencyLookup } from "../types";
interface WordListItem {
  name: string;
  stem: string;
  value: number;
}

function cleanupText(text: string) {
  return text
    .replace(/[^a-zA-Z0-9'\- ]/g, "") // remove non-alphanumeric chars except hyphens
    .replace(/\s+/g, " ") // replace multiple spaces with a single space
    .trim(); // remove leading and trailing whitespace
}

export default function getWordFreqLookupNLP(
  text: string,
  filterWords: string[] = []
): WordFrequencyLookup {
  const words = text;

  let nonTopics;
  const doc = nlp(words);

  // sort so that longest is first in case topic is a substring
  // of another topic
  const topics = doc
    .topics()
    .toTitleCase()
    .out("array")
    .map(cleanupText)
    .sort((a, b) => b.length - a.length);

  // this will be the string of all responses without the topics
  nonTopics = words;

  for (let i = 0; i < topics.length; i++) {
    // remove topics words

    // escapes any special chars from the topic that might mess up the regex
    const escapedTopic = topics[i].replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
    nonTopics = nonTopics.replace(
      new RegExp("\\b" + escapedTopic + "\\b", "gi"),
      "" // remove the topic
    );
  }

  if (!nonTopics.length && !topics.length) {
    return {};
  }

  // words is a single string of all the responses
  // we need to break into tokens, so that we keep things together like "New York" and "New York City" as one word
  const wordsWithoutStops = removeStopwords(
    // keep quote stuff together, but don't grab the quotes
    nonTopics.match(/"(.*?)"|\w+/g)
  );

  // combine the topics and the our nonTopic words into one array
  // (non topic words could also be "Quoted String" ..)
  const finalizedWords = wordsWithoutStops
    // convert nonTopic words to lowercase
    .map((w) => w.toLowerCase())
    // add the topics back in
    .concat(topics)
    // remove user defined filter words
    .filter((w) => !filterWords.includes(w))
    // remove any short words
    .filter((w) => w.length > 1)
    // remove any punctuation
    .map(cleanupText);

  const groups: WordListItem[] = finalizedWords.reduce((acc: any[], w) => {
    const unquotedWord = w.replace(/"/g, "");

    const normalizedWord = unquotedWord.toLowerCase();
    const stem = stemmer(normalizedWord);

    const indexOfStem = acc.findIndex((e) => e.stem === stem);

    if (indexOfStem > -1) {
      acc[indexOfStem].value += 1;
    } else {
      acc.push({
        name: unquotedWord,
        value: 1,
        stem: stem,
      });
    }

    return acc;
  }, []);

  // remove only the most important word. So sort them by count
  const sortedArray = groups.sort((a, b) => {
    return b.value - a.value;
  });

  // only return the top 200 words
  const topWordsOnly = sortedArray.slice(0, 200);

  // convert to a word frequency lookup
  return topWordsOnly.reduce((acc, word) => {
    return {
      ...acc,
      [word.name]: word.value,
    };
  }, {});
}
