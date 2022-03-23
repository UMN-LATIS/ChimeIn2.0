import * as api from "./user.js";

describe("/api/users", () => {
  beforeEach(() => {
    cy.refreshDatabase();
    cy.seed();
    cy.login("faculty");
  });

  it("gets current user info", () => {
    api.getCurrentUser().its("umndid").should("eq", "faculty");
  });
});
