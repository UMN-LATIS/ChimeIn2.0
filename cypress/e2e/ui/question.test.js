import api from "../api/index.js";

const favoriteColorQuestion = {
  chimeName: "Test Chime",
  folderName: "Test Folder",
  questionText: "What is your favorite color?",
  questionResponses: [
    { text: "<p>Red</p>", correct: false },
    { text: "<p>Green</p>", correct: false },
    { text: "<p>Blue</p>", correct: false },
  ],
};

describe("question", () => {
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

  it("creates a question (multiple choice)", () => {
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
        cy.get("[data-cy=question-type]").type("Multiple Choice{enter}");
        cy.get("[data-cy=question-editor]").type(
          "What is your favorite color?"
        );

        // add multiple choice options
        // new input should be focussed automatically after click and upon each {enter}
        cy.get("[data-cy=add-choice-button]")
          .click()
          .type("Red{enter}")
          .type("Green{enter}")
          .type("Blue");

        cy.contains("Save").click();

        // check that the question was created
        cy.get("[data-cy=question-list] .question-card").should(
          "contain",
          "What is your favorite color?"
        );

        // check that question card shows choices
        cy.get("[data-cy=question-list] .question-card")
          .should("contain.text", "Red")
          .should("contain.text", "Green")
          .should("contain.text", "Blue");
      });
  });

  it("opens a question", () => {
    let testChime, testFolder, testQuestion;

    api
      .createChimeFolderQuestion(favoriteColorQuestion)
      .then(({ chime, folder, question }) => {
        testChime = chime;
        testFolder = folder;
        testQuestion = question;
      })
      .then(() => {
        cy.visit(`/chime/${testChime.id}/folder/${testFolder.id}`);

        // open the question
        cy.get("[data-cy=toggle-open-question]").click();
      })
      .then(() => {
        cy.wait("@apiOpenQuestion", { requestTimeout: 2000 });
        return api.getQuestion({
          chimeId: testChime.id,
          folderId: testFolder.id,
          questionId: testQuestion.id,
        });
      })
      .then((question) => {
        // if question is open, current_session_id should be set
        expect(question.current_session_id).to.be.greaterThan(0);
      });
  });

  it("edits a question prompt", () => {
    let testChime, testFolder, testQuestion;

    api
      .createChimeFolderQuestion(favoriteColorQuestion)
      .then(({ chime, folder, question }) => {
        testChime = chime;
        testFolder = folder;
        testQuestion = question;
      })
      .then(() => {
        cy.visit(`/chime/${testChime.id}/folder/${testFolder.id}`);

        // edit the question
        cy.get("[data-cy=edit-question-button]").click();

        // change the question text
        cy.get("[data-cy=question-editor]")
          .find(".ql-editor")
          .clear()
          .type("Updated question prompt");

        cy.contains("Save").click();
        cy.wait("@apiUpdateQuestion");

        // expect that the UI is updated on question list page
        cy.get("[data-cy=question-list] .question-card").should(
          "contain",
          "Updated question prompt"
        );

        // expect that question is updated in presentation view
        cy.get("[data-cy=present-question-button]").click();
        cy.contains("Updated question prompt");
      })
      .then(() => {
        // also check that the API returns updated question
        return api.getQuestion({
          chimeId: testChime.id,
          folderId: testFolder.id,
          questionId: testQuestion.id,
        });
      })
      .then((question) => {
        expect(question.text).to.contain("Updated question prompt");
      });
  });

  it("deletes a question", () => {
    let testChime, testFolder;
    api
      .createChimeFolderQuestion(favoriteColorQuestion)
      .then(({ chime, folder }) => {
        testChime = chime;
        testFolder = folder;
      })
      .then(() => {
        cy.visit(`/chime/${testChime.id}/folder/${testFolder.id}`);
        cy.get("[data-cy=delete-question-button]").click();
        cy.wait("@apiDeleteQuestion");

        // check UI that question does not exist
        cy.get("[data-cy=question-list]").should(
          "not.contain.text",
          favoriteColorQuestion.questionText
        );

        // check API too
        return api.getAllQuestions({
          chimeId: testChime.id,
          folderId: testFolder.id,
        });
      })
      .then((questions) => {
        expect(questions).to.deep.equal([]);
      });
  });

  it("changes the folder");

  it("persists question settings changes on save", () => {
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

        // try toggling a question option and save
        cy.get('[data-cy="allow-multiple-responses-checkbox"]').click();
        cy.contains("Save").click();
        cy.wait("@apiCreateQuestion");

        // open the question
        cy.get('[data-cy="edit-question-button"]').click();

        // check that allow multiple is still checked
        cy.get('[data-cy="allow-multiple-responses-checkbox"]').should(
          "be.checked"
        );
      });
  });

  it("allows multiple responses", () => {
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

        // create a multiple response question
        cy.get("[data-cy=new-question-button]").click();
        cy.get("[data-cy=question-type]").type("Multiple Choice{enter}");
        cy.get('[data-cy="allow-multiple-responses-checkbox"]').click();

        cy.get("[data-cy=question-editor]").type("Which numbers are prime?");
        cy.get("[data-cy=add-choice-button]")
          .click()
          .type("1{enter}")
          .type("2{enter}")
          .type("3{enter}")
          .type("4{enter}");

        // mark 2 and 3 as correct
        cy.get(
          "#response-choice-item-1 > .response-choice-item__correct-toggle"
        ).click();
        cy.get(
          "#response-choice-item-2 > .response-choice-item__correct-toggle"
        ).click();

        cy.contains("Save").click();
        cy.wait("@apiCreateQuestion");
      })
      .then(() => {
        // open the question
        cy.get("[data-cy=toggle-open-question]").click();
        cy.logout();
      })
      .then(() => {
        // join as participant
        cy.visit(`/join/${testChime.access_code}`);

        // check options
        cy.get(":nth-child(2) > .form-check-label").click();

        cy.wait("@apiSubmitResponse");
        cy.get(":nth-child(3) > .form-check-label").click();
      })
      .then(() => {
        // check that results render properly
        cy.login("faculty");
        cy.visit(`/chime/${testChime.id}/folder/${testFolder.id}`);
        cy.get("[data-cy=present-question-button]").click();
        cy.get("[data-cy=show-results-button]").click();

        // wait for rendering
        // eslint-disable-next-line cypress/no-unnecessary-waiting
        cy.wait(3000);
        cy.viewport(1920, 1080);
        cy.get("#app").matchImageSnapshot("multiple-response");
      });
  });

  it("supports rich text formatting in the question text");
  it("allows LaTeX in the question text");
  it("creates an anonymous question");
});
