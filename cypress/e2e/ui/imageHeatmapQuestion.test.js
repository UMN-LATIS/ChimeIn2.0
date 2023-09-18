import api from "../api/index.js";

describe("image heatmap", () => {
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
  it("creates an image heatmap question", () => {
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

        // can't just use "Heatmap" as "Text Heatmap" will be selected first
        // press down arrow to get the second "Heatmap" option.
        // This is brittle. Maybe rename "Heatmap" as "Image Heatmap" once
        // tests are working.
        cy.get("[data-cy=question-type]").type("Image Heatmap").type("{enter}");
        cy.get("[data-cy=question-editor]").type("Image heatmap question?");

        cy.get("[data-cy=image-dropzone]").attachFile("goldy-650x435.jpg");

        // wait for upload to finish
        cy.wait("@upload", { requestTimeout: 10000 });

        // expect the image thumbnail to be displayed
        cy.get("[data-cy=image-thumbnail]").should("exist");

        cy.contains("Save").click();
        cy.wait("@apiCreateQuestion");

        // check that the question was created
        cy.get("[data-cy=question-list]").should(
          "contain",
          "Image heatmap question?"
        );

        // open question
        cy.get("[data-cy=toggle-open-question]").click();
        cy.wait("@apiOpenQuestion");

        // logout faculty, become guest user
        cy.logout();

        //  as a guest, record a response
        cy.visit(`/join/${testChime.access_code}`);

        // make sure image is loaded
        cy.get("#open-questions [data-cy=image-heatmap-target]")
          .as("image-heatmap-target")
          .should("be.visible")
          .and(($img) => {
            expect($img[0].naturalWidth).to.be.greaterThan(0);
          });

        // click on it
        // note: clicking on different coordinates should cause
        // match image snapshot fail
        cy.get("@image-heatmap-target").click(50, 100);
        cy.wait("@apiSubmitResponse");
      })
      .then(() => {
        // check that the circle appears on user interface
        cy.viewport(1920, 1080);
        cy.get("@image-heatmap-target").matchImageSnapshot(
          `image-heatmap-response-view_1920x1080`
        );
      })
      .then(() => {
        // check the circle location shows up in the correct place
        cy.get("#open-questions [data-cy=image-heatmap-click-spot]").as(
          "click-spot"
        );

        cy.get("@click-spot")
          .should("have.css", "top")
          .should((topVal) => {
            expect(topVal).to.include("px");
            expect(Number.parseInt(topVal, 10)).to.be.within(99, 101);
          });

        cy.get("@click-spot")
          .should("have.css", "left")
          .should((leftVal) => {
            expect(leftVal).to.include("px");
            expect(Number.parseInt(leftVal, 10)).to.be.within(49, 51);
          });
      })
      .then(() => {
        // check the answered questions tab
        cy.contains("Answered Questions").click();

        // check the circle location shows up in the correct place
        cy.get("#answered-questions [data-cy=image-heatmap-click-spot]").as(
          "answered-click-spot"
        );

        cy.get("@answered-click-spot")
          .should("have.css", "top")
          .should((topVal) => {
            expect(topVal).to.include("px");
            expect(Number.parseInt(topVal, 10)).to.be.within(99, 101);
          });

        cy.get("@answered-click-spot")
          .should("have.css", "left")
          .should((leftVal) => {
            expect(leftVal).to.include("px");
            expect(Number.parseInt(leftVal, 10)).to.be.within(49, 51);
          });
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

        cy.viewport(1920, 1080);
        cy.get(".overlay-container").matchImageSnapshot(
          `image-heatmap-present-view_1920x1080`
        );
      });
  });
});
