/// <reference types="Cypress" />

const POST = "POST";
const GET = "GET";

const api = {
  getChimes: () =>
    cy.request({
      method: GET,
      url: "/api/chime",
    }),
  createChime: (name) =>
    cy.csrfToken().then((_token) =>
      cy.request({
        method: POST,
        url: "/api/chime",
        body: {
          name,
          _token,
        },
      })
    ),
};

describe("/api", () => {
  beforeEach(() => {
    cy.refreshDatabase();
    cy.seed();
    cy.login({ umndid: "faculty" });
  });

  describe("/api/chime", () => {
    it("gets a list of current chimes", () => {
      api
        .getChimes()
        .its("body")
        .should("deep.equal", []);
    });

    it("creates a new chime", () => {
      api
        .createChime("New Chime")
        .its("status")
        .should("equal", 200);

      api.getChimes().then(({ body }) => {
        expect(body.length).to.equal(1);
        expect(body[0].name).to.equal("New Chime");
      });
    });
  });
});
