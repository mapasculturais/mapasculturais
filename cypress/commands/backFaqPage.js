function backFaqPage () {
  cy.wait(1000);
  cy.contains("a", "Dúvidas frequentes").click();
  cy.url().should("include", "perguntas-frequentes/");
}

module.exports = { backFaqPage };
