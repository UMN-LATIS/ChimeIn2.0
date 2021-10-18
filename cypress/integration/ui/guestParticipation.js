/// <reference types="Cypress" />

import api from "../api/index.js";

describe("participating as a guest", () => {
  let testChime;
  let testFolder;
  let testQuestion;

  beforeEach(() => {
    cy.refreshDatabase();
    cy.seed();

    // create a chime with folder and question
    // as a faculty member
    cy.login("faculty");
    api
      .createChime({ name: "Test Chime" })
      .then((chime) => {
        testChime = chime;
        return api.createFolder({
          chimeId: testChime.id,
          name: "Test Folder",
        });
      })
      .then((folder) => {
        testFolder = folder;
        return api.createQuestion({
          chimeId: testChime.id,
          folderId: testFolder.id,
          question_text: "Test Question",
          question_info: {
            question_type: "multiple_choice",
            question_responses: [
              {
                text: "A",
                correct: false,
              },
              {
                text: "B",
                correct: false,
              },
              {
                text: "C",
                correct: false,
              },
            ],
          },
        });
      })
      .then((question) => {
        testQuestion = question;
      });

    // logout, so that we enter tests as a
    // guest
    cy.logout();
  });

  it("shows no questions if none are open", () => {
    // guests should see no chime open
    cy.visit(`/join/${testChime.access_code}`)
      .get("#currentQuestions")
      .should("contain.text", "No Open Questions");
  });

  it("show open questions", () => {
    // open testQuestion
    cy.login("faculty");
    cy.visit(`/chime/${testChime.id}/folder/${testFolder.id}`);
    cy.get("[data-cy=toggle-open-question]").click();
    cy.logout();

    // check that chime is now available to guests
    cy.visit(`/join/${testChime.access_code}`)
      .get("#currentQuestions")
      .should("contain.text", testQuestion.text);

    cy.get("#radio1_0").click();
    cy.contains("Response Updated");
  });

  it("redirects to login when required", () => {
    //set chime to require login
    cy.login("faculty");
    cy.visit(`/chime/${testChime.id}`);
    cy.contains("Chime Settings").click();
    cy.contains("Require Login").click();
    cy.logout();

    // try accessing chime as guest
    cy.visit(`/join/${testChime.access_code}`);
    cy.contains("Login to Continue");
  });

  it("records a submitted response", () => {
    // open testQuestion
    cy.login("faculty");
    api.openQuestion({
      chimeId: testChime.id,
      folderId: testFolder.id,
      questionId: testQuestion.id,
    });
    cy.logout();

    // as a guest, record a response
    cy.visit(`/join/${testChime.access_code}`);

    cy.get("#radio1_0").click();
    cy.contains("Response Updated");

    // as faculty, verify that the response is recorded
    cy.login("faculty");
    api
      .getQuestion({
        folderId: testFolder.id,
        chimeId: testChime.id,
        questionId: testQuestion.id,
      })
      .then((question) => {
        const activeSession = question.sessions.find(
          (session) => session.id === question.current_session_id
        );

        expect(activeSession.responses.length).to.equal(1);
        expect(activeSession.responses[0].response_info).to.deep.equal({
          question_type: "multiple_choice",
          choice: "A",
        });
      });
  });
});
