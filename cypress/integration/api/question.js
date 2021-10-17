import { GET, POST, PATCH, DELETE, PUT } from "./methods";

export function getAllQuestionsInFolder({ chimeId, folderId }) {
  if (!chimeId) throw Error("chimeId is required");
  if (!folderId) throw Error("folderId is required");

  return cy
    .request({
      method: GET,
      url: `/api/chime/${chimeId}/folder/${folderId}/includeQuestions`,
    })
    .its("body.questions");
}

export function createQuestion({ chimeId, folderId, ...question }) {
  if (!chimeId) throw Error("chimeId is required");
  if (!folderId) throw Error("folderId is required");

  return cy.csrfToken().then((_token) => {
    return cy
      .request({
        method: POST,
        url: `/api/chime/${chimeId}/folder/${folderId}`,
        body: {
          _token,
          folder_id: folderId,
          ...question,
        },
      })
      .its("body");
  });
}

export function openQuestion({ chimeId, folderId, questionId, ...question }) {
  if (!chimeId) throw Error("chimeId is required");
  if (!folderId) throw Error("folderId is required");
  if (!questionId) throw Error("questionId is required");

  return cy.csrfToken().then((_token) => {
    return cy
      .request({
        method: POST,
        url: `/api/chime/${chimeId}/folder/${folderId}/question/${questionId}`,
        body: {
          _token,
          ...question,
        },
      })
      .its("body");
  });
}
