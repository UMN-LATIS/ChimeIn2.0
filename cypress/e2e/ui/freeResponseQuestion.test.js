import api from "../api";

describe("free response question", () => {
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

  it("creates a free response question and shows wordcloud of responses", () => {
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
        cy.get("[data-cy=question-type]").type("Free Response{enter}");
        cy.get("[data-cy=question-editor]").type("Free response question?");
        cy.contains("Save").click();
        cy.wait("@apiCreateQuestion");

        // check that the question was created
        cy.get("[data-cy=question-list]").should(
          "contain",
          "Free response question?"
        );

        // open question
        cy.get("[data-cy=toggle-open-question]").click();
        cy.wait("@apiOpenQuestion");

        // logout faculty, become guest user
        cy.logout();

        // as a guest, record a response
        cy.visit(`/join/${testChime.access_code}`);
        cy.get("[data-cy=free-response-textarea]").type("Guest response");
        cy.contains("Save").click();
        cy.wait("@apiSubmitResponse", { requestTimeout: 2000 });

        // login as faculty
        cy.login("faculty");
        cy.visit(`/chime/${testChime.id}/folder/${testFolder.id}`);
        cy.get("[data-cy=present-question-button]").click();
        cy.get("[data-cy=show-results-button]").click();
        cy.get("[data-cy=word-cloud] canvas")
          .invoke("attr", "aria-label")
          .should("eq", "guest: 1, response: 1");
      });
  });

  describe("Free Response input", () => {
    let testChime;
    let testFolder;
    let testQuestion;

    beforeEach(() => {
      // create an free response question
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

          // open the question
          api.openQuestion({
            chimeId: testChime.id,
            folderId: testFolder.id,
            questionId: testQuestion.id,
          });
        })
        .then(() => {
          // logout faculty
          cy.logout();
        });
    });

    it("should limit the size of a text response", () => {
      const MAX_RESPONSE_LENGTH = 10000;

      // join the chime as a participant
      cy.visit(`/join/${testChime.access_code}`);

      // create a response longer than the max length
      const longResponse = "a".repeat(MAX_RESPONSE_LENGTH);

      // put the long response in the textarea (faster than typing)
      cy.get('[data-cy="free-response-textarea"]')
        .invoke("val", longResponse)
        .trigger("input");

      // at max length, the character count should not be displayed
      cy.get('[data-cy="free-response-char-count"]').should("not.exist");

      // the save button should be enabled
      cy.contains("Save").should("not.be.disabled");

      // add a character to the response
      cy.get('[data-cy="free-response-textarea"]').type("a");

      // it should show the character count
      cy.get('[data-cy="free-response-char-count"]').should(
        "contain",
        `10,001 / 10,000`
      );

      // it should show the error message
      cy.contains("Your response is too long.");

      // it should disable the save button
      cy.contains("Save").should("be.disabled");

      // force enable the save button to test server validation
      cy.contains("Save").invoke("removeAttr", "disabled").click();

      // check that the server's error message is displayed
      cy.get(".participant-prompt > .alert").should(
        "contain",
        `cannot be longer than ${MAX_RESPONSE_LENGTH.toLocaleString()} characters`
      );
    });
  });
});
