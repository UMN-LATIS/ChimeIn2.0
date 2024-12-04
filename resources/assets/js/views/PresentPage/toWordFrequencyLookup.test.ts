import { describe, expect, it } from "@jest/globals";
import toWordFrequencyLookup, {
  removeQuotedStrings,
  getQuotedStrings,
  getSingleWords,
  getWordsFromText,
} from "./toWordFrequencyLookup";

describe("toWordFrequencyLookup", () => {
  it("gives an object with the frequency of each word in the text", () => {
    const responseTexts = [
      "rainbow willow",
      "willow spruce willow",
      "pine oak maple",
    ];

    const result = toWordFrequencyLookup(responseTexts);

    expect(result).toEqual({
      rainbow: 1,
      willow: 3,
      spruce: 1,
      pine: 1,
      oak: 1,
      maple: 1,
    });
  });

  it("ignores null response texts", () => {
    const responseTexts = ["rainbow willow", null, "willow spruce willow"];

    const result = toWordFrequencyLookup(responseTexts);

    expect(result).toEqual({
      rainbow: 1,
      willow: 3,
      spruce: 1,
    });
  });

  it('removes stopwords like "the" and "and"', () => {
    const responseTexts = [
      "the rainbow willow",
      "willow and spruce or willow",
      "the pine, the oak, the maple",
    ];

    const result = toWordFrequencyLookup(responseTexts);

    expect(result).toEqual({
      rainbow: 1,
      willow: 3,
      spruce: 1,
      pine: 1,
      oak: 1,
      maple: 1,
    });
  });

  it("removes simple filtered words", () => {
    const responseTexts = [
      "rainbow willow",
      "willow spruce willow",
      "pine oak maple",
    ];

    const result = toWordFrequencyLookup(responseTexts, ["willow"]);

    expect(result).toEqual({
      rainbow: 1,
      spruce: 1,
      pine: 1,
      oak: 1,
      maple: 1,
    });
  });

  it("treats quoted strings as single words", () => {
    const responseTexts = ["rainbow willow", '"willow spruce" willow'];

    const result = toWordFrequencyLookup(responseTexts);

    expect(result).toEqual({
      rainbow: 1,
      willow: 2,
      "willow spruce": 1,
    });
  });

  it("ignores filtered words in quoted strings", () => {
    const responseTexts = ["rainbow willow", '"willow spruce" willow'];

    const result = toWordFrequencyLookup(responseTexts, ["willow"]);

    expect(result).toEqual({
      rainbow: 1,
      "willow spruce": 1,
    });
  });

  it("removes empty strings", () => {
    const responseTexts = ["rainbow willow", "", "spruce willow"];

    const result = toWordFrequencyLookup(responseTexts);

    expect(result).toEqual({
      rainbow: 1,
      willow: 2,
      spruce: 1,
    });
  });

  it("filters quoted strings", () => {
    const responseTexts = [
      "rainbow willow",
      '"willow spruce" willow',
      '"willow spruce"',
    ];

    const result = toWordFrequencyLookup(responseTexts, ["willow spruce"]);

    expect(result).toEqual({
      rainbow: 1,
      willow: 2,
    });
  });
});

describe("removeQuotedStrings", () => {
  it("removes quoted strings from text", () => {
    const text = 'rainbow "willow spruce" willow';
    expect(removeQuotedStrings(text)).toBe("rainbow willow");
  });

  it("removes multiple quoted strings from text", () => {
    const text = 'rainbow "willow spruce" willow "pine oak maple"';
    expect(removeQuotedStrings(text)).toBe("rainbow willow");
  });
});

describe("getQuotedStrings", () => {
  it("returns an array of quoted strings", () => {
    const text = 'rainbow "willow spruce" willow "pine oak maple" maple';

    expect(getQuotedStrings(text)).toEqual(["willow spruce", "pine oak maple"]);
  });
});

describe("getSingleWords", () => {
  it("returns an array of single words", () => {
    const text = 'rainbow "willow spruce" willow "pine oak maple" maple';

    expect(getSingleWords(text)).toEqual([
      "rainbow",
      "willow",
      "spruce",
      "willow",
      "pine",
      "oak",
      "maple",
      "maple",
    ]);
  });

  it("ignores quotes", () => {
    const text = 'rainbow "willow spruce" willow "pine oak maple" maple';

    expect(getSingleWords(text)).toEqual([
      "rainbow",
      "willow",
      "spruce",
      "willow",
      "pine",
      "oak",
      "maple",
      "maple",
    ]);
  });

  it("does not split on '&'", () => {
    expect(getSingleWords("S&P500")).toEqual(["s&p500"]);
  });
});

describe("getWordsFromText", () => {
  it("returns an array of single words and quoted strings", () => {
    const text = 'rainbow "willow spruce" willow "pine oak maple" maple';

    expect(getWordsFromText(text)).toEqual([
      "willow spruce",
      "pine oak maple",
      "rainbow",
      "willow",
      "maple",
    ]);
  });

  it("handles extra spaces", () => {
    const text = "   maple    spruce  willow  ";
    expect(getWordsFromText(text)).toEqual(["maple", "spruce", "willow"]);
  });

  it("filters out given words", () => {
    const text = 'rainbow "willow spruce" willow "pine oak maple" maple';

    expect(getWordsFromText(text, ["willow"])).toEqual([
      "willow spruce",
      "pine oak maple",
      "rainbow",
      "maple",
    ]);
  });
});
