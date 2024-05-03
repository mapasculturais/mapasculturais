const { backHomepage } = require("./backHomepage");

function backHomepageCompact () {
  cy.wait(1000);
  cy.get(".mc-header-menu__btn-mobile").click();
  backHomepage();
}

module.exports = { backHomepageCompact };
