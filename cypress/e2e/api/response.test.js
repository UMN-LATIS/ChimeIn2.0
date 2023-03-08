import api from "./index";

describe("Response API", () => {
  beforeEach(() => {
    cy.refreshDatabase();
    cy.seed();
    cy.login("faculty");
  });

  it("limits the size of a text response", () => {
    let testChime;
    let testFolder;
    let testQuestion;
    let testSessionId;
    const MAX_RESPONSE_LENGTH = 10000;

    // create an open response question
    api
      .createChime({ name: "Test Chime" })
      .then((chime) => {
        testChime = chime;
        return api.createFolder({ chimeId: chime.id, name: "Test Folder" });
      })
      .then((folder) => {
        testFolder = folder;
        return api.createQuestion({
          chimeId: testChime.id,
          folderId: testFolder.id,
          question_text: "Open response question",
          question_info: {
            question_type: "free_response",
          },
        });
      })
      .then((question) => {
        testQuestion = question;
        api.openQuestion({
          chimeId: testChime.id,
          folderId: testFolder.id,
          questionId: testQuestion.id,
        });
      })
      .then(() => {
        // logout presenter
        cy.logout();

        cy.visit(`/join/${testChime.access_code}`);

        return api.getCurrentSessionForQuestion({
          chimeId: testChime.id,
          questionId: testQuestion.id,
        });
      })
      .then((session) => {
        testSessionId = session.id;

        // create a response that is too long
        const longResponse = "a".repeat(1.5 * MAX_RESPONSE_LENGTH);
        return api.createResponse({
          chimeId: testChime.id,
          sessionId: testSessionId,
          response_info: {
            text: `<p>${longResponse}</p>`,
          },
        });
      })
      .then((response) => {
        // verify that the server truncated the response
        cy.wrap(response).should((res) => {
          expect(res.response_info.text.length).to.equal(MAX_RESPONSE_LENGTH);
        });
      });
  });
});
