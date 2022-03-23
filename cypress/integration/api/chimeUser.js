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
  getChimeUsers({ chimeId })
    .then((users) => {
      console.log(users);
      const user = users.find((u) => u.id === userId);
      const updatedUser = { ...user, permission_number: permissionNumber };
      return [...users.filter((u) => u.id !== userId), updatedUser];
    })
    .then((users) => {
      console.log(users);
      return cy.csrfToken().then((_token) => {
        // TODO: create separate endpoint for updating a chimeUser
        // instead of requiring updating all chime users
        return cy.request({
          method: "PUT",
          url: `/api/chime/${chimeId}/users`,
          body: {
            users,
            _token,
          },
        });
      });
    });
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
