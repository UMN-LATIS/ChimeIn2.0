/// <reference types="Cypress" />

import api from "./api/index.js";

describe("Chime", () => {
  beforeEach(() => {
    cy.refreshDatabase();
    cy.seed();
  });

  context("when unauthenticated", () => {
    beforeEach(() => {
      cy.visit("/");
    });

    it("does not allow adding a chime", () => {
      cy.contains("Add a chime").should("not.exist");
    });
  });

  context("when authenticated as faculty", () => {
    beforeEach(() => {
      cy.login("faculty");
      cy.visit("/");
    });

    it("creates a new chime, folder, and multiple choice question", () => {
      // create a chime
      cy.get("main")
        .contains("Add a Chime")
        .click();
      cy.get("#chime_name_input").type("Test Chime");
      cy.get("#joinInstructions").check();
      cy.get("[data-cy=create-chime-button]").click();
      cy.url().should("match", /chime\/[0-9]+$/);
      cy.get("h1").should("contain", "Test Chime");

      // create a folder
      cy.get("#createFolder").type("Test Folder 1");
      cy.get("[data-cy=create-folder-button]")
        .contains("Create")
        .click();

      // go into the folder
      cy.get("[data-cy=folder-card]")
        .contains("Test Folder 1")
        .click();
      cy.url().should("match", /chime\/[0-9]+\/folder\/[0-9]+$/);
      cy.get("h1").should("contain", "Test Folder 1");

      // create a question
      cy.get("[data-cy=new-question-button]").click();
      cy.get("h3").should("contain", "Add a Question");

      // select multiple choice (not a true select)
      cy.get("[data-cy=question-type]").type("Multiple Choice{enter}");

      // Fill in the question
      cy.get("[data-cy=question-editor]").type("What is your favorite color?");

      // add multiple choice options
      cy.get("[data-cy=response-text-input]").type("Red{enter}");
      cy.get("[data-cy=response-text-input]").type("Green{enter}");
      cy.get("[data-cy=response-text-input]").type("Blue{enter}");

      cy.get("[data-cy=response-choice-list] li")
        .should("have.length", 3)
        .should("contain", "Red")
        .should("contain", "Green")
        .should("contain", "Blue");

      cy.contains("Save").click();

      // check that the question was created
      cy.get("[data-cy=question-list] li").should(
        "contain",
        "What is your favorite color?"
      );

      // open the question
      cy.get("[data-cy=toggle-open-question]").click();

      let testChime;
      let testFolder;
      api
        .getAllChimes()
        .then((chimes) => {
          testChime = chimes[0];
          return api.getAllFolders({ chimeId: testChime.id });
        })
        .then((folders) => {
          testFolder = folders[0];
          return api.getAllQuestions({
            chimeId: testChime.id,
            folderId: testFolder.id,
          });
        })
        .then((questions) => {
          const testQuestion = questions[0];

          // current_session_id is set if and only if question is open
          expect(testQuestion.current_session_id).to.be.greaterThan(0);
        });
    });

    it("show a spinner while waiting for chime data to load", () => {
      // set up a delayed response time
      cy.delayResponse("/api/chime*", 1000);

      // create a chime
      cy.contains("Add a Chime").click();
      cy.get("#chime_name_input").type("Test Chime");
      cy.get("[data-cy=create-chime-button]").click();

      // expect a spinner while loading
      cy.get(".spinner");

      // and then expect Test Chime
      cy.get("main h1").should("contain.text", "Test Chime");
    });
  });

  context("when user is a guest participant", () => {
    // as faculty create chime with a question
    let testChime;
    let testFolder;
    let testQuestion;
    beforeEach(() => {
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

      cy.logout();
    });

    it("shows no questions to participants if none are open", () => {
      // guests should see no chime open
      cy.visit(`/join/${testChime.access_code}`)
        .get("#currentQuestions")
        .should("contain.text", "No Open Questions");
    });

    it("show open questions to guests", () => {
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

    it("does not show open questions that require a login", () => {
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

    it("records a submitted response to a question", () => {
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
});
