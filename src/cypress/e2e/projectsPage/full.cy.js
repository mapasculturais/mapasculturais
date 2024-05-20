const { clearAllFilters } = require("../../commands/clearAllFilters");

describe("Pagina de Projetos", () => {
    beforeEach(() => {
        cy.viewport(1920, 1080);
        cy.visit("/projetos");
    });

    it("clica em \"Acessar\" e entra na pagina no projeto selecionado", () => {
        cy.get(":nth-child(2) > .entity-card__footer > .entity-card__footer--action > .button").click();
        cy.url().should("include", "/projeto/");
        cy.contains('h1', 'MinC');
    });

    it("Garante que o botão limpar filtros na pagina de projetos funciona", () => {
        clearAllFilters([
            ".verified",
            ".mc-multiselect--input",
            ":nth-child(1) > .mc-multiselect__option"
        ]);

        cy.wait(1000);

        let countBeforeClear;
        cy.get('.foundResults').then(($foundResults) => {
            let resultsTextArray;
            resultsTextArray = $foundResults.text().split(" ");
            countBeforeClear = Number(resultsTextArray[0]);
        });

        cy.get('.foundResults').then(($foundResults) => {
            let resultsTextArray, resultsCount;

            resultsTextArray = $foundResults.text().split(" ");
            resultsCount = Number(resultsTextArray[0]);
            
            cy.get(".upper.project__color").should("have.length", resultsCount);
            cy.wait(1000);
            cy.get(".upper.project__color").should("have.length", countBeforeClear);
            cy.wait(1000);
            cy.contains(resultsCount + " Projetos encontrados");
        });
    });
});