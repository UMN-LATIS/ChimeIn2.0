import { GET, POST, PATCH, DELETE } from "./methods";

export function getAllChimes() {
  return cy
    .request({
      method: GET,
      url: "/api/chime",
    })
    .its("body");
}

export function getChime({ chimeId }) {
  if (!chimeId) throw Error("chimeId is required");

  return cy
    .request({
      method: GET,
      url: `/api/chime/${chimeId}`,
    })
    .its("body");
}

export function createChime({ name }) {
  if (!name) throw Error("name is required");

  return cy.csrfToken().then((_token) => {
    return cy
      .request({
        method: POST,
        url: "/api/chime",
        body: {
          name,
          _token,
        },
      })
      .its("body");
  });
}

export function updateChime({ chimeId, ...updates }) {
  if (!chimeId) throw Error("chimeId is required");

  return cy.csrfToken().then((_token) => {
    return cy
      .request({
        method: POST,
        url: `/api/chime/${chimeId}`,
        body: {
          _token,
          _method: PATCH,
          ...updates,
        },
      })
      .its("body");
  });
}

export function deleteChime({ chimeId }) {
  if (!chimeId) throw Error("chimeId is required");

  return cy.csrfToken().then((_token) => {
    return cy
      .request({
        method: DELETE,
        url: `/api/chime/${chimeId}`,
        body: {
          _token,
        },
      })
      .its("body");
  });
}
