describe("Homepage", () => {
    beforeEach(() => {
        cy.visit("/");
    });

    it("Garante que a home page funciona", () => {
        cy.contains("Bem-vinde ao Mapas Culturais");
    });
});