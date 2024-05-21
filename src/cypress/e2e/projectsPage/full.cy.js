const { clearAllFilters } = require("../../commands/clearAllFilters");
const { checkProjectCount } = require("../../commands/checkProjectCount");
const { checkProjectCountWithClear } = require("../../commands/checkProjectCountWithClear");

describe("Pagina de Projetos", () => {
    beforeEach(() => {
        cy.viewport(1920, 1080);
        cy.visit("/projetos");
        cy.wait(1000);
    });

    it("clica em \"Acessar\" e entra na pagina no projeto selecionado", () => {
        cy.get(":nth-child(2) > .entity-card__footer > .entity-card__footer--action > .button").click();
        cy.url().should("include", "/projeto/");
        cy.contains('h1', 'MinC');
    });

    it("Garante que os filtros de projetos funcionem", () => {
        cy.get('.mc-multiselect--input').click();
        cy.wait(1000);
        cy.get(':nth-child(18) > .mc-multiselect__option').click();
        cy.wait(1000);
        checkProjectCount();
    });

    it("Garante que o botão limpar filtros na pagina de projetos funciona", () => {
        clearAllFilters([
            ".verified",
            ".mc-multiselect--input",
            ":nth-child(1) > .mc-multiselect__option",
            ":nth-child(2) > .mc-multiselect__option",
            ":nth-child(3) > .mc-multiselect__option",
            ":nth-child(4) > .mc-multiselect__option"
        ]);

        cy.wait(1000);

        checkProjectCountWithClear();
    });
});