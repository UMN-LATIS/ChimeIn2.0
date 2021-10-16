/// <reference types="Cypress" />

const POST = "POST";
const GET = "GET";

const api = {
  getAllChimes: () =>
    cy.request({
      method: GET,
      url: "/api/chime",
    }),
  getChime: (chimeId) =>
    cy.request({
      method: GET,
      url: `/api/chime/${chimeId}`,
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
        .getAllChimes()
        .its("body")
        .should("deep.equal", []);
    });

    it("creates a new chime", () => {
      api
        .createChime("New Chime")
        .its("status")
        .should("equal", 200);

      api.getAllChimes().then(({ body }) => {
        expect(body.length).to.equal(1);
        expect(body[0].name).to.equal("New Chime");
      });
    });

    it("gets a single chime", () => {
      let chimeName = "New Chime";
      let chimeId = null;
      api
        .createChime(chimeName)
        .then((res) => {
          chimeId = res.body.id;
          return api.getChime(chimeId);
        })
        .then((res) => {
          expect(res.body.id).to.equal(chimeId);
          expect(res.body.name).to.equal(chimeName);
        });
    });
  });
});
