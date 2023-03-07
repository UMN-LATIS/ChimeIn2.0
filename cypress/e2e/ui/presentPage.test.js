import api from "../api/index.js";
import toHyphenatedCode from "../../../resources/assets/js/helpers/toHyphenatedCode";

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

describe("presentPage", () => {
  beforeEach(() => {
    cy.refreshDatabase();
    cy.seed();
    cy.login("faculty");
  });

  it("shows join instructions", () => {
    let testChime;
    let testFolder;
    let testQuestion;

    api
      .createChimeFolderQuestion(favoriteColorQuestion)
      .then(({ chime, folder, question }) => {
        testChime = chime;
        testFolder = folder;
        testQuestion = question;

        return api.openQuestion({
          chimeId: testChime.id,
          folderId: testFolder.id,
          questionId: testQuestion.id,
        });
      })
      .then(() => {
        cy.visit(`/chime/${testChime.id}/folder/${testFolder.id}`);
        cy.contains("Present").click();
        cy.get("[data-cy=chime-host]").should("contain", "localhost");
        cy.get("[data-cy=access-code]").should(
          "contain.text",
          toHyphenatedCode(testChime.access_code)
        );
      });
  });

  it("shows canvas join instructions for canvas chimes", () => {
    let testChime;
    let testFolder;
    let testQuestion;

    cy.intercept("/api/chime/*", { fixture: "canvasChime.json" });

    api
      .createChimeFolderQuestion(favoriteColorQuestion)
      .then(({ chime, folder, question }) => {
        testChime = chime;
        testFolder = folder;
        testQuestion = question;

        return api.openQuestion({
          chimeId: testChime.id,
          folderId: testFolder.id,
          questionId: testQuestion.id,
        });
      })
      .then(() => {
        cy.visit(`/chime/${testChime.id}/folder/${testFolder.id}`);
        cy.contains("Present").click();
        cy.get("[data-cy=canvas-host]").should("contain", "canvas.umn.edu");

        // get join instructions
        cy.get("[data-cy=chime-host]").should("contain", "localhost");
        cy.get("[data-cy=access-code]").should(
          "contain.text",
          toHyphenatedCode("123-456")
        );
      });
  });
});
