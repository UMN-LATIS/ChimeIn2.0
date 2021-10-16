const POST = "POST";
const GET = "GET";
const PATCH = "PATCH";
const PUT = "PUT";

export function getAllChimes() {
  return cy
    .request({
      method: GET,
      url: "/api/chime",
    })
    .its("body");
}

export function getChime(chimeId) {
  return cy
    .request({
      method: GET,
      url: `/api/chime/${chimeId}`,
    })
    .its("body");
}

export function createChime(name) {
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

export function updateChime(chimeId, updates) {
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
