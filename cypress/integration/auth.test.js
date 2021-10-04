describe("authentication", () => {
  beforeEach(() => {
    cy.refreshDatabase();
    cy.seed();
    cy.visit("/");
  });

  context("when the user isnt authenticated", () => {
    it("greets them as guest user", () => {
      cy.get("main h1").contains("Guest User");
    });

    it("lets user login with valid credentials", () => {
      cy.contains("Log in", { matchCase: false }).click();

      // login
      cy.get("#username").type("admin");
      cy.get("#password").type("admin");
      cy.get(".login-box [type=submit]").click();

      // success
      cy.url().should("eq", Cypress.config().baseUrl + "/");
      cy.get("main h1").should("not.contain", "guest user");
    });

    it("does not let user in with invalid credentials", () => {
      cy.contains("Log in", { matchCase: false }).click();

      // BAD login attempt
      cy.get("#username").type("admin");
      cy.get("#password").type("wrong");
      cy.get(".login-box [type=submit]").click();

      // Failure returns to login page
      cy.get("body").contains("password", { matchCase: false });
    });
  });

  context("when the user has previously authenticated", () => {
    it("recognizes the user", () => {
      // login successfully (faking this using the api)
      cy.login({ umndid: "admin" });

      // now test that it recognizes our credentials
      cy.visit("/");
      cy.get("main h1").should("not.contain", "guest user");
    });
  });
});
