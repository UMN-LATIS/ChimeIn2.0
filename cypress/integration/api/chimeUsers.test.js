import { createChime } from "./chime.js";
import * as api from "./chimeUser.js";
describe("chimeUser api", () => {
  let testChime = null;

  beforeEach(() => {
    cy.refreshDatabase();
    cy.seed();
    cy.login("faculty");
    createChime({ name: "New Chime" }).then((chime) => {
      testChime = chime;
    });
  });

  it("gets a list of chime users", () => {
    api.getChimeUsers({ chimeId: testChime.id }).should("have.length", 1);
  });

});
