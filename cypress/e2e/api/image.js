import { GET } from "./methods";

export function uploadImage({ chimeId, fixturePath }) {
  if (!chimeId) throw Error("chimeId is required");
  if (!fixturePath) throw Error("fixturePath is required");

  return cy.fixture(fixturePath, "binary").then((fileContent) => {
    const blob = Cypress.Blob.binaryStringToBlob(fileContent, "image/jpeg");
    const formData = new FormData();
    formData.append("image", blob, fixturePath);

    return cy.csrfToken().then((_token) => {
      formData.append("_token", _token);
      return cy
        .request({
          method: "POST",
          url: `/api/chime/${chimeId}/image`,
          body: formData,
          headers: { "Content-Type": "multipart/form-data" },
        })
        .its("body");
    });
  });
}

export function getImage({ chimeId, imageName }) {
  if (!chimeId) throw Error("chimeId is required");
  if (!imageName) throw Error("imageName is required");

  return cy.request({
    method: GET,
    url: `/api/chime/${chimeId}/image/${imageName}`,
    encoding: "binary",
  });
}
