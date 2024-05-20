const { clearAllFilters } = require("../../commands/clearAllFilters");

describe("Agents Page", () => {
    beforeEach(() => {
        cy.viewport(1920, 1080);
        cy.visit("/agentes");
    });

    it("Garante que a página de agentes funciona", () => {
        cy.url().should("include", "agentes");

        cy.get("h1").contains("Agentes");

        cy.contains("Mais recentes primeiro");
        cy.contains("Agentes encontrados");
        cy.contains("Filtros de agente");
        cy.contains("Status do agente");
        cy.contains("Tipo");
        cy.contains("Área de atuação");
        cy.contains("Junin Oliveira");
    });

    it("Garante que os filtros de oportunidades funcionam quando não existem resultados pra busca textual", () => {
        cy.get(".search-filter__actions--form-input").type("Agente ruim");

        cy.wait(1000);

        cy.contains("Nenhuma entidade encontrada");
    });

    it("Garante que os filtros de agentes funcionam quando existem resultados para a busca textual", () => {
        cy.get(".search-filter__actions--form-input").type("Oliviana");

        cy.wait(1000);

        cy.contains("2 Agentes encontrados");
    });

    it("Garante que o filtro de agentes oficiais funciona", () => {
        cy.wait(1000);

        cy.contains("Status do agente");

        cy.get(".verified > input").click();

        cy.contains("4 Agentes encontrados");
    });

    it("Garante que os filtros por tipo de agente funcionam", () => {
        cy.wait(1000);

        cy.contains("Tipo");

        cy.get(":nth-child(2) > select").select(2);
        cy.contains("Agente Coletivo");
        cy.wait(1000);
        cy.contains("24 Agentes encontrados");

        cy.get(":nth-child(2) > select").select(1);
        cy.contains("Agente Individual");
        cy.wait(1000);
        cy.contains("69 Agentes encontrados");

        cy.get(":nth-child(2) > select").select(0);
        cy.contains("Todos");
        cy.wait(1000);
        cy.contains("93 Agentes encontrados");
    });

    it("Garante que os filtros por área de atuação funcionam", () => {
        cy.wait(1000);

        cy.contains("Área de atuação");

        cy.get(":nth-child(3) > .mc-multiselect > :nth-child(1) > .v-popper > .mc-multiselect--input").click();
        cy.get(":nth-child(3) > .item > .text").click();

        cy.wait(1000);

        cy.contains("9 Agentes encontrados");
    });

    it("Garante que o botão limpar filtros na página de agentes funciona", () => {
        clearAllFilters([
            ".verified",
            ":nth-child(2) > select",
            ".mc-multiselect--input",
            ":nth-child(1) > .item > .text"
        ], "98 Agentes encontrados");
    });
});