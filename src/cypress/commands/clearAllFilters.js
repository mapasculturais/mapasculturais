function clearAllFilters (selectors, text) {
  selectors.forEach(selector => {
    if (selector.includes(".item > .text")) {
      cy.get(selector).click({ multiple: true, force: true });
      cy.wait(1000);
      cy.get(".tabs-component__panels").click({ force: true });
    } else if (/select$/.test(selector)) {
      cy.get(selector).select(1);
      cy.wait(1000);
    } else {
      cy.get(selector).click({force: true, multiple: true});
      cy.wait(1000);
    }
  });

  cy.get(".clear-filter").click({force: true});
  cy.wait(1000);

  cy.contains(text);
}

module.exports = { clearAllFilters };
