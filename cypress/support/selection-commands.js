/**
 * Commands for selecting/highlighting text on a page.
 * src: https://github.com/netlify/netlify-cms/blob/master/cypress/support/commands.js
 * license: MIT
 * author: @netlify
 *
 * see also: https://github.com/cypress-io/cypress/issues/2839
 */

/**
 * low level command used by `setSelection`
 */
Cypress.Commands.add("selection", { prevSubject: true }, (subject, fn) => {
  // Trigger mousedown and pass the subject to the provided function
  cy.wrap(subject).trigger("mousedown");
  fn(subject);
  cy.wrap(subject).trigger("mouseup");

  cy.document().trigger("selectionchange");

  return cy.wrap(subject);
});

/**
 * Selects texts on a page
 *
 * @example
 * ```
 * // Types "foo" and then selects "fo"
 * cy.get('input')
 *   .type('foo')
 *   .setSelection('fo')
 *
 * // Types "foo", "bar", "baz", and "qux" on separate lines,
 * // then selects "foo", "bar", and "baz"
 * cy.get('textarea')
 *   .type('foo{enter}bar{enter}baz{enter}qux{enter}')
 *   .setSelection('foo', 'baz')
 */
Cypress.Commands.add(
  "setSelection",
  { prevSubject: true },
  (subject, query, endQuery) => {
    return cy.wrap(subject).selection(($el) => {
      if (typeof query === "string") {
        const anchorNode = getTextNode($el[0], query);
        const focusNode = endQuery ? getTextNode($el[0], endQuery) : anchorNode;
        const anchorOffset = anchorNode.wholeText.indexOf(query);
        const focusOffset = endQuery
          ? focusNode.wholeText.indexOf(endQuery) + endQuery.length
          : anchorOffset + query.length;
        setBaseAndExtent(anchorNode, anchorOffset, focusNode, focusOffset);
      } else if (typeof query === "object") {
        const el = $el[0];
        const anchorNode = getTextNode(el.querySelector(query.anchorQuery));
        const anchorOffset = query.anchorOffset || 0;
        const focusNode = query.focusQuery
          ? getTextNode(el.querySelector(query.focusQuery))
          : anchorNode;
        const focusOffset = query.focusOffset || 0;
        setBaseAndExtent(anchorNode, anchorOffset, focusNode, focusOffset);
      }
    });
  },
);

/**
 * low level command used by `setCursorBefore` and `setCursorAfter`
 */
Cypress.Commands.add(
  "setCursor",
  { prevSubject: true },
  (subject, query, atStart) => {
    return cy.wrap(subject).selection(($el) => {
      const node = getTextNode($el[0], query);
      const offset =
        node.wholeText.indexOf(query) + (atStart ? 0 : query.length);
      const document = node.ownerDocument;
      document.getSelection().removeAllRanges();
      document.getSelection().collapse(node, offset);
    });
  },
);

/**
 * sets cursor location on the page before the given text
 *
 * @example
 * ```
 * // Types "foo" and then sets the cursor at the beginning of the word
 * cy.get('input')
 *   .type('foo')
 *   .setCursorBefore('foo')
 * ```
 */

Cypress.Commands.add(
  "setCursorBefore",
  { prevSubject: true },
  (subject, query) => {
    cy.wrap(subject).setCursor(query, true);
  },
);

/**
 * sets cursor location on the page after the given text
 *
 * @example
 * ```
 * // Types "foo" and then sets the cursor after 'fo' and before the last 'o'
 * cy.get('input')
 *   .type('foo')
 *   .setCursorAfter('fo')
 * ```
 */

Cypress.Commands.add(
  "setCursorAfter",
  { prevSubject: true },
  (subject, query) => {
    cy.wrap(subject).setCursor(query);
  },
);

function getTextNode(el, match) {
  const walk = document.createTreeWalker(el, NodeFilter.SHOW_TEXT, null, false);
  if (!match) {
    return walk.nextNode();
  }

  let node;
  while ((node = walk.nextNode())) {
    if (node.wholeText.includes(match)) {
      return node;
    }
  }
}

function setBaseAndExtent(...args) {
  const document = args[0].ownerDocument;
  document.getSelection().removeAllRanges();
  document.getSelection().setBaseAndExtent(...args);
}
