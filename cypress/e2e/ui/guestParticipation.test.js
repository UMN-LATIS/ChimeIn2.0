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
      .createChimeFolderQuestion({
        chimeName: "Test Chime",
        folderName: "Test Folder",
        questionText: "Test Question",
        questionResponses: [
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
      })
      .then(({ chime, folder, question }) => {
        testChime = chime;
        testFolder = folder;
        testQuestion = question;
      });

    // logout, so that we enter tests as a
    // guest
    cy.logout();
  });

  it("shows no questions if none are open", () => {
    // guests should see no chime open
    cy.visit(`/join/${testChime.access_code}`)
      .get("#open-questions")
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
      .get("#open-questions")
      .should("contain.text", testQuestion.text);

    cy.get(":nth-child(1) > .form-check-label").click();
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

    cy.get(":nth-child(1) > .form-check-label").click();
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

  it("shows Answered Questions in reverse chronological order", () => {
    // login as faculty
    cy.login("faculty");

    // create a few more questions in your chime and folder
    cy.wrap([1, 2, 3]).each((i) => {
      api
        .createQuestion({
          folderId: testFolder.id,
          chimeId: testChime.id,
          question_text: `<p>Question ${i}</p>`,
          question_info: {
            question_type: "multiple_choice",
            question_responses: [
              {
                text: "This",
                correct: false,
              },
              {
                text: "That",
                correct: true,
              },
            ],
          },
        })
        .then((question) => {
          api.openQuestion({
            chimeId: testChime.id,
            folderId: testFolder.id,
            questionId: question.id,
          });
        });
    });

    // logout faculty
    cy.logout();

    // go to the participant page
    cy.visit(`/join/${testChime.access_code}`);

    // answer the questions in order 1, 2, 3
    cy.wrap([1, 2, 3]).each((i) => {
      // answer the question
      cy.contains(`Question ${i}`)
        .closest(".participant-prompt")
        .within(() => {
          cy.contains("This").click();

          // make sure the response is saved
          cy.contains("Saved").should("be.visible");
        });
    });
    // click the Answered Questions tab
    cy.contains("Answered Questions").click();

    cy.get("#answered-questions").should("be.visible");

    // verify that the questions are in reverse chronological order
    cy.get("#answered-questions .responseContainer").each(($el, index) => {
      expect($el).to.contain(`Question ${3 - index}`);
    });

    // verify that the questions remain in the same order after refreshing
    cy.reload();
    cy.contains("Answered Questions").click();
    cy.get("#answered-questions .responseContainer").each(($el, index) => {
      expect($el).to.contain(`Question ${3 - index}`);
    });
  });
});
