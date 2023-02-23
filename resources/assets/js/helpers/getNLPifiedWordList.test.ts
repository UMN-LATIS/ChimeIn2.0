import getNLPifiedWordList from "./getNLPifiedWordList";

describe("getNLPifiedWordList", () => {
  it("should return an array of words", () => {
    const { topics, nonTopics } = getNLPifiedWordList("This is a test");
    expect(topics).toEqual([]);
    expect(nonTopics).toEqual(["this", "is", "a", "test"]);
  });

  it("should return topics as a single word", () => {
    const { topics, nonTopics } = getNLPifiedWordList(
      "James Bond bought a new bond in New York"
    );
    expect(topics).toEqual(["James Bond", "New York"]);
    expect(nonTopics).toEqual(["bought", "a", "new", "bond", "in"]);
  });

  it("should preserve duplicates", () => {
    const { topics, nonTopics } = getNLPifiedWordList(
      "New york, new york! It's a helluva town"
    );
    expect(topics).toEqual(["New York", "New York"]);

    expect(nonTopics).toEqual(["it", "is", "a", "helluva", "town"]);
  });

  it("should remove punctuation", () => {
    const words1 = getNLPifiedWordList("This is a test.");
    expect(words1.topics).toEqual([]);
    expect(words1.nonTopics).toEqual(["this", "is", "a", "test"]);

    const words2 = getNLPifiedWordList("It's a test!");
    expect(words2.topics).toEqual([]);
    expect(words2.nonTopics).toEqual(["it", "is", "a", "test"]);

    const words3 = getNLPifiedWordList(`This is a "test?"`);
    expect(words3.topics).toEqual([]);
    expect(words3.nonTopics).toEqual(["this", "is", "a", "test"]);

    const words4 = getNLPifiedWordList("This is a ... ,, ././. test...");
    expect(words4.topics).toEqual([]);
    expect(words4.nonTopics).toEqual(["this", "is", "a", "test"]);
  });
});
