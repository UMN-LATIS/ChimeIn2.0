import get from "lodash/get.js";

export const selectIsCanvasChime = (chime) =>
  !!get(chime, "lti_return_url", null);

export function selectCanvasCourseUrl(chime) {
  const url = get(chime, "lti_return_url", null);
  if (!url) return null;
  const found = url.match(/^(?<courseUrl>http.*\/courses\/\d+).*/);
  return found.groups.courseUrl || null;
}

export const selectJoinUrl = (chime) =>
  `${window.location.origin}/join/${chime.access_code}`;
