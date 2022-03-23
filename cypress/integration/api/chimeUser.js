import { GET } from "./methods";

export function getChimeUsers({ chimeId }, options) {
  return cy
    .request({
      method: GET,
      url: `/api/chime/${chimeId}/users`,
      ...options,
    })
    .then((req) => {
      if (req.status !== 200) return req;
      return req.body;
    });
}

export function updateChimeUser(
  { chimeId, userId, permissionNumber },
  options
) {
  return cy.csrfToken().then((_token) =>
    cy
      .request({
        method: "PUT",
        url: `/api/chime/${chimeId}/users/${userId}`,
        body: {
          permission_number: permissionNumber,
          _token,
        },
        ...options,
      })
      .then((req) => {
        if (req.status !== 200) return req;
        return req.body;
      })
  );
}

export function removeChimeUser({ chimeId, userId }, opts) {
  return cy.csrfToken().then((_token) => {
    cy.request({
      method: "DELETE",
      url: `/api/chime/${chimeId}/users/${userId}`,
      body: {
        _token,
      },
      ...opts,
    }).then((req) => {
      if (req.status !== 200) return req;
      return req.body;
    });
  });
}
