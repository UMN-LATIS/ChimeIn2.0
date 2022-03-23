import { createChime } from "./chime.js";
import * as api from "./chimeUser.js";

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

  it("lets presenters remove a participant from a chime", () => {
    let student = null;

    cy.logout();
    cy.login("student");
    cy.visit("/join/" + testChime.access_code);
    cy.login("faculty");

    api
      .getChimeUsers({ chimeId: testChime.id })
      .then((users) => {
        student = users[1];
        expect(users).to.have.length(2);

        api.removeChimeUser({ chimeId: testChime.id, userId: student.id });
      })
      .then(() => api.getChimeUsers({ chimeId: testChime.id }))
      .then((users) => {
        // only the faculty remains
        expect(users).to.have.length(1);
        expect(users[0].email).to.equal("latistecharch+faculty@umn.edu");
      });
  });

  it("lets presenters remove another presenter from a chime", () => {
    let faculty = null;
    let student = null;

    cy.logout();
    cy.login("student");
    cy.visit("/join/" + testChime.access_code);
    cy.login("faculty");

    api
      .getChimeUsers({ chimeId: testChime.id })
      .then((users) => {
        [faculty, student] = users;
        expect(users).to.have.length(2);

        api.updateChimeUser({
          chimeId: testChime.id,
          userId: student.id,
          permissionNumber: 300,
        });
      })
      .then(() => {
        // now that the student's a presenter,
        // they should be able to remove the faculty
        cy.login("student");
        api.removeChimeUser({ chimeId: testChime.id, userId: faculty.id });
      })
      .then(() => api.getChimeUsers({ chimeId: testChime.id }))
      .then((users) => {
        // only the faculty remains
        expect(users).to.have.length(1);
        expect(users[0].email).to.equal("latistecharch+student@umn.edu");
      });
  });

  it("lets participants remove themselves with userId = 'self'", () => {
    // let faculty = null;
    // let student = null;

    cy.logout();
    cy.login("student");
    cy.visit("/join/" + testChime.access_code);
    cy.login("faculty");

    api
      .getChimeUsers({ chimeId: testChime.id })
      .then((users) => {
        // [faculty, student] = users;
        expect(users).to.have.length(2);
      })
      .then(() => {
        // now that the student's a presenter,
        // they should be able to remove the faculty
        cy.login("student");
        api.removeChimeUser({ chimeId: testChime.id, userId: "self" });
      })
      .then(() => {
        cy.login("faculty");
        return api.getChimeUsers({ chimeId: testChime.id });
      })
      .then((users) => {
        // only the faculty remains
        expect(users).to.have.length(1);
        expect(users[0].email).to.equal("latistecharch+faculty@umn.edu");
      });
  });

  it("lets presenters remove themselves when there are other presenters", () => {
    let student = null;

    cy.logout();
    cy.login("student");
    cy.visit("/join/" + testChime.access_code);
    cy.login("faculty");

    api
      .getChimeUsers({ chimeId: testChime.id })
      .then((users) => {
        student = users[1];
        expect(users).to.have.length(2);

        api.updateChimeUser({
          chimeId: testChime.id,
          userId: student.id,
          permissionNumber: 300,
        });
      })
      .then(() => {
        // now that the student's a presenter,
        // they should be able to remove the faculty
        cy.login("student");
        api.removeChimeUser({ chimeId: testChime.id, userId: "self" });
      })
      .then(() => {
        cy.login("faculty");
        api.getChimeUsers({ chimeId: testChime.id });
      })
      .then((users) => {
        // only the faculty remains
        expect(users).to.have.length(1);
        expect(users[0].email).to.equal("latistecharch+faculty@umn.edu");
      });
  });

  it("does not let presenters remove themselves if they are the only presenter", () => {
    api
      .getChimeUsers({ chimeId: testChime.id })
      .then(() => {
        return api.removeChimeUser(
          { chimeId: testChime.id, userId: "self" },
          {
            failOnStatusCode: false,
          }
        );
      })
      .then((response) => {
        expect(response.status).to.equal(405);
        api.getChimeUsers({ chimeId: testChime.id });
      })
      .then((users) => {
        // only the faculty remains
        expect(users).to.have.length(1);
        expect(users[0].email).to.equal("latistecharch+faculty@umn.edu");
      });
  });
});
