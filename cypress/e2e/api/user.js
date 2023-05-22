import { GET } from "./methods";

export function getCurrentUser() {
  return cy
    .request({
      method: GET,
      url: "/api/users/self",
    })
    .its("body");
}
