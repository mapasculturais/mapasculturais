describe("Projeto", () => {
    beforeEach(() => {
        cy.visit("/");
    });

    it("Garante que o projeto seja clicável", () => {
        cy.get(".mc-header-menu__btn-mobile").click();
        cy.contains(".mc-header-menu__itens a", "Projetos").click();
        cy.url().should("include", "projetos");

        cy.get(".search-filter__actions--form-input").type("projeto");

        cy.get(".search-filter__actions--form-button").click();

        cy.wait(5000);

        cy.visit("https://redesign.testes.map.as/projeto/20/#info");

        cy.url().should("include", "https://redesign.testes.map.as/projeto/20/#info");
    });
});