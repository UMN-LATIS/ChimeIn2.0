/// <reference types="Cypress" />

import api from "./index.js";

describe("folder api", () => {
  let chime = null;

  beforeEach(() => {
    cy.refreshDatabase();
    cy.seed();
    cy.login({ umndid: "faculty" });
    api
      .createChime({ name: "Test Chime" })
      .then((newChime) => (chime = newChime));
  });

  it("gets all folders within a given chime", () => {
    api.getAllFolders({ chimeId: chime.id }).then((folders) => {
      expect(folders).to.deep.equal([]);
    });
  });

  it("creates a folder within a chime", () => {
    api
      .createFolder({ chimeId: chime.id, name: "New Folder" })
      .then((folder) => {
        expect(folder.name).to.equal("New Folder");

        api.getAllFolders({ chimeId: chime.id }).then((folders) => {
          expect(folders[0].name).to.equal("New Folder");
        });
      });
  });

  it("gets a single folder", () => {
    let folderId = null;
    api
      .createFolder({ chimeId: chime.id, name: "Test Folder" })
      .then((folder) => {
        folderId = folder.id;
        return api.getFolder({ chimeId: chime.id, folderId: folder.id });
      })
      .then((folder) => {
        expect(folder.id).to.equal(folderId);
        expect(folder.name).to.equal("Test Folder");
      });
  });

  it("updates a folder", () => {
    let folderId = null;
    api
      .createFolder({ chimeId: chime.id, name: "Test Folder" })
      .then((folder) => {
        folderId = folder.id;
        return api.updateFolder({
          chimeId: chime.id,
          folderId,
          name: "Updated Name",
        });
      })
      .then((folder) => {
        expect(folder.id).to.equal(folderId);
        expect(folder.name).to.equal("Updated Name");
      });
  });

  it("deletes a folder", () => {
    let folderId = null;
    api
      .createFolder({ chimeId: chime.id, name: "Test Folder" })
      .then((folder) => {
        folderId = folder.id;
        api.deleteFolder({ chimeId: chime.id, folderId });
      })
      .then(() => {
        api.getAllFolders({ chimeId: chime.id }).should("deep.equal", []);
      });
  });
});
