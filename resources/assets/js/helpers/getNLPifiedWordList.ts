import nlp from "compromise";

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

  const textWithouTopics = text.replace(anyWordMatch, "");

  // do a bit of cleanup in case we have multiple spaces,
  // weird punctuation, etc.
  return cleanupText(textWithouTopics);
}

export default (text: string): string[] => {
  const normalizedText = nlp(text).normalize({
    possessives: true,
    plurals: true,
    verbs: true, // e.g. "walked" => "walk"
  });

  // get a list of topic words like ["James Bond", "New York"]
  const topics = normalizedText
    .topics()
    .out("array")
    // compromise seems to clobber the case of the words
    // so we need to manually title case topics
    .map(toTitleCase);

  const textWithoutTopics = removeWordsFromText(
    normalizedText.out("text"),
    topics
  );

  const nonTopicWords = nlp(textWithoutTopics)
    .normalize({
      possessives: true,
      plurals: true,
      verbs: true, // e.g. "walked" => "walk"
    })
    .terms()
    .out("array");
  return [...topics, ...nonTopicWords];
};
