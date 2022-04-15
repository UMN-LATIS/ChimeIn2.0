import api from "../api/index.js";

describe("folder", () => {
  beforeEach(() => {
    cy.refreshDatabase();
    cy.seed();
    cy.login("faculty");
  });

  it("creates a folder", () => {
    let testChime;
    api
      .createChime({ name: "Test Chime" })
      .then((chime) => {
        testChime = chime;
        cy.visit(`/chime/${testChime.id}`);
        cy.get("[data-cy=new-folder-input]").type("Test Folder");
        cy.get("[data-cy=create-folder-button]").click();
      })
      .then(() => {
        // new folder should appear in the ui
        cy.get('[data-cy="folder-card"]').should("contain.text", "Test Folder");

        // verify that the folder is in the database
        api.getChime({ chimeId: testChime.id }).then((chime) => {
          expect(chime.folders.length).to.equal(1);
          expect(chime.folders[0].name).to.equal("Test Folder");
        });
      });
  });

  it("deletes a folder", () => {
    let testChime;
    api
      .createChime({ name: "Test Chime" })
      .then((chime) => {
        testChime = chime;
        return api.createFolder({ chimeId: testChime.id, name: "Test Folder" });
      })
      .then(() => {
        cy.visit(`/chime/${testChime.id}`);

        // verify that the folder is exists before deleting
        cy.get('[data-cy="folder-card"]').should("contain.text", "Test Folder");

        // delete the folder
        cy.get('[data-cy="delete-folder-button"]').click();

        // confirm the delete
        cy.get('[data-cy="confirm-delete-button"]').click();
      })
      .then(() => {
        // folder should no longer appear in the ui
        cy.get(".chime__folder-list").should("not.contain.text", "Test Folder");

        // verify that the folder isn't returned from the api
        api.getChime({ chimeId: testChime.id }).then((chime) => {
          expect(chime.folders.length).to.equal(0);
        });
      });
  });
});
