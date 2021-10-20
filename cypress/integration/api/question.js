import { GET, POST, DELETE, PUT } from "./methods";

export function getAllQuestions({ chimeId, folderId }) {
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

export function openQuestion({ chimeId, folderId, questionId }) {
  if (!chimeId) throw Error("chimeId is required");
  if (!folderId) throw Error("folderId is required");
  if (!questionId) throw Error("questionId is required");

  return cy.csrfToken().then((_token) => {
    return cy.request({
      method: POST,
      url: `/api/chime/${chimeId}/folder/${folderId}/question/${questionId}`,
      body: {
        _token,
      },
    });
  });
}

export function closeQuestion({ chimeId, folderId, questionId }) {
  if (!chimeId) throw Error("chimeId is required");
  if (!folderId) throw Error("folderId is required");
  if (!questionId) throw Error("questionId is required");

  return cy.csrfToken().then((_token) => {
    return cy
      .request({
        method: POST,
        url: `/api/chime/${chimeId}/folder/${folderId}/question/${questionId}/stopSession`,
        body: {
          _token,
          _method: PUT,
        },
      })
      .its("body");
  });
}

export function getQuestion({ chimeId, folderId, questionId }) {
  if (!chimeId) throw Error("chimeId is required");
  if (!folderId) throw Error("folderId is required");
  if (!questionId) throw Error("questionId is required");

  return getAllQuestions({ chimeId, folderId }).then((questions) =>
    questions.find((q) => q.id === questionId)
  );
}

export function updateQuestion({ chimeId, folderId, questionId, ...question }) {
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
          _method: PUT,
          folder_id: folderId,
          ...question,
        },
      })
      .its("body");
  });
}

export function deleteQuestion({ chimeId, folderId, questionId }) {
  if (!chimeId) throw Error("chimeId is required");
  if (!folderId) throw Error("folderId is required");
  if (!questionId) throw Error("questionId is required");

  return cy.csrfToken().then((_token) => {
    return cy.request({
      method: DELETE,
      url: `/api/chime/${chimeId}/folder/${folderId}/question/${questionId}`,
      body: {
        _token,
      },
    });
  });
}
