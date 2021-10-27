import api from "../api/index.js";

const favoriteColorQuestion = {
  chimeName: "Test Chime",
  folderName: "Test Folder",
  questionText: "What is your favorite color?",
  questionResponses: [
    { text: "Red", correct: false },
    { text: "Green", correct: false },
    { text: "Blue", correct: false },
  ],
};

describe("question", () => {
  beforeEach(() => {
    cy.refreshDatabase();
    cy.seed();
    cy.login("faculty");
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
        cy.get("[data-cy=response-text-input]").type("Red{enter}");
        cy.get("[data-cy=response-text-input]").type("Green{enter}");
        cy.get("[data-cy=response-text-input]").type("Blue{enter}");
        cy.contains("Save").click();

        // check that the question was created
        cy.get("[data-cy=question-list] li").should(
          "contain",
          "What is your favorite color?"
        );
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

  it("edits a question", () => {
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

        // change a response option
        cy.contains("Red").as("test-response");
        cy.get("@test-response")
          .parent()
          .find("[data-cy=edit-response]")
          .click();
        cy.get("[data-cy=response-text-input]")
          .clear()
          .type("Updated response");
        cy.get("[data-cy=save-response-button]").click();
        cy.get("@test-response").should("contain.text", "Updated response");

        cy.contains("Save").click();

        // expect that the UI is updated on question list page
        cy.get("[data-cy=question-list] li").should(
          "contain",
          "Updated question prompt"
        );

        // expect that question is updated in presentation view
        cy.get("[data-cy=present-question-button]").click();
        cy.contains("Updated question prompt");
        cy.contains("Updated response");
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
        const responses = question.question_info.question_responses;
        expect(responses[0].text).to.contain("Updated response");
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
  it("allows multiple reponses");
  it("supports rich text formatting in the question text");
  it("allows LaTeX in the question text");
  it("creates an anonymous question");

  describe("multiple choice", () => {
    it("marks correct responses");
    it("edits responses");
    it("removes respones");
  });

  describe("free response question", () => {
    it("creates a free response question", () => {
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

          // check that the question was created
          cy.get("[data-cy=question-list]").should(
            "contain",
            "Free response question?"
          );

          // open question
          cy.get("[data-cy=toggle-open-question]").click();

          // logout faculty, become guest user
          cy.logout();

          // as a guest, record a response
          cy.visit(`/join/${testChime.access_code}`);
          cy.get("[data-cy=free-response-textarea]").type("Guest response");
          cy.contains("Save").click();

          // login as faculty
          cy.login("faculty");
          cy.visit(`/chime/${testChime.id}/folder/${testFolder.id}`);
          cy.get("[data-cy=present-question-button]").click();
          cy.get("[data-cy=show-results-button").click();

          // "Guest" and "response" should be in the SVG word cloud
          cy.get("[data-cy=word-cloud]")
            .contains("svg", "Guest")
            .contains("svg", "response");
        });
    });

    it("hides wordcloud");
  });

  describe("text heatmap", () => {
    it("creates a text heatmap question");
    it("lets participants select parts of heatmap text");
  });

  describe("image response", () => {
    it("creates an image response question");
  });

  describe("slider", () => {
    it("creates a slider question");
    it("sets the left label");
    it("sets the right label");
    it("lets range be quantitative");
  });

  describe("image heatmap", () => {
    it("creates an image heatmap question");
    it("lets user change image");
  });

  describe("no response", () => {
    it("creates a no response / placeholder");
  });
});
