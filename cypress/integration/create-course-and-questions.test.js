/// <reference types="Cypress" />

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
      cy.login({ umndid: "faculty" });
      cy.visit("/");
    });

    it("creates a new chime, folder, and multiple choice question", () => {
      // create a chime
      cy.contains("Add a Chime").click();
      cy.get("#chime_name_input").type("Test Chime");
      cy.get("#joinInstructions").click();
      cy.contains("Create").click();
      cy.url().should("match", /chime\/[0-9]+$/);
      cy.get("h1").should("contain", "Test Chime");

      // create a folder
      cy.get("#createFolder").type("Test Folder 1");
      cy.contains("Create").click();

      // go into the folder
      cy.contains("Test Folder 1").click();
      cy.url().should("match", /chime\/[0-9]+\/folder\/[0-9]+$/);
      cy.get("h1").should("contain", "Test Folder 1");

      // create a question
      cy.get("[data-cy=new-question-button]").click();
      cy.get("h3").should("contain", "Add a Question");

      // Choose Multiple choice by opening select
      // then clicking "multiple choice". Multiple choice is the default
      // but we want to be sure we can click it.
      // Note that this isn't a true select, but a search
      // input
      // cy.get("[data-cy=question-type]").click();
      // cy.get("[data-cy=question-type]")
      //   .contains("Multiple Choice")
      //   .click();

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
    });
  });
});
