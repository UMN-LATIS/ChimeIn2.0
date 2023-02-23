import getNLPifiedWordList from "./getNLPifiedWordList";

describe("getNLPifiedWordList", () => {
  it("should return an array of words", () => {
    const words = getNLPifiedWordList("This is a test");
    expect(words).toEqual(["this", "is", "a", "test"]);
  });

  it("should return topics as a single word", () => {
    const words = getNLPifiedWordList(
      "James Bond bought a new bond in New York"
    );
    expect(words).toEqual([
      "James Bond",
      "New York",
      "bought",
      "a",
      "new",
      "bond",
      "in",
    ]);
  });

  it("should preserve duplicates", () => {
    expect(
      getNLPifiedWordList("New york, new york! It's a helluva town")
    ).toEqual(["New York", "New York", "it", "is", "a", "helluva", "town"]);
  });

  it("should remove punctuation", () => {
    expect(getNLPifiedWordList("This is a test.")).toEqual([
      "this",
      "is",
      "a",
      "test",
    ]);
    expect(getNLPifiedWordList("It's a test!")).toEqual([
      "it",
      "is",
      "a",
      "test",
    ]);
    expect(getNLPifiedWordList(`This is a "test?"`)).toEqual([
      "this",
      "is",
      "a",
      "test",
    ]);
    expect(getNLPifiedWordList("This is a ... ,, ././. test...")).toEqual([
      "this",
      "is",
      "a",
      "test",
    ]);
  });
});
