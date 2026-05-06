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
      .then((body) => {
        expect(body).to.have.property("image");
        expect(body.image).to.be.a("string").and.not.be.empty;
      });
  });

  it("serves an uploaded image", () => {
    api
      .uploadImage({ chimeId: chime.id, fixturePath: "goldy-650x435.jpg" })
      .then(({ image }) => {
        return api.getImage({ chimeId: chime.id, imageName: image });
      })
      .then((response) => {
        expect(response.status).to.equal(200);
        expect(response.headers["content-type"]).to.match(/image\//);
      });
  });

  it("rejects upload from a non-owner", () => {
    cy.login("student");
    api
      .uploadImage({ chimeId: chime.id, fixturePath: "goldy-650x435.jpg" })
      .then((body) => {
        // Should not return an image key — chime not found for this user
        expect(body).to.not.have.property("image");
      });
  });

  it("rejects images over 24mb", () => {
    cy.csrfToken().then((_token) => {
      // Build a blob that exceeds the 24576 KB limit
      const oversizedBlob = new Blob([new Uint8Array(25 * 1024 * 1024)], {
        type: "image/jpeg",
      });
      const formData = new FormData();
      formData.append("image", oversizedBlob, "big.jpg");
      formData.append("_token", _token);

      cy.request({
        method: "POST",
        url: `/api/chime/${chime.id}/image`,
        body: formData,
        headers: { "Content-Type": "multipart/form-data" },
        failOnStatusCode: false,
      })
        .its("status")
        .should("equal", 400);
    });
  });
});
