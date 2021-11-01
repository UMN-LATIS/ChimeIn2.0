// ***********************************************
// This example commands.js shows you how to
// create various custom commands and overwrite
// existing commands.
//
// For more comprehensive examples of custom
// commands please read more here:
// https://on.cypress.io/custom-commands
// ***********************************************
//
//
// -- This is a parent command --
// Cypress.Commands.add('login', (email, password) => { ... })
//
//
// -- This is a child command --
// Cypress.Commands.add('drag', { prevSubject: 'element'}, (subject, options) => { ... })
//
//
// -- This is a dual command --
// Cypress.Commands.add('dismiss', { prevSubject: 'optional'}, (subject, options) => { ... })
//
//
// -- This will overwrite an existing command --
// Cypress.Commands.overwrite('visit', (originalFn, url, options) => { ... })

import "cypress-file-upload";
import { addMatchImageSnapshotCommand } from "cypress-image-snapshot/command";

/**
 * Adds an artificial network delay when responding to url for the
 * duration of the test.
 *
 * @param {string} url - the url path to delay
 * @param {number} delayInMs - the number of ms by which to delay the response
 *
 * @example cy.delayResponse('/api/chime*', 1000);
 *
 * @see https://docs.cypress.io/api/commands/intercept
 */
Cypress.Commands.add("delayResponse", (url, delayInMs) => {
  cy.intercept(url, (req) => {
    req.on("response", (res) => {
      return res.setDelay(delayInMs);
    });
  }).as("delayResponse");
});

/**
 * Adds command for `.matchImageSnapshot()`
 * By default, snapshots are written to `<rootDir>/cypress/snapshot`
 *
 * @see https://github.com/jaredpalmer/cypress-image-snapshot
 *
 * @example
 * ```js
 * // snapshot name will be the test title
 * cy.matchImageSnapshot();
 *
 * // snapshot name will be the name passed in
 * cy.matchImageSnapshot('login');
 *
 * // options object passed in
 * cy.matchImageSnapshot(options);
 *
 * // match element snapshot
 * cy.get('#login').matchImageSnapshot();
 * ```
 */
addMatchImageSnapshotCommand({
  failureThreshold: 0,
  failureThresholdType: "percent",
  customDiffConfig: { threshold: 0.05 },
  capture: "viewport",
});

Cypress.Commands.add("setResolution", (size) => {
  if (Cypress._.isArray(size)) {
    cy.viewport(size[0], size[1]);
  } else {
    cy.viewport(size);
  }
});
