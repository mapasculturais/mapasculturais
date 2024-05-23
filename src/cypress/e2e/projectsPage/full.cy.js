const { clearAllFilters } = require("../../commands/clearAllFilters");
const { checkFilterCountOf } = require("../../commands/checkFilterCountOf");

describe("Pagina de Projetos", () => {
    beforeEach(() => {
        cy.viewport(1920, 1080);
        cy.visit("/projetos");
        cy.wait(1000);
    });

    it("clica em \"Acessar\" e entra na pagina no projeto selecionado", () => {
        cy.get(":nth-child(2) > .entity-card__footer > .entity-card__footer--action > .button").click();
        cy.url().should("include", "/projeto/");
    });

    it("Garante que os filtros de tipos de projetos funcionem", () => {
        cy.contains("Tipos de projetos");
        cy.get('.mc-multiselect--input').click();
        cy.wait(1000);
        cy.get(':nth-child(18) > .mc-multiselect__option').click();
        cy.wait(1000);
        checkFilterCountOf("project");
        cy.reload();
        cy.wait(1000);
        cy.get('.mc-multiselect--input').click();
        cy.get(':nth-child(18) > .mc-multiselect__option').click();
        cy.wait(1000);
        checkFilterCountOf("project");
    });

    it("Garante que o botão limpar filtros na pagina de projetos funciona", () => {
        checkFilterCountOf("project");
        
        clearAllFilters([
            ".verified",
            ".mc-multiselect--input",
            ":nth-child(1) > .mc-multiselect__option",
            ":nth-child(2) > .mc-multiselect__option",
            ":nth-child(3) > .mc-multiselect__option",
            ":nth-child(4) > .mc-multiselect__option"
        ]);

        cy.wait(1000);

        checkFilterCountOf("project");
    });
});