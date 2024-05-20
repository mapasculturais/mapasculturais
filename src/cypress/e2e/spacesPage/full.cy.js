const { clearAllFilters } = require("../../commands/clearAllFilters");

describe("Pagina de Espaços", () => {
    beforeEach(() => {
        cy.viewport(1920, 1080);
        cy.visit("/espacos/#list");
    });

    it("clica em \"Acessar\" e entra na pagina no espaço selecionado", () => {
        cy.get(`[href="https://mapas.tec.br/espaco/13/"]`).last().click();
        cy.url().should("include", "/espaco/");
        cy.contains('h1', 'Teatro Deodoro');
    });

    it("Garante que o botão limpar filtros na pagina de espaços funciona", () => {
        cy.wait(1000);
        
        clearAllFilters([
            ".form > :nth-child(1) > :nth-child(2)",
            ".verified",
            ":nth-child(2) > .mc-multiselect > :nth-child(1) > .v-popper > .mc-multiselect--input",
            ":nth-child(1) > .mc-multiselect__option",
            ":nth-child(3) > .mc-multiselect > :nth-child(1) > .v-popper > .mc-multiselect--input",
            ":nth-child(1) > .mc-multiselect__option"
        ]);

        cy.wait(1000);

        cy.get('.foundResults').then(($foundResults) => {
            let resultsTextArray, resultsCount;

            resultsTextArray = $foundResults.text().split(" ");
            resultsCount = Number(resultsTextArray[0]);
            
            cy.get(".upper.space__color").should("have.length", resultsCount);
            cy.wait(1000);
            cy.contains(resultsCount + " Espaços encontrados");
        });
    });
});