import { GET, PUT } from "./methods";

export function getCurrentSessionForQuestion({ chimeId, questionId }) {
  return cy.csrfToken().then((_token) => {
    return cy
      .request({
        method: GET,
        url: `/api/chime/${chimeId}/openQuestions`,
        body: {
          _token,
        },
      })
      .its("body.sessions")
      .then((sessions) => {
        return sessions.find((session) => session.question.id === questionId);
      });
  });
}

export async function createResponse({ chimeId, sessionId, response_info }) {
  const url = `/api/chime/${chimeId}/session/${sessionId}/response`;

  return cy.csrfToken().then((_token) => {
    return cy
      .request({
        method: PUT,
        url,
        body: {
          _token,
          response_info,
        },
      })
      .its("body");
  });
}
