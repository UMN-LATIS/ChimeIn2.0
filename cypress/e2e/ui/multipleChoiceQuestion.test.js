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

describe("multiple choice", () => {
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
  it("marks correct responses", () => {
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
        cy.get("[data-cy=edit-question-button]").click();
        cy.get(
          "#response-choice-item-2 > .response-choice-item__correct-toggle",
        ).click();

        // except input background color to be green
        cy.get("#response-choice-item-2").should(
          "have.class",
          "response-choice-item--is-correct",
        );
        cy.contains("Save").click();
        cy.wait("@apiUpdateQuestion");
      })
      .then(() => {
        // check the API that the correct answers match expectation
        return api.getQuestion({
          chimeId: testChime.id,
          folderId: testFolder.id,
          questionId: testQuestion.id,
        });
      })
      .then((question) => {
        const responses = question.question_info.question_responses;
        expect(responses).to.deep.equal([
          { text: "<p>Red</p>", correct: false },
          { text: "<p>Green</p>", correct: false },
          { text: "<p>Blue</p>", correct: true },
        ]);
      });
  });

  it("edits a question choice", () => {
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

        // change a response option
        cy.get(".response-choice-item__text [contenteditable]")
          .first()
          .as("test-response")
          .should("contain", "Red");

        cy.get("@test-response").click();
        // eslint-disable-next-line cypress/unsafe-to-chain-command
        cy.focused().type("{selectAll}Updated response");
        cy.contains("Save").click();
        cy.wait("@apiUpdateQuestion");

        // expect that question is updated in presentation view
        cy.get("[data-cy=present-question-button]").click();
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
        const responses = question.question_info.question_responses;
        expect(responses[0].text).to.contain("<p>Updated response</p>");
      });
  });

  it("removes a response choice", () => {
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

        // remove the first response
        cy.get("[data-cy=remove-response-button]").first().click();

        cy.contains("Save").click();
        cy.wait("@apiUpdateQuestion");

        // expect that question is updated in presentation view
        cy.get("[data-cy=present-question-button]").click();
        cy.get("[data-cy=multiple-choice-options-list]").should(
          "not.contain",
          "Red",
        );
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
        const responses = question.question_info.question_responses;
        expect(responses).to.deep.equal([
          { text: "<p>Green</p>", correct: false },
          { text: "<p>Blue</p>", correct: false },
        ]);
      });
  });

  it("creates response choices with equations", () => {
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
          "What is your favorite equation?",
        );

        // add multiple choice options
        // new input should be focussed automatically after click and upon each {enter}
        // note: {{} is needed for `{` because a brace is a delimiter for
        // special keys like {enter}
        ["e=mc^2", "a^2 + b^2 = c^2", "e^{{}\\pi i{}} - 1 = 0"].forEach(
          (eq, i) => {
            cy.get("[data-cy=add-choice-button]").click();

            // click the equation button
            cy.get(`#response-choice-item-${i}`)
              .find(
                // ".response-choice-item__contents > .quillWrapper > .ql-toolbar > .ql-formats > .ql-formula"
                ".response-choice-item__contents > .ql-toolbar > .ql-formats > .ql-formula",
              )
              .click();

            // eslint-disable-next-line cypress/unsafe-to-chain-command
            cy.focused().type(`${eq}{enter}`);
          },
        );

        cy.contains("Save").click();
        cy.wait("@apiCreateQuestion");
      })
      .then(() => {
        // open the question for presentation
        // open question
        cy.get("[data-cy=toggle-open-question]").click();

        // present
        cy.get("[data-cy=present-question-button]").click();

        // wait for equations to render
        cy.get(".katex-html");

        // expect screenshot to look correct
        cy.viewport(1920, 1080);
        cy.get("[data-cy=multiple-choice-options-list]").matchImageSnapshot(
          "presenter-equation-choices",
        );

        cy.logout();
      })
      .then(() => {
        // check that participant equation choices render correctly
        cy.visit(`/join/${testChime.access_code}`);

        // wait for equations to render
        cy.get(".katex .mathnormal").should("contain", "e");

        // eslint-disable-next-line cypress/no-unnecessary-waiting
        cy.wait(1000);
        cy.viewport(1920, 1080);
        cy.get(
          "[data-cy=multiple-choice-participant-choices]",
        ).matchImageSnapshot("participant-equation-choices");

        // click e^πi-1=0
        cy.get(".form-group > :nth-child(3) > .form-check-label").click();
        cy.wait("@apiSubmitResponse", { requestTimeout: 2000 });
      })
      .then(() => {
        // check that results render properly, including equation labels
        cy.login("faculty");
        cy.visit(`/chime/${testChime.id}/folder/${testFolder.id}`);
        cy.get("[data-cy=present-question-button]").click();
        cy.get("[data-cy=show-results-button]").click();
        cy.get("#app").should("contain.text", "e=mc");
      });
  });

  it("renders large labels without clipping,", () => {
    let testChime, testFolder;

    const questionWithALongChoice = {
      ...favoriteColorQuestion,
      questionResponses: [
        {
          text: "<p>Etiam porta sem malesuada magna mollis euismod. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam porta sem malesuada magna mollis euismod. Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>",
          correct: false,
        },
        { text: "<p>Green</p>", correct: false },
        { text: "<p>Blue</p>", correct: false },
      ],
    };

    api
      .createChimeFolderQuestion(questionWithALongChoice)
      .then(({ chime, folder }) => {
        testChime = chime;
        testFolder = folder;
      })
      .then(() => {
        cy.visit(`/chime/${testChime.id}/folder/${testFolder.id}`);

        // open the question
        cy.get("[data-cy=toggle-open-question]").click();

        // logout faculty
        cy.logout();
      })
      .then(() => {
        cy.visit(`/join/${testChime.access_code}`);
        cy.get(":nth-child(1) > .form-check-label").click();
      })
      .then(() => {
        cy.login("faculty");
        cy.visit(`/chime/${testChime.id}/folder/${testFolder.id}`);
        cy.get("[data-cy=present-question-button]").click();
        cy.get("[data-cy=show-results-button]").click();

        // wait for rendering and animation to complete
        // eslint-disable-next-line cypress/no-unnecessary-waiting
        cy.wait(1500);
        cy.viewport(1920, 1080);
        cy.get("#app").matchImageSnapshot(`mult-choice-stats-with-long-labels`);
      });
  });

  it("marks a correct choice with check when presenting stats", () => {
    let testChime, testFolder;

    const questionWithCorrectChoice = {
      ...favoriteColorQuestion,
      questionResponses: [
        {
          text: "<p>Red</p>",
          correct: false,
        },
        { text: "<p>Green</p>", correct: false },
        { text: "<p>Blue</p>", correct: true },
      ],
    };

    api
      .createChimeFolderQuestion(questionWithCorrectChoice)
      .then(({ chime, folder }) => {
        testChime = chime;
        testFolder = folder;
      })
      .then(() => {
        cy.visit(`/chime/${testChime.id}/folder/${testFolder.id}`);

        // open the question
        cy.get("[data-cy=toggle-open-question]").click();

        // logout faculty
        cy.logout();
      })
      .then(() => {
        cy.visit(`/join/${testChime.access_code}`);
        cy.get(":nth-child(1) > .form-check-label").click();
      })
      .then(() => {
        cy.login("faculty");
        cy.visit(`/chime/${testChime.id}/folder/${testFolder.id}`);
        cy.get("[data-cy=present-question-button]").click();
        cy.get("[data-cy=show-results-button]").click();

        // wait for rendering and animation to complete
        // eslint-disable-next-line cypress/no-unnecessary-waiting
        cy.wait(1500);
        cy.viewport(1920, 1080);
        cy.get("#app").matchImageSnapshot(
          `mult-choice-stats-with-checkmark-on-correct`,
        );
      });
  });
});
