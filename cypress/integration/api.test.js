/// <reference types="Cypress" />

const POST = "POST";
const GET = "GET";
const PATCH = "PATCH";
const PUT = "PUT";

function getAllChimes() {
  return cy
    .request({
      method: GET,
      url: "/api/chime",
    })
    .its("body");
}

function getChime(chimeId) {
  return cy
    .request({
      method: GET,
      url: `/api/chime/${chimeId}`,
    })
    .its("body");
}

function createChime(name) {
  return cy.csrfToken().then((_token) => {
    return cy
      .request({
        method: POST,
        url: "/api/chime",
        body: {
          name,
          _token,
        },
      })
      .its("body");
  });
}

function updateChime(chimeId, updates) {
  return cy.csrfToken().then((_token) => {
    return cy
      .request({
        method: POST,
        url: `/api/chime/${chimeId}`,
        body: {
          _token,
          _method: PATCH,
          ...updates,
        },
      })
      .its("body");
  });
}

const api = {
  getAllChimes,
  getChime,
  createChime,
  updateChime,
};

describe("/api", () => {
  beforeEach(() => {
    cy.refreshDatabase();
    cy.seed();
    cy.login({ umndid: "faculty" });
  });

  describe("/api/chime", () => {
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
  });
});
