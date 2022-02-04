import get from "lodash/get.js";

export const selectIsCanvasChime = (chime) => {
  const canvasChimeUrl = get(chime, "lti_return_url", null);
  return Boolean(canvasChimeUrl);
};

export function selectCanvasCourseUrl(chime) {
  const url = get(chime, "lti_return_url", null);
  if (!url) return null;
  const found = url.match(/^(?<courseUrl>http.*\/courses\/\d+).*/);
  return found ? found.groups.courseUrl : null;
}

export function selectLtiReturnUrl(chime) {
  const urlString = get(chime, "lti_return_url", null);
  return urlString || null;
}

export const selectJoinUrl = (chime) =>
  `${window.location.origin}/join/${chime.access_code}`;
