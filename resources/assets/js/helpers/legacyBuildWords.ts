import nlp from "compromise";
import { removeStopwords } from "stopword";
import { stemmer } from "stemmer";
import type { WordFrequencyLookup } from "../types";

interface StemFrequencyLookup {
  [stem: string]: {
    wordToDisplay: string;
    count: number;
  };
}

function cleanupText(text: string) {
  return text
    .replace(/[^a-zA-Z0-9- ]/g, "") // remove non-alphanumeric chars except hyphens
    .replace(/\s+/g, " ") // replace multiple spaces with a single space
    .trim(); // remove leading and trailing whitespace
}

const MAX_WORDS = 200;

export default function getWordFreqLookupNLP(
  text: string,
  filterWords: string[] = []
): WordFrequencyLookup {
  const words = text;

  let nonTopics: string;
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

  // get a list of all the words in the non-topics
  // but keep quoted string together as "one word"
  const tokenizedNonTopics = nonTopics.match(/"(.*?)"|'(.*?)'|\w+/g) || [];

  // remove stopwords like "the" and "a"
  const wordsWithoutStops = removeStopwords(tokenizedNonTopics);

  const stemsOfFilterWords = filterWords.map((w) => stemmer(w));
  const wordFreqLookupByStem = wordsWithoutStops
    // convert nonTopic words to lowercase
    .map((w) => w.toLowerCase())
    // add the topics back in
    .concat(topics)
    // remove user defined filter words
    .filter((w) => !filterWords.includes(w))
    // remove any short words
    .filter((w) => w.length > 1)
    // remove any punctuation
    .map(cleanupText)
    // convert to WordFrequencyLookup
    .reduce((acc: StemFrequencyLookup, word) => {
      const stem = stemmer(word);
      const prevCount = acc[stem]?.count || 0;

      // if the stem matches a filter word, don't add it
      if (stemsOfFilterWords.includes(stem)) {
        return acc;
      }

      return {
        ...acc,
        [stem]: {
          // use the first instance of the word as the word to display
          wordToDisplay: acc[stem]?.wordToDisplay ?? word,
          count: prevCount + 1,
        },
      };
    }, {});

  return (
    Object.values(wordFreqLookupByStem)
      // sort by count
      .sort((a, b) => b.count - a.count)
      // only keep the top 200 words
      .slice(0, MAX_WORDS)
      // convert to WordFrequencyLookup
      .reduce(
        (acc: WordFrequencyLookup, { wordToDisplay, count }) => ({
          ...acc,
          [wordToDisplay]: count,
        }),
        {}
      )
  );
}
