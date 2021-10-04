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
      cy.get("#createFolder").type("Test Folder 1{enter}");
      cy.get("[data-cy=folder-card]").contains("Test Folder 1").click();
      cy.contains("Create").click();

      // go into the folder
      cy.get('main').contains("Test Folder 1").click();
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
      cy.request("/api/chime").its("body").then(chimes => {
        expect(chimes.length).to.equal(1);
        expect(chimes[0].name).to.equal("Test Chime");
      });
    });

    it('show a spinner while waiting for chime data to load', () => {
      // set up a delayed response time
      cy.delayResponse('/api/chime*', 1000);

      // create a chime
      cy.contains("Add a Chime").click();
      cy.get("#chime_name_input").type("Test Chime")
      cy.get("[data-cy=create-chime-button]").click();

      // expect a spinner while loading
      cy.get('.spinner');

      // and then expect Test Chime
      cy.get('main h1').should('contain.text', 'Test Chime');
    });


    it('opens a question to students');
    it('can require login to participate');
    it('shows access code on presentation screen');
    it('previews the question');

  });

  context("when user is a guest participant", () => {
    it("shows no questions if none are open");
    it("show open questions that do not require login");
    it("does not show open questions that require a login");
    it("records the submitted response to a question");
  });
});
