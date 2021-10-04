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
Cypress.Commands.add('delayResponse', (url, delayInMs) => {
  cy.intercept(url, (req) => {
    req.on('response', (res) => {
      return res.setDelay(delayInMs);
    });
  }).as('delayResponse');
});
