describe("HomePage", () => {
    beforeEach(() => {
        cy.viewport(1920, 1080);
        cy.visit("/");
    });

    it("acessa \"Agentes\" no navbar", () => {
        cy.contains("a", "Agentes").click();
        cy.url().should("include", "/agentes");
    });
});
describe("Pagina de Agentes", () => {
    beforeEach(() => {
        cy.viewport(1920, 1080);
        cy.visit("/agentes");
    });

    it("clica em \"Acessar\" e entra na pagina no agente selecionado", () => {
        cy.get(":nth-child(2) > .entity-card__footer > .entity-card__footer--action > .button").click();
        cy.contains("Informações");
    });
});