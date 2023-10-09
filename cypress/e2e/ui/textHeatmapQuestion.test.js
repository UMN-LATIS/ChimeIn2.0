import api from "../api/index.js";

describe("text heatmap", () => {
  beforeEach(() => {
    cy.refreshDatabase();
    cy.seed();
    cy.login("faculty");

    cy.intercept({
      method: "POST",
      url: "/api/chime/*/folder/*/question/*",
    }).as("apiOpenQuestion");

    cy.intercept({
      method: "POST",
      url: "/api/chime/*/folder/*",
    }).as("apiCreateQuestion");

    cy.intercept({
      method: "PUT",
      url: "/api/chime/*/folder/*/question/*",
    }).as("apiUpdateQuestion");

    cy.intercept({
      method: "DELETE",
      url: "/api/chime/*/folder/*/question/*",
    }).as("apiDeleteQuestion");

    cy.intercept({
      method: "PUT",
      url: "/api/chime/*/session/*/response",
    }).as("apiSubmitResponse");
  });
  it("creates a text heatmap question and lets partipants select parts", () => {
    let testChime;
    let testFolder;
    api
      .createChime({ name: "Test Chime" })
      .then((chime) => {
        testChime = chime;
        return api.createFolder({ name: "Test Folder", chimeId: chime.id });
      })
      .then((folder) => {
        testFolder = folder;
      })
      .then(() => {
        cy.visit(`/chime/${testChime.id}/folder/${testFolder.id}`);

        // create the question
        cy.get("[data-cy=new-question-button]").click();
        cy.get("[data-cy=question-type]").type("Text Heatmap{enter}");
        cy.get("[data-cy=question-editor]").type("Text heatmap question?");

        cy.get("[data-cy=heatmap-text-editor]").type(
          `A guy told me one time, "Don't let yourself get attached to anything you are not willing to walk out on in 30 seconds flat if you feel the heat around the corner."`
        );
        cy.contains("Save").click();
        cy.wait("@apiCreateQuestion");

        // check that the question was created
        cy.get("[data-cy=question-list]").should(
          "contain",
          "Text heatmap question?"
        );

        // open question
        cy.get("[data-cy=toggle-open-question]").click();
        cy.wait("@apiOpenQuestion");

        // logout faculty, become guest user
        cy.logout();

        //   // as a guest, record a response
        cy.visit(`/join/${testChime.access_code}`);
        cy.get(
          "[data-cy=text-heatmap-highlighted-text-container]"
        ).setSelection("feel the heat");
        cy.contains("Submit Selection").click();
        cy.wait("@apiSubmitResponse", { requestTimeout: 2000 });

        //   // login as faculty
        cy.login("faculty");
        cy.visit(`/chime/${testChime.id}/folder/${testFolder.id}`);
        cy.get("[data-cy=present-question-button]").click();
        cy.get("[data-cy=show-results-button]").click();

        // the "h" in "feel the heat" should be highlighted redish
        cy.get(":nth-child(140)").should(
          "have.css",
          "background-color",
          "rgb(255, 153, 153)"
        );

        // the "a" in "around" should not be highlighted
        cy.get(":nth-child(145)").should(
          "have.css",
          "background-color",
          "rgb(255, 255, 255)"
        );
      });
  });
});
