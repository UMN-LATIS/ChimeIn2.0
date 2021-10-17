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
        return api.createFolder(chime.id, { name: "Test Folder" });
      })
      .then((newFolder) => (folder = newFolder));
  });

  it("gets all the questions within a chime folder", () => {
    api
      .getAllQuestions({ chimeId: chime.id, folderId: folder.id })
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
        return api.getAllQuestions({
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

  it("closes an open question", () => {
    let testQuestion = null;

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
      .then((newQuestion) => {
        // open question
        testQuestion = newQuestion;
        return api.openQuestion({
          chimeId: chime.id,
          folderId: folder.id,
          questionId: testQuestion.id,
        });
      })
      .then(() => {
        // check that its open
        api
          .getQuestion({
            chimeId: chime.id,
            folderId: folder.id,
            questionId: testQuestion.id,
          })
          .then((question) => {
            expect(question.current_session_id).to.be.greaterThan(0);
          });

        // now close it
        api.closeQuestion({
          chimeId: chime.id,
          folderId: folder.id,
          questionId: testQuestion.id,
        });
      })
      .then(() => {
        return api.getQuestion({
          chimeId: chime.id,
          folderId: folder.id,
          questionId: testQuestion.id,
        });
      })
      .then((question) => {
        expect(question.current_session_id).to.be.null;
      });
  });

  it("updates a question", () => {
    const initialQuestion = {
      chimeId: chime.id,
      folderId: folder.id,
      question_text: `<p>What?</p>`,
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
    };

    let questionId = null;

    api
      .createQuestion(initialQuestion)
      .then((question) => {
        questionId = question.id;
        return api.updateQuestion({
          ...initialQuestion,
          questionId,
          question_text: `<p>Updated!</p>`,
        });
      })
      .then(() => {
        api
          .getQuestion({
            chimeId: chime.id,
            folderId: folder.id,
            questionId,
          })
          .its("text")
          .should("equal", `<p>Updated!</p>`);
      });
  });

  it("deletes a question", () => {
    const initialQuestion = {
      chimeId: chime.id,
      folderId: folder.id,
      question_text: `<p>What?</p>`,
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
    };

    let questionId = null;

    api
      .createQuestion(initialQuestion)
      .then((question) => {
        questionId = question.id;
        return api.updateQuestion({
          ...initialQuestion,
          questionId,
          question_text: `<p>Updated!</p>`,
        });
      })
      .then(() => {
        api.deleteQuestion({
          folderId: folder.id,
          chimeId: chime.id,
          questionId,
        });
      })
      .then(() => {
        api
          .getAllQuestions({
            folderId: folder.id,
            chimeId: chime.id,
          })
          .should("deep.equal", []);
      });
  });
});
