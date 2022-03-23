import { createChime } from "./chime.js";
import * as api from "./chimeUser.js";
// import { getCurrentUser } from "./user.js";

// function addGuestUserToChime({ access_code }) {
//   cy.logout();

//   // join chime as guest
//   return cy.visit("/join/" + access_code).then(() => getCurrentUser());
// }

describe("chimeUser api", () => {
  let testChime = null;

  beforeEach(() => {
    cy.refreshDatabase();
    cy.seed();
    cy.login("faculty");
    createChime({ name: "New Chime" }).then((chime) => {
      testChime = chime;
    });
  });

  it("gets a list of chime users", () => {
    api.getChimeUsers({ chimeId: testChime.id }).should("have.length", 1);
  });

  it("lets presenter change a users role", () => {
    cy.logout();
    cy.login("student");
    cy.visit("/join/" + testChime.access_code);
    cy.login("faculty");

    api
      .getChimeUsers({ chimeId: testChime.id })
      .then((users) => {
        const user = users[1];
        expect(user.permission_number).to.equal(100);
        api.updateChimeUser({
          chimeId: testChime.id,
          userId: user.id,
          permissionNumber: 300,
        });
      })
      .then(() => {
        api.getChimeUsers({ chimeId: testChime.id }).then((users) => {
          const user = users[1];
          expect(user.permission_number).to.equal(300);
        });
      });
  });

  it("does not allow participants/guests get users", () => {
    cy.logout();
    cy.login("student");
    cy.visit("/join/" + testChime.access_code);

    api
      .getChimeUsers({ chimeId: testChime.id }, { failOnStatusCode: false })
      .then((response) => {
        expect(response.status).to.equal(403);
      });
  });

  it("does not allow participants/guests to change role", () => {
    let student = null;

    cy.logout();
    cy.login("student");
    cy.visit("/join/" + testChime.access_code)
      .then(() => {
        // get a valid student ID
        cy.login("faculty");
        return api.getChimeUsers(
          { chimeId: testChime.id },
          { failOnStatusCode: false }
        );
      })
      .then((users) => {
        student = users.find((u) => u.permission_number === 100);
      })
      .then(() => {
        cy.logout();
        cy.login("student");
        return api.updateChimeUser(
          {
            chimeId: testChime.id,
            userId: student.id,
            permissionNumber: 300,
          },
          { failOnStatusCode: false }
        );
      })
      .then((response) => {
        expect(response.status).to.equal(403);
      });
  });

  it("gets info about current user");

  // it("should remove a user from a chime", () => {
  //   let guestUser = null;

  //   addGuestUserToChime(testChime)
  //     .then((user) => {
  //       guestUser = user;

  //       // verify that guest is in chime
  //       cy.login("faculty");
  //       api.getChimeUsers({ chimeId: testChime.id }).should("have.length", 2);
  //     })
  //     .then(() => {
  //       api
  //         .removeChimeUser({ chimeId: testChime.id, userId: guestUser.id })
  //         .then(() => {
  //           api
  //             .getChimeUsers({ chimeId: testChime.id })
  //             .should("have.length", 1);
  //         });
  //     });
  // });
});
