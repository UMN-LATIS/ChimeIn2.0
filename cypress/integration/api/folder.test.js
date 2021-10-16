/// <reference types="Cypress" />

import * as chimeApi from "./chime.js";
import * as folderApi from "./folder.js";
import { GET, POST, PATCH, DELETE, PUT } from "./methods";

const api = {
  ...chimeApi,
  ...folderApi,
};

describe("/api folder", () => {
  let chime = null;

  beforeEach(() => {
    cy.refreshDatabase();
    cy.seed();
    cy.login({ umndid: "faculty" });
    api.createChime("Test Chime").then((newChime) => (chime = newChime));
  });

  it("gets all folders within a given chime", () => {
    api.getAllFoldersInChime(chime.id).then((folders) => {
      expect(folders).to.deep.equal([]);
    });
  });

  it("creates a folder within a chime", () => {
    api.createFolderInChime(chime.id, { name: "New Folder" }).then((folder) => {
      expect(folder.name).to.equal("New Folder");

      api.getAllFoldersInChime(chime.id).then((folders) => {
        expect(folders[0].name).to.equal("New Folder");
      });
    });
  });

  it("gets a single folder", () => {
    let folderId = null;
    api
      .createFolderInChime(chime.id, { name: "Test Folder" })
      .then((folder) => {
        folderId = folder.id;
        return api.getFolderInChime(chime.id, folder.id);
      })
      .then((folder) => {
        expect(folder.id).to.equal(folderId);
        expect(folder.name).to.equal("Test Folder");
      });
  });

  it("updates a folder", () => {
    let folderId = null;
    api
      .createFolderInChime(chime.id, { name: "Test Folder" })
      .then((folder) => {
        folderId = folder.id;
        return api.updateFolderInChime(chime.id, folderId, {
          name: "Updated Name",
        });
      })
      .then((folder) => {
        expect(folder.id).to.equal(folderId);
        expect(folder.name).to.equal("Updated Name");
      });
  });
});
