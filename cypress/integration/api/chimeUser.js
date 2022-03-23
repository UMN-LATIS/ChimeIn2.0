import { GET } from "./methods";

export function getChimeUsers({ chimeId }) {
  return cy
    .request({
      method: GET,
      url: `/api/chime/${chimeId}/users`,
    })
    .its("body");
}

export function updateChimeUser({ chimeId, userId, permissionNumber }) {
  return cy.csrfToken().then((_token) =>
    cy
      .request({
        method: "PUT",
        url: `/api/chime/${chimeId}/users/${userId}`,
        body: {
          permission_number: permissionNumber,
          _token,
        },
      })
      .its("body")
  );
}

// export function removeChimeUser({ chimeId, userId }) {
//   return cy.csrfToken().then((_token) => {
//     cy.request({
//       method: DELETE,
//       url: `/api/chime/${chimeId}/users/${userId}`,
//       body: {
//         _token,
//       },
//     }).its("body");
//   });
// }
