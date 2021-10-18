import { getChime } from "./chime";
import { GET, POST, PATCH, DELETE, PUT } from "./methods";

export function getAllFolders({ chimeId }) {
  if (!chimeId) throw Error("chimeId is required");

  return getChime({ chimeId }).its("folders");
}

export function createFolder({ chimeId, name, folder_name, ...rest }) {
  if (!chimeId) throw Error("chimeId is required");
  if (!name && !folder_name) throw Error("folder_name (or name) is required");

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

export function getFolder({ chimeId, folderId }) {
  if (!chimeId) throw Error("chimeId is required");
  if (!folderId) throw Error("folderId is required");

  return cy.csrfToken().then((_token) => {
    return cy
      .request({
        method: GET,
        url: `/api/chime/${chimeId}/folder/${folderId}`,
      })
      .its("body");
  });
}

export function updateFolder({
  chimeId,
  folderId,
  name,
  folder_name,
  ...rest
}) {
  if (!chimeId) throw Error("chimeId is required");
  if (!folderId) throw Error("folderId is required");

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

export function deleteFolder({ chimeId, folderId }) {
  if (!chimeId) throw Error("chimeId is required");
  if (!folderId) throw Error("folderId is required");

  return cy.csrfToken().then((_token) => {
    return cy
      .request({
        method: DELETE,
        url: `/api/chime/${chimeId}/folder/${folderId}`,
        body: {
          _token,
        },
      })
      .its("body");
  });
}
