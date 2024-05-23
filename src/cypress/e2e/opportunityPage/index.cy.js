describe("Oportunidade", () => {
    it("Garante que o oportunidades seja clicável, permite digitar no campo de busca e navega para uma URL específica", () => {
        cy.visit("/");
        cy.wait(1000);
        cy.get(".mc-header-menu__btn-mobile").click();
        cy.contains(".mc-header-menu__itens a", "Oportunidades").click();
        cy.url().should("include", "/oportunidades");
        cy.get(".search-filter__actions--form-input").type("DJs");
        cy.wait(1000);
        cy.visit("/oportunidade/78/#info");
        cy.wait(1000);
        cy.url().should("include", "/oportunidade/");
    });
});