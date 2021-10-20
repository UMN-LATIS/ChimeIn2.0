import api from '../api/index.js';

describe("question", () => {
  beforeEach(() => {
    cy.refreshDatabase();
    cy.seed();
    cy.login("faculty");
  });

  it("creates a question", () => {
    let testChime; 
    let testFolder;
    api.createChime({ name: "Test Chime"}).then(chime => {
      testChime = chime;
      return api.createFolder({ name: "Test Folder", chimeId: chime.id });
    }).then(folder => {
      testFolder = folder;
    }).then(() => {
      cy.visit(`/chime/${testChime.id}/folder/${testFolder.id}`);

      // create the question
      cy.get('[data-cy=new-question-button]').click();
      cy.get("[data-cy=question-type]").type("Multiple Choice{enter}");
      cy.get("[data-cy=question-editor]").type("What is your favorite color?");
      
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
    api.createChimeFolderQuestion({ chimeName: "Test Chime", folderName: "Test Folder", questionText: "What is your favorite color?", questionResponses: [
      { text: "Red", correct: false },
      { text: "Green", correct: false },
      { text: "Blue", correct: false },
    ]}).then(({chime, folder, question }) => {
      testChime = chime;
      testFolder = folder;
      testQuestion = question;
    }).then(() => {
      cy.visit(`/chime/${testChime.id}/folder/${testFolder.id}`);

      // open the question
      cy.get("[data-cy=toggle-open-question]").click();
    }).then(() => {
      return api.getQuestion({
        chimeId: testChime.id,
        folderId: testFolder.id,
        questionId: testQuestion.id,
      })
    }).then((question) => {
      // if question is open, current_session_id should be set
      expect(question.current_session_id).to.be.greaterThan(0);
    })
  });

  it("edits a question");
  it("deletes a question");
  it("changes the folder");
  it("allows multiple reponses");
  it("supports rich text formatting in the question text");
  it("allows LaTeX in the question text");
  it("creates an anonymous question");

  describe("multiple choice", () => {
    it("adds a multiple choice question with responses");
    it("marks correct responses");
    it("edits responses");
    it("removes respones");
  });

  describe("free response question", () => {
    it("creates a free response question");
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
