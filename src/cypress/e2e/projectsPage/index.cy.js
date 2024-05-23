describe("Projeto", () => {
    it("Garante que o projeto seja clicável", () => {
        cy.visit("/");
        cy.get(".mc-header-menu__btn-mobile").click();
        cy.contains(".mc-header-menu__itens a", "Projetos").click();
        cy.url().should("include", "projetos");
        cy.get(".search-filter__actions--form-input").type("Festa Junina");
        cy.wait(1000);
        cy.get('.entity-card__footer--action > .button').click();
        cy.wait(1000);
        cy.url().should("include", "/projeto/");
    });
});