export const isCanvasChime = (chime) => !!chime.lti_return_url;

export function getCanvasCourseUrl(chime) {
  const url = chime.lti_return_url;
  if (!url) return null;
  const found = url.match(/^(?<courseUrl>http.*\/courses\/\d+).*/);
  return found.groups.courseUrl || null;
}
