/// <reference types="Cypress" />

import api from "./index.js";

describe("image api", () => {
  let chime = null;

  beforeEach(() => {
    cy.refreshDatabase();
    cy.seed();
    cy.login("faculty");
    api.createChime({ name: "Image Test Chime" }).then((newChime) => {
      chime = newChime;
    });
  });

  it("uploads an image and returns a filename", () => {
    api
      .uploadImage({ chimeId: chime.id, fixturePath: "goldy-650x435.jpg" })
      .then(({ body }) => {
        expect(body).to.have.property("image");
        expect(body.image).to.be.a("string").and.not.be.empty;
      });
  });

  it("serves an uploaded image via the storage symlink", () => {
    api
      .uploadImage({ chimeId: chime.id, fixturePath: "goldy-650x435.jpg" })
      .then(({ body }) => {
        return cy.request({ url: `/storage/${body.image}` });
      })
      .then((response) => {
        expect(response.status).to.equal(200);
        expect(response.headers["content-type"]).to.match(/image\//);
      });
  });

  it("rejects upload from a non-owner", () => {
    cy.login("student");
    api
      .uploadImage({
        chimeId: chime.id,
        fixturePath: "goldy-650x435.jpg",
        failOnStatusCode: false,
      })
      .then(({ status, body }) => {
        expect(status).to.equal(400);
        expect(body).to.not.have.property("image");
      });
  });

  it("rejects images over 24mb", () => {
    cy.csrfToken().then((_token) => {
      const oversizedBlob = new Blob([new Uint8Array(25 * 1024 * 1024)], {
        type: "image/jpeg",
      });
      const formData = new FormData();
      formData.append("image", oversizedBlob, "big.jpg");
      formData.append("_token", _token);

      cy.window({ log: false }).then((win) =>
        win
          .fetch(`/api/chime/${chime.id}/image`, {
            method: "POST",
            body: formData,
            credentials: "same-origin",
          })
          .then((res) => {
            expect(res.status).to.equal(400);
          }),
      );
    });
  });
});
