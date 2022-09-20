// import get from "lodash/get.js";

import { Chime } from "../types";

export const selectIsCanvasChime = (chime: Chime): boolean =>
  Boolean(chime?.lti_return_url);

export function selectCanvasCourseUrl(chime: Chime): string | null {
  const url = chime?.lti_return_url ?? null;
  if (!url) return null;

  const found = url.match(/^(?<courseUrl>http.*\/courses\/\d+).*/);
  return found?.groups?.courseUrl ?? null;
}

export function selectLtiReturnUrl(chime: Chime): string | null {
  return chime?.lti_return_url ?? null;
}

export const selectJoinUrl = (chime: Chime): string =>
  `${window.location.origin}/join/${chime.access_code}`;
