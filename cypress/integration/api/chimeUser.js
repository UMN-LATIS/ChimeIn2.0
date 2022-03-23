import { GET } from "./methods";

export function getChimeUsers({ chimeId }) {
  return cy
    .request({
      method: GET,
      url: `/api/chime/${chimeId}/users`,
    })
    .its("body");
}
