import nlp from "compromise";
import { removeStopwords } from "stopword";
import { stemmer } from "stemmer";
import { Response } from "../types";

interface BuildWordsArgs {
  responses: Response[];
  textProcessing: boolean;
  filterWords: string[];
}

interface LegacyWordListItem {
  name: string;
  stem: string;
  value: number;
}

export function legacyBuildWords({
  responses,
  textProcessing,
  filterWords,
}: BuildWordsArgs): LegacyWordListItem[] {
  const words = responses.map((r) => r.response_info.text).join("\n ");

  let filteredWords;
  let topics;
  if (textProcessing) {
    const doc = nlp(words);

    topics = doc.topics().out("array");

    filteredWords = words;

    for (let i = 0; i < topics.length; i++) {
      // remove topics words from our filtered words
      filteredWords = filteredWords.replace(
        new RegExp(
          "\\b" + topics[i].replace(/[.*+?^${}()|[\]\\]/g, "\\$&") + "\\b",
          "gi"
        ),
        ""
      );
    }
  } else {
    filteredWords = words;
    topics = [];
  }

  if (filteredWords.length == 0) {
    return [];
  }

  const wordsWithoutStops = removeStopwords(
    filteredWords.match(/"(.*?)"|\w+/g)
  );

  const finalizedWords = wordsWithoutStops
    .concat(topics)
    .filter((w) => !filterWords.includes(w));

  const groups = finalizedWords.reduce((acc: any[], w) => {
    if (w.length < 2) {
      return acc;
    }
    const stem = stemmer(w.toLowerCase().replace(/"/g, ""));
    const i = acc.findIndex((e) => e.stem === stem);

    if (i > -1) {
      acc[i].value += 1;
    } else {
      acc.push({
        name: w.replace(/"/g, ""),
        value: 1,
        stem: stem,
      });
    }

    return acc;
  }, []);

  const sortedArray = groups.sort((a, b) => {
    return b.value - a.value;
  });

  return sortedArray.slice(0, 200);
}
