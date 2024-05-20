function backHomepage () {
  cy.wait(1000);
  cy.contains("a", "Home").click({ force: true });
  cy.url().should("include", "");
}

module.exports = { backHomepage };
