import api from "../api/index.js";

describe("image response", () => {
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

    // set up an intecept to watch image uploads
    // so that we can wait for upload to complete
    cy.intercept({
      method: "POST",
      url: "/api/chime/**/image",
    }).as("upload");
  });

  it("creates an image response question, guest responds with image and alt, presenter views response", () => {
    let testChime;
    let testFolder;

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
        cy.wait("@apiCreateQuestion");

        // check that the question was created
        cy.get("[data-cy=question-list]").should(
          "contain",
          "Image response question?"
        );

        // open question
        cy.get("[data-cy=toggle-open-question]").click();
        cy.wait("@apiOpenQuestion");

        // logout faculty, become guest user
        cy.logout();

        // as a guest, attach an image
        cy.visit(`/join/${testChime.access_code}`);
        cy.get("[data-cy=image-dropzone]").attachFile("goldy-650x435.jpg");

        // wait for upload to finish
        cy.wait("@upload", { requestTimeout: 10000 });

        // expect the image preview to be displayed
        cy.get("[data-cy=image-preview]").should("exist");

        // add alt text
        cy.get('.textarea-input > [data-cy="alt-text-input"]').type("Goldy");
        // submit the response
        cy.contains("Save").click();

        // expect the thumbnail of response to be displayed
        cy.get("[data-cy=image-response-thumbnail]").should("exist");

        // login as faculty
        cy.login("faculty");
        cy.visit(`/chime/${testChime.id}/folder/${testFolder.id}`);
        cy.get("[data-cy=present-question-button]").click();
        cy.get("[data-cy=show-results-button]").click();

        // expect an image image to be displayed
        cy.get("[data-cy=image-responses]")
          .find("img")
          .should("have.attr", "src")
          .should("include", ".jpg");
        // .should("have.attr", "alt", "Goldy");
      });
  });

  it("allows multiple responses", () => {
    let testChime;
    let testFolder;

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

        // make it a multiple response question
        cy.get("[data-cy=allow-multiple-responses-checkbox]").click();

        // add question text and save
        cy.get("[data-cy=question-editor]").type("Image response question?");
        cy.contains("Save").click();
        cy.wait("@apiCreateQuestion");

        // open question
        cy.get("[data-cy=toggle-open-question]").click();
        cy.wait("@apiOpenQuestion");

        // logout faculty, become guest user
        cy.logout();

        // as a guest, attach an image
        cy.visit(`/join/${testChime.access_code}`);
        cy.get("[data-cy=image-dropzone]").attachFile("goldy-650x435.jpg");

        // wait for upload to finish
        cy.wait("@upload", { requestTimeout: 10000 });

        // add alt text
        cy.get('.textarea-input > [data-cy="alt-text-input"]').type("Goldy");
        cy.contains("Save").click();

        // expect the image thumbnail to be displayed
        cy.get("[data-cy=image-response-thumbnail]")
          .should("exist")
          .should("have.attr", "alt", "Goldy");

        // add a second image
        cy.get("[data-cy=image-dropzone]").attachFile(
          "goldy-with-cape-650x435.jpg"
        );

        // wait for upload to finish
        cy.wait("@upload", { requestTimeout: 10000 });

        // add alt text
        cy.get('.textarea-input > [data-cy="alt-text-input"]').type(
          "Goldy with Cape"
        );
        cy.contains("Add Response").click();

        // most recent response should be displayed
        cy.get("[data-cy=image-response-thumbnail]")
          .should("exist")
          .should("have.attr", "alt", "Goldy with Cape");

        // both responses should show in the Answered Questions tab
        cy.contains("Answered Questions").click();
        cy.get("#answered-questions [data-cy=image-response-thumbnail]").should(
          "have.length",
          2
        );

        // login as faculty
        cy.login("faculty");
        cy.visit(`/chime/${testChime.id}/folder/${testFolder.id}`);
        cy.get("[data-cy=present-question-button]").click();
        cy.get("[data-cy=show-results-button]").click();

        // faculty should see both responses as thumbnails
        cy.get("[data-cy=image-responses] img")
          .should("have.length", 2)
          .then((images) => {
            expect(images[0].alt).to.equal("Goldy");
            expect(images[1].alt).to.equal("Goldy with Cape");
          });

        // after clicking the manage button, faculty should see both responses
        // and their alt attributes in the responses table
        cy.contains("Manage").click();
        cy.get("[data-cy=responses-table]").should("exist");
        cy.get("[data-cy=responses-table]").should("contain", "Goldy");
        cy.get("[data-cy=responses-table]").should(
          "contain",
          "Goldy with Cape"
        );
      });
  });
});
