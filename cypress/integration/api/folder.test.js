/// <reference types="Cypress" />

import * as api from "./chime.js";
import { GET, POST, PATCH, DELETE } from "./methods";

describe("/api/chime/{chimeId}/folder", () => {
  beforeEach(() => {
    cy.refreshDatabase();
    cy.seed();
    cy.login({ umndid: "faculty" });
  });

  it("gets all folders within a given chime", () => {
    // create a chime
    api
      .createChime("Test Chime")
      .then((chime) => {
        return cy.request({
          method: GET,
          url: `/api/chime/${chime.id}/folder`,
        });
      })
      .then((folders) => {
        expect(folders).to.deep.equal([]);
      });
  });
});
