import { describe, expect, it } from "@jest/globals";
import { isCanvasChime, getCanvasCourseUrl } from "./canvasChime.js";

const linkedChime = {
  id: 1,
  access_code: "123456",
  name: "Canvas Course",
  lti_return_url: "https://canvas.umn.edu/courses/277604/assignments",
};

const unlinkedChime = {
  id: 2,
  access_code: "987654",
  name: "Test Chime",
  lti_return_url: null,
};

describe("isCanvasChime", () => {
  it("returns true if a given chime is linked to Canvas via LTI", () => {
    expect(isCanvasChime(linkedChime)).toBe(true);
  });

  it("returns false if a chime is not linked to Canvas", () => {
    expect(isCanvasChime(unlinkedChime)).toBe(false);
  });
});

describe("getCanvasCourseUrl", () => {
  it("gets course url from a given chime", () => {
    expect(getCanvasCourseUrl(linkedChime)).toBe(
      "https://canvas.umn.edu/courses/277604"
    );
  });
  it("returns null if chime is not linked to canvas", () => {
    expect(getCanvasCourseUrl(unlinkedChime)).toBeNull();
  });
});
