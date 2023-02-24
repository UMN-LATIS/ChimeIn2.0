import nlp from "compromise";
import { removeStopwords } from "stopword";
import { stemmer } from "stemmer";
import { Response } from "../types";

interface BuildWordsArgs {
  responses: Response[];
  textProcessing: boolean;
  filterWords: string[];
}

interface WordListItem {
  name: string;
  stem: string;
  value: number;
}

export function legacyBuildWords({
  responses,
  textProcessing,
  filterWords,
}: BuildWordsArgs): WordListItem[] {
  const words = responses.map((r) => r.response_info.text).join("\n ");

  let filteredWords;
  let topics;
  if (textProcessing) {
    const doc = nlp(words);

    // sort so that longest is first in case topic is a substring
    // of another topic
    topics = doc
      .topics()
      .out("array")
      .sort((a, b) => b.length - a.length);

    // this will be the string of all responses without the topics
    filteredWords = words;

    for (let i = 0; i < topics.length; i++) {
      // remove topics words from our filtered words

      // escapes any special chars from the topic that might mess up the regex
      const escapedTopic = topics[i].replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
      filteredWords = filteredWords.replace(
        new RegExp("\\b" + escapedTopic + "\\b", "gi"),
        "" // remove the topic
      );

      // at this point, filteredWords hold all the non-topics
    }
  } else {
    filteredWords = words;
    topics = [];
  }

  // BUG: Topics
  if (filteredWords.length == 0 && topics.length == 0) {
    return [];
  }

  // words is a single string of all the responses
  // we need to break into tokens, so that we keep things together like "New York" and "New York City" as one word
  const wordsWithoutStops = removeStopwords(
    // keep quote stuff together, but don't grab the quotes
    filteredWords.match(/"(.*?)"|\w+/g)
  );

  // combine the topics and the our nonTopic words into one array
  // (non topic words could also be "Quoted String" ..)
  const finalizedWords = wordsWithoutStops
    .concat(topics)
    // remove user defined filter words
    .filter((w) => !filterWords.includes(w));

  const groups: WordListItem[] = finalizedWords.reduce((acc: any[], w) => {
    // don't include single letter words or numbers
    if (w.length < 2) {
      return acc;
    }

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
  return sortedArray.slice(0, 200);
}
