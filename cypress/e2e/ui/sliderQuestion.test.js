import api from "../api/index.js";

describe("slider", () => {
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

  it("creates a qualitative slider question and lets participant respond", () => {
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
        cy.get("[data-cy=question-type]").type("Slider{enter}");
        cy.get("[data-cy=question-editor]").type("Slider question?");
        cy.get("#left_choice_text").type("Bad");
        cy.get("#right_choice_text").type("Good");
        cy.contains("Save").click();
        cy.wait("@apiCreateQuestion");

        // check that the question was created
        cy.get("[data-cy=question-list]").should("contain", "Slider question?");

        // open question
        cy.get("[data-cy=toggle-open-question]").click();
        cy.wait("@apiOpenQuestion");

        // logout faculty, become guest user
        cy.logout();
      })
      .then(() => {
        // as a guest, record a response
        cy.visit(`/join/${testChime.access_code}`);
        cy.get("[data-cy=slider-response-input]")
          .invoke("val", 25)
          .then(($input) => {
            // using native event triggering rather
            // than `.trigger`.
            // see: https://github.com/cypress-io/cypress/issues/1570

            $input[0].dispatchEvent(new Event("change"));
          });
        cy.wait("@apiSubmitResponse");
      })
      .then(() => {
        // login as faculty
        cy.login("faculty");
        cy.visit(`/chime/${testChime.id}/folder/${testFolder.id}`);
        cy.get("[data-cy=present-question-button]").click();
        cy.get("[data-cy=show-results-button]").click();

        // wait for animation to complete
        // eslint-disable-next-line cypress/no-unnecessary-waiting
        cy.wait(2000);

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
          expect(barHeights.filter((_, i) => i !== tallBarIndex)).to.deep.equal(
            Array(15).fill(0.5),
          );
        });
      });
  });

  it("lets range be quantitative");
});
