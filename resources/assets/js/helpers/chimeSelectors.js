export const selectIsCanvasChime = (chime) => !!chime.lti_return_url;

export function selectCanvasCourseUrl(chime) {
  const url = chime.lti_return_url;
  if (!url) return null;
  const found = url.match(/^(?<courseUrl>http.*\/courses\/\d+).*/);
  return found.groups.courseUrl || null;
}

export const selectJoinUrl = (chime) =>
  `${global.location.origin}/join/${chime.access_code}`;
