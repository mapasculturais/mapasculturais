describe("Navbar", () => {
    beforeEach(() => {
        cy.viewport(1920, 1080);
        cy.visit("/");
    });

    it("Garante o funcionamento da navbar completa", () => {
        cy.contains("a", "Home").click();
        cy.url().should("include", "");

        cy.contains("a", "Oportunidades").click();
        cy.url().should("include", "oportunidades");

        cy.contains("a", "Agentes").click();
        cy.url().should("include", "agentes");

        cy.contains("a", "Eventos").click();
        cy.url().should("include", "eventos/#list");

        cy.contains("a", "Espaços").click();
        cy.url().should("include", "espacos/#list");

        cy.contains("a", "Projetos").click();
        cy.url().should("include", "projetos");
    });
});