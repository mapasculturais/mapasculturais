const { confirmRecaptcha } = require("./recaptcha");

function login () {
  cy.visit("/autenticacao/");
  cy.get("input[id='email']").type("Admin@local");
  cy.get("input[id='password']").type("mapas123");
  confirmRecaptcha();
  cy.wait(1000);
  cy.get("button[type='submit']").click();
  cy.url().should("include", "/painel");
}

function loginWith (username, password) {
  cy.get("input[id='email']").type(username);
  if (password) {
    cy.get("input[id='password']").type(password);
  }
  confirmRecaptcha();
  cy.wait(1000);
  cy.get("button[type='submit']").click();
}

module.exports = { login, loginWith };
