import getWordFreqLookupNLP from "./getWordFreqLookupNLP";

describe("getNLPifiedWordList", () => {
  it("should return lookup of words with their frequency", () => {
    expect(getWordFreqLookupNLP("test test car cat")).toEqual({
      test: 2,
      car: 1,
      cat: 1,
    });
  });

  it("should filter stopwords", () => {
    const words1 = getWordFreqLookupNLP("I a the with an and to test");
    expect(words1).toEqual({
      test: 1,
    });
  });

  it("should treat topics as a single word", () => {
    expect(
      getWordFreqLookupNLP("James Bond bought a new bond in New York")
    ).toEqual({
      "James Bond": 1,
      buy: 1,
      new: 1,
      bond: 1,
      "New York": 1,
    });
  });

  it("should count duplicates", () => {
    expect(
      getWordFreqLookupNLP("New york, new york! It's a helluva town")
    ).toEqual({
      "New York": 2,
      helluva: 1,
      town: 1,
    });
  });

  it("shouldn't have punctuation", () => {
    const words1 = getWordFreqLookupNLP("This is a test.");
    expect(words1).toEqual({
      test: 1,
    });

    const words2 = getWordFreqLookupNLP("It's a test!");
    expect(words2).toEqual({
      test: 1,
    });

    const words3 = getWordFreqLookupNLP(`This is a "test?"`);
    expect(words3).toEqual({
      test: 1,
    });
  });

  it("should use infinites for verbs", () => {
    const words1 = getWordFreqLookupNLP(
      "I walked a walk with Walt Walker while walking the dog."
    );
    expect(words1).toEqual({
      walk: 3,
      "Walt Walker": 1,
      dog: 1,
    });
  });

  it("should filter words if provided", () => {
    const words1 = getWordFreqLookupNLP(
      "I walked a walk with Walt Walker while walking the dog.",
      ["dog"]
    );
    expect(words1).toEqual({
      walk: 3,
      "Walt Walker": 1,
    });
  });

  it("should handle topics that are substrings of other topics", () => {
    const responses = [
      "Molly is a dog",
      "Molly McFadden went to New York",
      "Colin McFadden went to New York",
    ];

    expect(getWordFreqLookupNLP(responses.join("\n"))).toEqual({
      "Molly McFadden": 1,
      "Colin McFadden": 1,
      "New York": 2,
      Molly: 1,
      dog: 1,
      go: 2,
    });
  });
});
