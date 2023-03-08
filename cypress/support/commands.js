// ***********************************************
// This example commands.js shows you how to
// create various custom commands and overwrite
// existing commands.
//
// For more comprehensive examples of custom
// commands please read more here:
// https://on.cypress.io/custom-commands
// ***********************************************

import "cypress-file-upload";
import { addMatchImageSnapshotCommand } from "@simonsmith/cypress-image-snapshot/command";

addMatchImageSnapshotCommand({
  failureThreshold: 0.1,
  failureThresholdType: "percent",
  customDiffConfig: {
    threshold: 0.1,
  },
  capture: "viewport",
});

/**
 * Adds an artificial network delay when responding to url for
 *  the duration of the test.
 */
Cypress.Commands.add("delayResponse", (url, delayInMs) => {
  cy.intercept(url, (req) => {
    req.on("response", (res) => {
      return res.setDelay(delayInMs);
    });
  }).as("delayResponse");
});
