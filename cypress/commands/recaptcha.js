function confirmRecaptcha () {
  cy.get("iframe")
    .first()
    .its("0.contentDocument.body")
    .should("not.be.undefined")
    .and("not.be.empty")
    .then(cy.wrap)
    .find("#recaptcha-anchor")
    .should("be.visible")
    .click();
}

module.exports = { confirmRecaptcha };
