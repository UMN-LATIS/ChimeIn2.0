/// <reference types="Cypress" />

import * as api from "./chime.js";

describe("chime api", () => {
  beforeEach(() => {
    cy.refreshDatabase();
    cy.seed();
    cy.login({ umndid: "faculty" });
  });

  it("gets a list of current chimes", () => {
    api.getAllChimes().should("deep.equal", []);
  });

  it("creates a new chime", () => {
    api
      .createChime("New Chime")
      .its("name")
      .should("equal", "New Chime");

    api.getAllChimes().then((chimes) => {
      expect(chimes.length).to.equal(1);
      expect(chimes[0].name).to.equal("New Chime");
    });
  });

  it("gets a given chime by id", () => {
    let chimeName = "New Chime";
    let chimeId = null;
    api
      .createChime(chimeName)
      .then((chime) => {
        chimeId = chime.id;
        return api.getChime(chimeId);
      })
      .then((chime) => {
        expect(chime.id).to.equal(chimeId);
        expect(chime.name).to.equal(chimeName);
      });
  });

  it("updates a given chime", () => {
    let chimeId = null;
    api
      .createChime("Test Chime")
      .then((chime) => {
        chimeId = chime.id;
        return api.updateChime(chimeId, { name: "Updated Chime Name" });
      })
      .then((body) => {
        expect(body.success).to.equal(true);
        api
          .getChime(chimeId)
          .its("name")
          .should("equal", "Updated Chime Name");
      });
  });

  it("deletes a given chime", () => {
    let chimeId = null;
    api
      .createChime("Test Chime")
      .then((chime) => {
        chimeId = chime.id;
        return api.deleteChime(chimeId);
      })
      .then((body) => {
        api.getAllChimes().should("deep.equal", []);
      });
  });
});
