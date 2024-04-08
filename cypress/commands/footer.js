const { login } = require("./login");

function footer () {
  if (cy.contains("Entrar")) {
    login();
  } else {
    cy.contains("Minha conta");
  }

  const url = [
    "/oportunidades/",
    "/eventos/#list",
    "/agentes/#list",
    "/espacos/#list",
    "/projetos/",
    "/minhas-oportunidades/#publish",
    "/meus-eventos/#publish",
    "/meus-agentes/#publish",
    "/meus-espacos/#publish"
  ];

  for (let i = 2; i <= 6; i++) {
    cy.get(`.main-footer__content--links > :nth-child(1) > :nth-child(${i}) > a`).click();
    if (i <= url.length) {
      cy.url().should("include", url[i - 2]);
    }
  }

  for (let i = 2; i <= 5; i++) {
    cy.get(`.main-footer__content--links > :nth-child(2) > :nth-child(${i}) > a`).click();
    if (i <= url.length) {
      cy.url().should("include", url[i + 3]);
    }
  }

  cy.get(".main-footer__content--links > :nth-child(3) > :nth-child(2) > a").click();
  cy.url().should("include", "/perguntas-frequentes/");
}

module.exports = { footer };
