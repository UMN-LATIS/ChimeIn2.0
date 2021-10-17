/// <reference types="Cypress" />

import api from "./index.js";

describe("question api", () => {
  let chime = null;
  let folder = null;

  beforeEach(() => {
    cy.refreshDatabase();
    cy.seed();
    cy.login({ umndid: "faculty" });
    api
      .createChime("Test Chime")
      .then((newChime) => {
        chime = newChime;
        return api.createFolderInChime(chime.id, { name: "Test Folder" });
      })
      .then((newFolder) => (folder = newFolder));
  });

  it("gets all the questions within a chime folder", () => {
    api
      .getAllQuestionsInFolder({ chimeId: chime.id, folderId: folder.id })
      .should("deep.equal", []);
  });
  it("creates a question within a chime folder", () => {
    const question_info = {
      question_type: "multiple_choice",
      question_responses: ["Red", "White", "Blue"],
    };

    api
      .createQuestion({
        chimeId: chime.id,
        folderId: folder.id,
        question_text: `<p>What's your favorite color?</p>`,
        question_info,
      })
      .then((question) => {
        expect(question.text).to.equal(`<p>What's your favorite color?</p>`);
        expect(question.question_info).to.deep.equal(question_info);
      });
  });

  it("opens a question within a chime", () => {
    let questionId = null;

    api
      .createQuestion({
        chimeId: chime.id,
        folderId: folder.id,
        question_text: `<p>What?</p>`,
        question_info: {
          question_type: "multiple_choice",
          question_responses: ["This", "That"],
        },
      })
      .then((question) => {
        questionId = question.id;
        return api.openQuestion({
          chimeId: chime.id,
          folderId: folder.id,
          questionId,
        });
      })
      .then(() => {
        return api.getAllQuestionsInFolder({
          chimeId: chime.id,
          folderId: folder.id,
        });
      })
      .then((questions) => {
        const openQuestion = questions[0];
        expect(openQuestion.id).to.equal(questionId);

        const currentSessionId = openQuestion.current_session_id;
        const isOpen = !!currentSessionId;
        expect(isOpen).to.be.true;

        const currentSession = openQuestion.sessions.find(
          (sess) => sess.id === currentSessionId
        );
        expect(currentSession.question_id).to.equal(questionId);
      });
  });
});
