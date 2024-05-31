describe("Homepage compactada", () => {
    beforeEach(() => {
        cy.viewport(1000, 768);
        cy.visit("/");
    });
    it("acessa \"Agentes\" no navbar", () => {
        cy.get(".mc-header-menu__btn-mobile").click();
        cy.contains(".mc-header-menu__itens a", "Agentes").click();
        cy.url().should("include", "agentes");
    });
});
describe("Pagina de Agentes", () => {
    beforeEach(() => {
        cy.viewport(1000, 768);
        cy.visit("/agentes");
    });

    it("clica em \"Acessar\" e entra na pagina no agente selecionado", () => {
        cy.get(":nth-child(2) > .entity-card__footer > .entity-card__footer--action > .button").click();
        cy.contains("Informaçõeuhyg");
    });
});