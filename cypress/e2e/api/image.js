export function uploadImage({ chimeId, fixturePath, failOnStatusCode = true }) {
  if (!chimeId) throw Error("chimeId is required");
  if (!fixturePath) throw Error("fixturePath is required");

  return cy.fixture(fixturePath, null).then((fileBuffer) => {
    const blob = new Blob([fileBuffer], { type: "image/jpeg" });
    const formData = new FormData();
    formData.append("image", blob, fixturePath);

    return cy.csrfToken().then((_token) => {
      formData.append("_token", _token);

      return cy.window({ log: false }).then((win) =>
        win
          .fetch(`/api/chime/${chimeId}/image`, {
            method: "POST",
            body: formData,
            credentials: "same-origin",
          })
          .then((res) => {
            if (failOnStatusCode && !res.ok) {
              throw new Error(
                `Upload failed: ${res.status} ${res.statusText}`,
              );
            }
            const contentType = res.headers.get("content-type") || "";
            const parse = contentType.includes("application/json")
              ? res.json()
              : res.text().then((text) => ({ message: text }));
            return parse.then((body) => ({ status: res.status, body }));
          }),
      );
    });
  });
}

