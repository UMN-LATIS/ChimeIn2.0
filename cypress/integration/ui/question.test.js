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
          cy.get("[data-cy=show-results-button]").click();

          // "Guest" and "response" should be in the SVG word cloud
          cy.get("[data-cy=word-cloud]")
            .contains("svg", "Guest")
            .contains("svg", "response");
        });
    });

    it("hides wordcloud");
  });

  describe("text heatmap", () => {
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

          // check that the question was created
          cy.get("[data-cy=question-list]").should(
            "contain",
            "Text heatmap question?"
          );

          //   // open question
          cy.get("[data-cy=toggle-open-question]").click();

          // logout faculty, become guest user
          cy.logout();

          //   // as a guest, record a response
          cy.visit(`/join/${testChime.access_code}`);
          cy.get(
            "[data-cy=text-heatmap-highlighted-text-container]"
          ).setSelection("feel the heat");
          cy.contains("Submit Selection").click();

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

  describe("image response", () => {
    it("creates an image response question", () => {
      let testChime;
      let testFolder;

      // set up an intecept to watch image uploads
      // so that we can wait for upload to complete
      cy.intercept({
        method: "POST",
        url: "/api/chime/**/image",
      }).as("upload");

      api
        .createChime({
          name: "Test Chime",
        })
        .then((chime) => {
          testChime = chime;
          return api.createFolder({
            name: "Test Folder",
            chimeId: chime.id,
          });
        })
        .then((folder) => {
          testFolder = folder;
        })
        .then(() => {
          cy.visit(`/chime/${testChime.id}/folder/${testFolder.id}`);

          // create the question
          cy.get("[data-cy=new-question-button]").click();
          cy.get("[data-cy=question-type]").type("Image Response{enter}");
          cy.get("[data-cy=question-editor]").type("Image response question?");
          cy.contains("Save").click();

          // check that the question was created
          cy.get("[data-cy=question-list]").should(
            "contain",
            "Image response question?"
          );

          // open question
          cy.get("[data-cy=toggle-open-question]").click();

          // logout faculty, become guest user
          cy.logout();

          // as a guest, attach an image
          cy.visit(`/join/${testChime.access_code}`);
          cy.get("[data-cy=image-dropzone]").attachFile("goldy-650x435.jpg");

          // wait for upload to finish
          cy.wait("@upload", { requestTimeout: 10000 });

          // expect the image thumbnail to be displayed
          cy.get("[data-cy=image-thumbnail]").should("exist");

          // login as faculty
          cy.login("faculty");
          cy.visit(`/chime/${testChime.id}/folder/${testFolder.id}`);
          cy.get("[data-cy=present-question-button]").click();
          cy.get("[data-cy=show-results-button]").click();

          // expect goldy image to be displayed
          // just checking extension for now, as name could have changed
          // to the image's hash
          cy.get("[data-cy=image-responses]")
            .find("img")
            .should("have.attr", "src")
            .should("include", ".jpg");
        });
    });
  });

  describe("slider", () => {
    it("creates a qualitative slider question and lets participant respond", () => {
      let testChime;
      let testFolder;

      // listen for participant responses
      // so that we can wait for them to complete
      cy.intercept({
        method: "PUT",
        url: "/api/**/response",
      }).as("participantResponse");

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
          cy.get("[data-cy=question-type]").type("Slider{enter}");
          cy.get("[data-cy=question-editor]").type("Slider question?");
          cy.get("#left_choice_text").type("Bad");
          cy.get("#right_choice_text").type("Good");
          cy.contains("Save").click();

          // check that the question was created
          cy.get("[data-cy=question-list]").should(
            "contain",
            "Slider question?"
          );

          // open question
          cy.get("[data-cy=toggle-open-question]").click();

          // logout faculty, become guest user
          cy.logout();
        })
        .then(() => {
          // as a guest, record a response
          cy.visit(`/join/${testChime.access_code}`);
          cy.get("[data-cy=slider-response-input]")
            .invoke("val", 25)
            .trigger("change");
          cy.wait("@participantResponse", { requestTimeout: 3000 });
        })
        .then(() => {
          // login as faculty
          cy.login("faculty");
          cy.visit(`/chime/${testChime.id}/folder/${testFolder.id}`);
          cy.get("[data-cy=present-question-button]").click();
          cy.get("[data-cy=show-results-button]").click();

          // expect the labels to be displayed
          cy.get("[data-cy=chart-container]")
            .contains("svg", "Bad")
            .contains("svg", "Good");

          // this is hacky
          // select all svg bars in the bargraph (all the same stroke)
          // and check that the correct one is tall
          cy.get('[stroke="#36a2eb"]').then(($bars) => {
            // cy.get returns a jQuery set, so convert to a vanilla array and
            // then get height attribute as float
            const barHeights = $bars
              .toArray()
              .map((b) => b.getAttribute("height"))
              .map(Number.parseFloat);

            expect(barHeights.length).to.equal(16);

            // expect the tall bar to be tall
            const tallBarIndex = 4;
            expect(barHeights[tallBarIndex]).to.be.greaterThan(10);

            // all the rest should be short (0.5)
            expect(
              barHeights.filter((_, i) => i !== tallBarIndex)
            ).to.deep.equal(Array(15).fill(0.5));
          });
        });
    });

    it("lets range be quantitative");
  });

  describe("image heatmap", () => {
    it("creates an image heatmap question", () => {
      let testChime;
      let testFolder;

      // set up an intecept to watch image uploads
      // so that we can wait for upload to complete
      cy.intercept({
        method: "POST",
        url: "/api/chime/**/image",
      }).as("upload");

      cy.intercept({
        method: "PUT",
        url: "/api/**/response",
      }).as("heatmapResponse");

      api
        .createChime({
          name: "Test Chime",
        })
        .then((chime) => {
          testChime = chime;
          return api.createFolder({
            name: "Test Folder",
            chimeId: chime.id,
          });
        })
        .then((folder) => {
          testFolder = folder;
        })
        .then(() => {
          cy.visit(`/chime/${testChime.id}/folder/${testFolder.id}`);

          // create the question
          cy.get("[data-cy=new-question-button]").click();

          // can't just use "Heatmap" as "Text Heatmap" will be selected first
          // press down arrow to get the second "Heatmap" option.
          // This is brittle. Maybe rename "Heatmap" as "Image Heatmap" once
          // tests are working.
          cy.get("[data-cy=question-type]")
            .type("Heatmap")
            .type("{downarrow}")
            .type("{enter}");
          cy.get("[data-cy=question-editor]").type("Image heatmap question?");

          cy.get("[data-cy=image-dropzone]").attachFile("goldy-650x435.jpg");

          // wait for upload to finish
          cy.wait("@upload", { requestTimeout: 10000 });

          // expect the image thumbnail to be displayed
          cy.get("[data-cy=image-thumbnail]").should("exist");

          cy.contains("Save").click();

          // check that the question was created
          cy.get("[data-cy=question-list]").should(
            "contain",
            "Image heatmap question?"
          );

          // open question
          cy.get("[data-cy=toggle-open-question]").click();

          // logout faculty, become guest user
          cy.logout();

          //  as a guest, record a response
          cy.visit(`/join/${testChime.access_code}`);

          // make sure image is loaded
          cy.get("#currentQuestions [data-cy=image-heatmap-target]")
            .as("image-heatmap-target")
            .should("be.visible")
            .and(($img) => {
              expect($img[0].naturalWidth).to.be.greaterThan(0);
            });

          // click on it
          // note: clicking on different coordinates should cause
          // match image snapshot fail
          cy.get("@image-heatmap-target").click(50, 100);
        })
        .then(() => {
          cy.wait("@heatmapResponse", { requestTimeout: 10000 });
          // check that the circle appears on user interface
          cy.get("@image-heatmap-target").matchImageSnapshot(
            `image-heatmap-response-view_1920x1080`
          );
        })
        .then(() => {
          // login as faculty
          cy.login("faculty");
          cy.visit(`/chime/${testChime.id}/folder/${testFolder.id}`);
          cy.get("[data-cy=present-question-button]").click();
          cy.get("[data-cy=show-results-button]").click();

          // to make it easier for tests to see, make img behind heatmap
          // transparent
          cy.get("[data-cy=image-heatmap-original]").then(($img) => {
            $img.css("opacity", 0.1);
            $img.css("filter", "grayscale(1)");
          });

          cy.get(".overlayContainer").matchImageSnapshot(
            `image-heatmap-present-view_1920x1080`
          );
        });
    });

    it("participants can respond to heatmap");
  });

  describe("no response", () => {
    it("creates a no response / placeholder");
  });
});
