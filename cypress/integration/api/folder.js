import { getChime } from "./chime";
import { GET, POST, PATCH, DELETE, PUT } from "./methods";

export function getAllFoldersInChime(chimeId) {
  return getChime(chimeId).its("folders");
}

export function createFolderInChime(chimeId, { name, folder_name, ...rest }) {
  return cy.csrfToken().then((_token) => {
    return cy
      .request({
        method: POST,
        url: `/api/chime/${chimeId}/folder`,
        body: {
          _token,
          folder_name: folder_name || name,
          ...rest,
        },
      })
      .its("body");
  });
}

export function getFolderInChime(chimeId, folderId) {
  return cy.csrfToken().then((_token) => {
    return cy
      .request({
        method: GET,
        url: `/api/chime/${chimeId}/folder/${folderId}`,
      })
      .its("body");
  });
}

export function updateFolderInChime(
  chimeId,
  folderId,
  { name, folder_name, ...rest }
) {
  return cy.csrfToken().then((_token) => {
    return cy
      .request({
        method: POST,
        url: `/api/chime/${chimeId}/folder/${folderId}`,
        body: {
          _token,
          _method: PUT,
          folder_name: folder_name || name,
          ...rest,
        },
      })
      .its("body");
  });
}
