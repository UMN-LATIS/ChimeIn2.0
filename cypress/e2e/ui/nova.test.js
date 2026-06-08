/// <reference types="Cypress" />

describe("Laravel Nova admin (/admin)", () => {
  beforeEach(() => {
    cy.refreshDatabase();
    cy.seed();
  });

  context("when the user is not a global admin", () => {
    it("forbids access to /admin with a 403", () => {
      // Loading the app first establishes a non-admin guest session (see the
      // AuthIfNecessary middleware). Nova's authorization gate then returns a
      // 403 for that guest, rather than redirecting an anonymous request to
      // the Nova login page.
      cy.visit("/");

      cy.request({ url: "/admin", failOnStatusCode: false })
        .its("status")
        .should("eq", 403);
    });
  });

  context("when logged in as a global admin", () => {
    beforeEach(() => {
      cy.login("admin");
    });

    it("loads the dashboard with the Get Started panel", () => {
      cy.visit("/admin");

      // The Main dashboard renders Nova's Help card, headed "Get Started".
      cy.contains("Get Started", { timeout: 10000 }).should("be.visible");
    });

    it("lists seeded chimes at /admin/resources/chimes", () => {
      cy.visit("/admin/resources/chimes");

      // The ChimesTableSeeder creates a chime named "A chime with Responses".
      cy.get("table", { timeout: 10000 }).should(
        "contain",
        "A chime with Responses"
      );
    });
  });
});
