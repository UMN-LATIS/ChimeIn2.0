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
          "What is your favorite color?",
        );

        // add multiple choice options
        // new input should be focussed automatically after click and upon each {enter}
        cy.get("[data-cy=add-choice-button]").click();
        cy.focused().type("Red{enter}");

        cy.focused().type("Green{enter}");

        cy.focused().type("Blue");

        cy.contains("Save").click();

        // check that the question was created
        cy.get("[data-cy=question-list] .question-card").should(
          "contain",
          "What is your favorite color?",
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
          .type("{selectAll}Updated question prompt");

        cy.contains("Save").click();
        cy.wait("@apiUpdateQuestion");

        // expect that the UI is updated on question list page
        cy.get("[data-cy=question-list] .question-card").should(
          "contain",
          "Updated question prompt",
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
          favoriteColorQuestion.questionText,
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
          "be.checked",
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
        cy.get("[data-cy=add-choice-button]").click();

        cy.focused().type("1{enter}");
        cy.focused().type("2{enter}");
        cy.focused().type("3{enter}");
        cy.focused().type("4{enter}");

        // mark 2 and 3 as correct
        cy.get(
          "#response-choice-item-1 > .response-choice-item__correct-toggle",
        ).click();
        cy.get(
          "#response-choice-item-2 > .response-choice-item__correct-toggle",
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
        cy.get("#app").matchImageSnapshot("multiple-response");
      });
  });

  it("moves a question to a different folder", () => {
    // create a chime with two folders
    let testChime;
    let testFolder1;
    let testFolder2;

    api
      .createChime({ name: "Test Chime" })
      .then((chime) => {
        testChime = chime;
        return api.createFolder({ name: "Test Folder 1", chimeId: chime.id });
      })
      .then((folder) => {
        testFolder1 = folder;
        return api.createFolder({
          name: "Test Folder 2",
          chimeId: testChime.id,
        });
      })
      .then((folder) => {
        testFolder2 = folder;
      })
      .then(() => {
        // create 3 questions
        [1, 2, 3].forEach((n) => {
          api.createQuestion({
            chimeId: testChime.id,
            folderId: testFolder1.id,
            question_text: `Question ${n}`,
            question_info: {
              question_type: "free_response",
              question_responses: [],
            },
          });
        });
      })
      .then(() => {
        // go to the first folder
        cy.visit(`/chime/${testChime.id}/folder/${testFolder1.id}`);
        // verify that there are 3 questions
        cy.get("[data-cy=question-list] .question-card").should(
          "have.length",
          3
        );
      })
      .then(() => {
        // move the second question to the second folder
        cy.get("[data-cy=question-list] .question-card:nth-child(2)").within(
          () => {
            cy.contains("Edit").click();
          }
        );
        cy.get("[data-cy=folder-select]").type(
          "{selectAll}Test Folder 2{enter}"
        );
        cy.contains("Save").click();
      })
      .then(() => {
        // verify that there are 2 questions in the first folder
        cy.visit(`/chime/${testChime.id}/folder/${testFolder1.id}`);
        cy.get("[data-cy=question-list] .question-card").should(
          "have.length",
          2
        );
      })
      .then(() => {
        // verify that there is 1 question in the second folder
        cy.visit(`/chime/${testChime.id}/folder/${testFolder2.id}`);
        cy.get("[data-cy=question-list] .question-card").should(
          "have.length",
          1
        );

        // click the present button
        cy.get("[data-cy=present-question-button]").click();

        // verify that Question 2 appears on the screen
        // checks that nothing went screwy with the ordering
        cy.contains("Question 2");
      });
  });

  it("supports rich text formatting in the question text");
  it("allows LaTeX in the question text");
  it("creates an anonymous question");
});
