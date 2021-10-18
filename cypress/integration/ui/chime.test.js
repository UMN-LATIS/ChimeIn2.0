/// <reference types="Cypress" />

import api from "../api/index.js";
import toHyphenatedCode from "../../../resources/assets/js/helpers/toHyphenatedCode.mjs";

describe("chime UI", () => {
  beforeEach(() => {
    cy.refreshDatabase();
    cy.seed();
  });

  context("when not authenticated", () => {
    it("does not allow adding a chime", () => {
      cy.visit("/");
      cy.contains("Add a chime").should("not.exist");
    });

    it("joins upon entering an access code");
  });

  context("when authenticated as faculty", () => {
    beforeEach(() => {
      cy.login("faculty");
    });

    it("creates a new chime", () => {
      cy.visit("/");
      cy.get("main")
        .contains("Add a Chime")
        .click();
      cy.get("#chime_name_input").type("Test Chime");
      cy.get("#joinInstructions").check();
      cy.get("[data-cy=create-chime-button]").click();
      cy.url().should("match", /chime\/[0-9]+$/);
      cy.get("h1").should("contain", "Test Chime");
    });

    it("show a spinner while waiting for chime data to load", () => {
      cy.visit("/");
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

    describe("change chime settings", () => {
      let testChime;
      let testFolder;
      let testQuestion;

      beforeEach(() => {
        api
          .createChime({ name: "TestChime" })
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
              question_text: "<p>What?</p>",
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
            });
          })
          .then((question) => {
            testQuestion = question;
            cy.visit(`/chime/${testChime.id}`);
            cy.get("[data-cy=toggle-chime-settings-panel]").click();
          });
      });

      it("updates a chime name", () => {
        cy.get("[data-cy=chime-name-input]")
          .clear()
          .type("Updated Name");
        cy.get("[data-cy=save-chime-name-button]").click();
        cy.get(".chime__name").should("contain.text", "Updated Name");
      });

      it("requires login to access", () => {
        cy.get("#requireLogin").check();
        cy.logout();

        // accessing chime as guest should redirect to login page
        cy.visit(`/join/${testChime.access_code}`);
        cy.get(".title").should("contain.text", "Login to Continue");
      });

      it("displays join instructions when presenting", () => {
        cy.get("#joinInstructions").check();
        cy.visit(`/chime/${testChime.id}/folder/${testFolder.id}/present`);

        // FIXME: This text is in the navbar and will be hidden on small screens

        cy.get("[data-cy=show-join-code]").should(
          "contain.text",
          toHyphenatedCode(testChime.access_code)
        );
      });

      it("reveals folder titles to participants", () => {
        cy.get('#showFolderTitle').check();
        api.openQuestion({
          chimeId: testChime.id,
          folderId: testFolder.id,
          questionId: testQuestion.id,
        });
        cy.logout();

        cy.visit(`/join/${testChime.access_code}`);
        cy.get('[data-cy=show-folder-to-participants]').should('contain.text',testFolder.name);
      });


      it("removes users from chime");
      it("promotes participants to presenters");
      it("demotes presenters to participants");
    });

    describe("chime export", () => {
      // TODO: Ask Colin about intended functionality and use case for each Export option.
      it("exports folder participation");
      it("exports question participation");
      it("exports full responses");
      it("exports questions (no responses)");
      it("exports individual sessions with responses");
      it("exports only selected folders");
      it("exports only _correct_ answers for multiple choice");
    });
  });
});
