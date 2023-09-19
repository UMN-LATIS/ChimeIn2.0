/// <reference types="Cypress" />

import api from "../api/index.js";

describe("happy path", () => {
  beforeEach(() => {
    cy.refreshDatabase();
    cy.seed();
    cy.login("faculty");
    cy.visit("/");
  });

  it("creates a new chime, folder, and multiple choice question", () => {
    // create a chime
    cy.get("main").contains("Add Chime").click();
    cy.get("#chime_name_input").type("Test Chime");
    cy.get("#joinInstructions").check();
    cy.get("[data-cy=create-chime-button]").click();
    cy.url().should("match", /chime\/[0-9]+$/);
    cy.get("[data-cy=chime-name]").should("contain", "Test Chime");

    // create a folder
    cy.get("#createFolder").type("Test Folder 1");
    cy.get("[data-cy=create-folder-button]").contains("Add Folder").click();

    // go into the folder
    cy.get("[data-cy=folder-card]").contains("Test Folder 1").click();
    cy.url().should("match", /chime\/[0-9]+\/folder\/[0-9]+$/);
    cy.get("[data-cy=folder-name]").should("contain", "Test Folder 1");

    // create a question
    cy.get("[data-cy=new-question-button]").click();
    cy.get("[data-cy=add-question-form] h3").should(
      "contain",
      "Add a Question",
    );

    // select multiple choice (not a true select)
    cy.get("[data-cy=question-type]").type("Multiple Choice{enter}");

    // Fill in the question
    cy.get("[data-cy=question-editor]").type("What is your favorite color?");

    // add multiple choice options
    // new input should be focussed automatically after click and upon each {enter}
    cy.get("[data-cy=add-choice-button]").click();

    cy.get("#response-choice-item-0").type("Red{enter}");
    cy.get("#response-choice-item-1").type("Green{enter}");
    cy.get("#response-choice-item-2").type("Blue");

    cy.get(".multiple-choice-question-options")
      .should("contain", "Red")
      .should("contain", "Green")
      .should("contain", "Blue");

    cy.contains("Save").click();

    // check that the question was created
    cy.get("[data-cy=question-list] .chime-card").should(
      "contain",
      "What is your favorite color?",
    );

    // open the question
    cy.get("[data-cy=toggle-open-question]").click();

    // verify that the chime, folder, and question exists
    // and the session is active
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
});
