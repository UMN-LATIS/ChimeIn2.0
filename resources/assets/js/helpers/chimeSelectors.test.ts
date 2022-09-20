import { describe, expect, it } from "@jest/globals";
import {
  selectIsCanvasChime,
  selectCanvasCourseUrl,
  selectJoinUrl,
} from "./chimeSelectors";
import type { Chime } from "../types";

const linkedChime = {
  id: 1,
  access_code: "123456",
  name: "Canvas Course",
  lti_return_url: "https://canvas.umn.edu/courses/277604/assignments",
} as Chime;

const unlinkedChime = {
  id: 2,
  access_code: "987654",
  name: "Test Chime",
  lti_return_url: null,
} as Chime;

describe("selectIsCanvasChime", () => {
  it("returns true if a given chime is linked to Canvas via LTI", () => {
    expect(selectIsCanvasChime(linkedChime)).toBe(true);
  });

  it("returns false if a chime is not linked to Canvas", () => {
    expect(selectIsCanvasChime(unlinkedChime)).toBe(false);
  });
});

describe("selectCanvasCourseUrl", () => {
  it("gets course url from a given chime", () => {
    expect(selectCanvasCourseUrl(linkedChime)).toBe(
      "https://canvas.umn.edu/courses/277604"
    );
  });
  it("returns null if chime is not linked to canvas", () => {
    expect(selectCanvasCourseUrl(unlinkedChime)).toBeNull();
  });
});

describe("selectJoinUrl", () => {
  it("gets the full join URL from a given chime", () => {
    expect(selectJoinUrl(linkedChime)).toBe("http://localhost/join/123456");
    expect(selectJoinUrl(unlinkedChime)).toBe("http://localhost/join/987654");
  });
});
