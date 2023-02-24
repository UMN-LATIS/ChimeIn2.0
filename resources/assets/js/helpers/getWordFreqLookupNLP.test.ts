// import getWordFreqLookupNLP from "./getWordFreqLookupNLP";
import getWordFreqLookupNLP from "./legacyBuildWords";

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
      bought: 1,
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

  it("should use first occurance of a stem's word", () => {
    const words1 = getWordFreqLookupNLP(
      "I walked a walk with Walt Walker while walking the dog."
    );
    expect(words1).toEqual({
      walked: 3,
      "Walt Walker": 1,
      dog: 1,
    });
  });

  it("should normalize non-topic words to lowercase", () => {
    expect(getWordFreqLookupNLP("Walk walks WALKING wAlKeD")).toEqual({
      walk: 4,
    });
  });

  it("should filter words if provided", () => {
    const phrase = "I walked a walk with Walt Walker while walking the dog.";
    expect(getWordFreqLookupNLP(phrase, ["dog"])).toEqual({
      walked: 3,
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
      went: 2,
    });
  });

  it("should count words with the same stem as a single item", () => {
    expect(
      getWordFreqLookupNLP(
        "The President talks. She talked. They are talking. I talk."
      )
    ).toEqual({
      president: 1,
      she: 1,
      talks: 4,
    });

    expect(
      getWordFreqLookupNLP("Dancing dancers danced drunkenly. Let's dance.")
    ).toEqual({
      dancing: 3, // dancing, dance, danced have stem `danc`
      dancers: 1, // has stem `dancer` via stemmer
      let: 1,
      drunkenly: 1,
    });

    expect(getWordFreqLookupNLP("Coloring colors are colored dog")).toEqual({
      coloring: 3,
      dog: 1,
    });
  });

  it("should not count words opposite meanings but the same stem as a single item", () => {
    expect(
      getWordFreqLookupNLP(
        "comfort comforting discomfort discomforted discomforting"
      )
    ).toEqual({
      comfort: 2,
      discomfort: 3,
    });
  });

  it('should treat "Quoted Phrases" as a single item', () => {
    const phraseWithDoubleQuote =
      'The "most important" thing is time. But "most important" is relative.';
    const phraseWithSingleQuote =
      "The 'most important' thing is time. But 'most important' is relative.";
    const expected = {
      "most important": 2,
      thing: 1,
      time: 1,
      relative: 1,
    };

    expect(getWordFreqLookupNLP(phraseWithDoubleQuote)).toEqual(expected);
    expect(getWordFreqLookupNLP(phraseWithSingleQuote)).toEqual(expected);
  });
  it("should only return the top 200 most common words", () => {
    const phrase = Array.from({ length: 300 }, (_, i) => `word${i}`).join(" ");
    expect(Object.keys(getWordFreqLookupNLP(phrase)).length).toEqual(200);
  });
});
