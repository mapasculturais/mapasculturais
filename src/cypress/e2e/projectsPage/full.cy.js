const { clearAllFilters } = require("../../commands/clearAllFilters");

describe("Pagina de Projetos", () => {
    beforeEach(() => {
        cy.viewport(1920, 1080);
        cy.visit("/projetos");
    });

    it("clica em \"Acessar\" e entra na pagina no projeto selecionado", () => {
        cy.get(":nth-child(2) > .entity-card__footer > .entity-card__footer--action > .button").click();
        cy.url().should("include", "/projeto/19/#info");
        cy.contains('h1', 'Oficina');
    });

    it("Garante que o botão limpar filtros na pagina de projetos funciona", () => {
        clearAllFilters([
            ".verified",
            ".mc-multiselect--input",
            ":nth-child(1) > .mc-multiselect__option"
        ], "19 Projetos encontrados");
    });
});