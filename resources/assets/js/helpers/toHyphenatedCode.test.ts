import { describe, expect, it } from "@jest/globals";
import toHyphenatedCode from "./toHyphenatedCode";

describe("toHyphenatedCode", () => {
  it("adds a hyphen in a 6-digit access code", () => {
    expect(toHyphenatedCode(123456)).toBe("123-456");
    expect(toHyphenatedCode("123456")).toBe("123-456");
  });
  it("adds a hyphen every 3 digits", () => {
    expect(toHyphenatedCode(123)).toBe("123");
    expect(toHyphenatedCode(1234)).toBe("123-4");
    expect(toHyphenatedCode(12345)).toBe("123-45");
    expect(toHyphenatedCode(1234567890)).toBe("123-456-789-0");
  });
});
